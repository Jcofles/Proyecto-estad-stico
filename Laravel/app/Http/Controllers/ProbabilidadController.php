<?php

namespace App\Http\Controllers;
use App\Services\FastApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Simulacion;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SimulacionExport;
use App\Imports\SimulacionImport;

class ProbabilidadController extends Controller
{
    public function index()
    {
        $historial = Simulacion::orderBy('created_at', 'desc')->get();
        return view('probabilidad.index', compact('historial'));
    }

    public function calcular(Request $request, FastApiClient $fastApiClient)
    {
        $datos = $request->validate([
            'numero_de_clientes' => ['required', 'integer', 'min:1', 'max:10000'],
            'simulaciones' => ['required', 'integer', 'min:1', 'max:1000000'],
            'contexto' => ['required', 'string', 'in:dia_normal,quincena,festivo'],
            'franja_horaria' => ['required', 'string', 'in:manana,tarde,noche'],
            'efectivo_inicial' => ['required', 'numeric', 'min:1000000'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'min' => 'El campo :attribute debe ser mayor a :min.',
            'in' => 'La opción seleccionada para :attribute no es válida.',
        ]);

        if ($datos['numero_de_clientes'] * $datos['simulaciones'] > 2_000_000) {
            return back()
                ->withErrors(['simulaciones' => 'La combinación de clientes y simulaciones no puede superar 2.000.000. Reduce uno de los valores.'])
                ->withInput();
        }

        $resultados = $fastApiClient->calcularProbabilidad($datos);

        // Guardar resultado en DB
        $simulacion = Simulacion::create([
            'contexto' => $datos['contexto'],
            'franja_horaria' => $datos['franja_horaria'],
            'efectivo_inicial' => $datos['efectivo_inicial'],
            'numero_de_clientes' => $datos['numero_de_clientes'],
            'simulaciones' => $datos['simulaciones'],
            'tiempo_espera_promedio' => $resultados['metricas_colas']['tiempo_espera_promedio_minutos'],
            'tiempo_espera_maximo' => $resultados['metricas_colas']['tiempo_espera_maximo_minutos'],
            'prob_sin_efectivo' => $resultados['riesgo_cajero']['probabilidad_sin_efectivo'],
            'prob_colapso' => $resultados['riesgo_cajero']['probabilidad_colapso_transaccional'],
            'uptime' => $resultados['riesgo_cajero']['disponibilidad_operacional_uptime']
        ]);

        // El análisis de IA es opcional y no debe impedir ver los resultados.
        $informeDatos = [
            'escenario' => $datos,
            'metricas' => $resultados,
        ];

        $analisis_ia = null;
        $analisis_ia_fuente = 'OpenRouter';
        $openRouterKey = config('services.openrouter.key');
        if ($openRouterKey) {
            try {
                $iaResponse = Http::withToken($openRouterKey)
                    ->timeout(60)
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => config('services.openrouter.model'),
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => <<<'PROMPT'
Eres analista de operaciones bancarias y debes explicar estos resultados en español claro para una persona que no conoce estadística.

Escribe texto plano, sin Markdown, sin asteriscos, sin almohadillas, sin tablas y sin abreviaturas sin explicar. Usa exactamente estas secciones, cada título en su propia línea y deja una línea en blanco entre secciones:

RESUMEN EJECUTIVO
Resume el escenario y el resultado principal en lenguaje sencillo.

¿QUÉ SIGNIFICAN LOS INDICADORES?
Explica la tasa de llegada, la espera promedio, la espera máxima promedio por escenario (promedio de los máximos de cada mundo simulado), el retiro promedio estimado y la probabilidad ajustada de falla por transacción. Distingue la espera promedio de la espera máxima promedio.

ANÁLISIS DE RIESGOS
Explica la probabilidad de quedarse sin efectivo. Explica que la probabilidad de falla transaccional es la proporción estimada de escenarios simulados en los que ocurre al menos una falla, no el porcentaje de tiempo que el cajero estará caído. Explica que el uptime mostrado es el complemento de esa probabilidad dentro de este modelo; no es una medición histórica ni un SLA.

RECOMENDACIONES
Da entre dos y cuatro acciones prácticas, priorizadas según los riesgos y las métricas observadas. No afirmes que el escenario es seguro si hay riesgos relevantes.

NOTA SOBRE LA ESTIMACIÓN
Indica que son estimaciones de Monte Carlo basadas en los supuestos del modelo, no garantías ni mediciones reales. No inventes datos ni cambies valores; explica cada porcentaje usando su significado en este modelo.
PROMPT,
                            ],
                            [
                                'role' => 'user',
                                'content' => json_encode($informeDatos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                            ],
                        ],
                ]);
                if ($iaResponse->successful() && $iaResponse->json('choices.0.message.content')) {
                    $analisis_ia = trim($iaResponse->json('choices.0.message.content'));
                    $analisis_ia = preg_replace('/^\s{0,3}#{1,6}\s*/m', '', $analisis_ia);
                    $analisis_ia = preg_replace('/^\s*[-*]\s+/m', '• ', $analisis_ia);
                    $analisis_ia = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '$1', $analisis_ia);
                    $analisis_ia = str_replace(['**', '__', '`'], '', $analisis_ia);
                } else {
                    $status = $iaResponse->status();
                    $providerMessage = $iaResponse->json('error.message');
                    Log::warning('OpenRouter no generó el informe.', [
                        'status' => $status,
                        'error' => $providerMessage,
                        'model' => config('services.openrouter.model'),
                    ]);

                    $analisis_ia = match ($status) {
                        401 => 'OpenRouter rechazó la clave API (401). Revisa OPENROUTER_API_KEY en Laravel/.env y vuelve a cargar la configuración.',
                        402 => 'OpenRouter indica que la cuenta no tiene crédito disponible (402).',
                        403 => 'OpenRouter denegó la solicitud (403). Revisa los permisos de la clave o las políticas de la cuenta.',
                        404 => 'OpenRouter no encontró el modelo configurado (404). Revisa OPENROUTER_MODEL en Laravel/.env.',
                        429 => 'Se alcanzó el límite de solicitudes de OpenRouter (429). Espera un momento y vuelve a intentarlo.',
                        502, 503, 524, 529 => 'El proveedor del modelo está temporalmente saturado o no disponible. Intenta de nuevo más tarde.',
                        default => 'OpenRouter respondió con error HTTP '.$status.'. '.($providerMessage ?: 'Consulta el log de Laravel para más detalles.'),
                    };
                    $analisis_ia = $this->crearInformeRespaldo($datos, $resultados)
                        ."\n\nESTADO DEL ANÁLISIS\n"
                        .$analisis_ia.' Se muestra una explicación local de los resultados.';
                    $analisis_ia_fuente = 'Resumen local (OpenRouter no respondió)';
                }
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                Log::warning('No fue posible conectar con OpenRouter.', ['message' => $exception->getMessage()]);
                $analisis_ia = $this->crearInformeRespaldo($datos, $resultados)
                    ."\n\nESTADO DEL ANÁLISIS\n"
                    .'OpenRouter tardó demasiado o no se pudo conectar. Se muestra una explicación local de los resultados.';
                $analisis_ia_fuente = 'Resumen local (OpenRouter no respondió)';
            }
        } else {
            $analisis_ia = $this->crearInformeRespaldo($datos, $resultados)
                ."\n\nESTADO DEL ANÁLISIS\n"
                .'No hay una clave de OpenRouter configurada. Se muestra una explicación local de los resultados.';
            $analisis_ia_fuente = 'Resumen local (OpenRouter sin configurar)';
        }

        $historial = Simulacion::orderBy('created_at', 'desc')->get();

        return view('probabilidad.index', [
            'resultados' => $resultados,
            'datos' => $datos,
            'historial' => $historial,
            'analisis_ia' => $analisis_ia,
            'analisis_ia_fuente' => $analisis_ia_fuente,
        ]);
    }

    private function crearInformeRespaldo(array $datos, array $resultados): string
    {
        $contextos = [
            'dia_normal' => 'día normal',
            'quincena' => 'quincena',
            'festivo' => 'día festivo',
        ];
        $franjas = ['manana' => 'mañana', 'tarde' => 'tarde', 'noche' => 'noche'];
        $colas = $resultados['metricas_colas'];
        $riesgos = $resultados['riesgo_cajero'];
        $parametros = $resultados['parametros_calculados'];
        $porcentaje = fn (float $valor): string => number_format($valor * 100, 2, ',', '.').' %';
        $dinero = fn (float $valor): string => '$'.number_format($valor, 0, ',', '.').' COP';

        $riesgoEfectivo = $riesgos['probabilidad_sin_efectivo'];
        $riesgoFalla = $riesgos['probabilidad_colapso_transaccional'];
        $esperaPromedio = $colas['tiempo_espera_promedio_minutos'];
        $esperaMaximaPromedio = $colas['tiempo_espera_maximo_minutos'];

        $riesgoEfectivoLectura = number_format($riesgoEfectivo * 100, 1, ',', '.');
        $riesgoFallaLectura = number_format($riesgoFalla * 100, 1, ',', '.');

        $recomendaciones = [];
        if ($riesgoFalla >= 0.20) {
            $recomendaciones[] = 'Priorizar una revisión de las causas de falla transaccional y monitorear cada tipo de error.';
        } else {
            $recomendaciones[] = 'Mantener el monitoreo de fallas y revisar periódicamente que los equipos y la conexión estén operativos.';
        }
        if ($riesgoEfectivo >= 0.05) {
            $recomendaciones[] = 'Revisar el monto de recarga y la frecuencia de abastecimiento para reducir el riesgo de quedarse sin efectivo.';
        } else {
            $recomendaciones[] = 'Mantener el nivel actual de efectivo y validar la estimación con datos reales de retiros.';
        }
        if ($esperaMaximaPromedio >= 10) {
            $recomendaciones[] = 'Evaluar medidas para atender la demanda de esta franja y reducir las esperas más largas.';
        }

        return "RESUMEN EJECUTIVO\n"
            .'Se simularon '.number_format($datos['numero_de_clientes']).' clientes en '.number_format($datos['simulaciones']).' escenarios para '.($contextos[$datos['contexto']] ?? $datos['contexto']).' en la '.($franjas[$datos['franja_horaria']] ?? $datos['franja_horaria']).'. El cajero inicia con '.$dinero((float) $datos['efectivo_inicial']).".\n\n"
            ."¿QUÉ SIGNIFICAN LOS INDICADORES?\n"
            .'La tasa de llegada estimada es de '.number_format($parametros['tasa_llegada_por_hora'], 1, ',', '.').' clientes por hora. La espera promedio es de '.number_format($esperaPromedio, 1, ',', '.').' minutos por cliente. La máxima espera promedio por escenario es de '.number_format($esperaMaximaPromedio, 1, ',', '.').' minutos: corresponde al promedio de la espera más larga dentro de cada escenario, no a la espera promedio de todos los clientes. El retiro promedio estimado es '.$dinero((float) $parametros['retiro_promedio_estimado']).'. La probabilidad ajustada de falla de una transacción es '.$porcentaje((float) $parametros['probabilidad_falla_ajustada']).".\n\n"
            ."ANÁLISIS DE RIESGOS\n"
            .'El riesgo estimado de quedarse sin efectivo es '.$porcentaje((float) $riesgoEfectivo).': aproximadamente '.$riesgoEfectivoLectura.' de cada 100 escenarios simulados no tendrían efectivo suficiente para cubrir los retiros. La probabilidad de falla transaccional es '.$porcentaje((float) $riesgoFalla).': en aproximadamente '.$riesgoFallaLectura.' de cada 100 escenarios ocurrió al menos una falla; no significa que el cajero esté caído ese porcentaje del tiempo. El uptime de '.$porcentaje((float) $riesgos['disponibilidad_operacional_uptime']).' es el complemento de ese riesgo dentro del modelo, no una medición histórica de disponibilidad.' ."\n\n"
            ."RECOMENDACIONES\n"
            .implode("\n", array_map(fn ($recomendacion) => '• '.$recomendacion, $recomendaciones))."\n\n"
            ."NOTA SOBRE LA ESTIMACIÓN\n"
            .'Estos resultados son estimaciones de Monte Carlo basadas en los supuestos del modelo. Pueden variar entre ejecuciones y no son una garantía de lo que ocurrirá en la operación real.';
    }

    public function export()
    {
        return Excel::download(new SimulacionExport, 'historial_simulaciones.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new SimulacionImport, $request->file('archivo_excel'));

        return redirect()->route('probabilidad.index')->with('success', 'Historial importado correctamente');
    }
}

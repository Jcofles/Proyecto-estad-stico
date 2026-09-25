<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor de Análisis Estocástico | Cajero Automático</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased min-h-screen p-6">

    <div class="max-w-6xl mx-auto space-y-8">

        <!-- Encabezado -->
        <header class="text-center mt-6">
            <h1 class="text-4xl font-extrabold text-indigo-700">Motor de Análisis Estocástico</h1>
            <p class="text-gray-600 mt-2 text-lg">Simulación de Teoría de Colas y Riesgos (Método Monte Carlo con JAX)</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Tarjeta del Formulario (Columna Izquierda) -->
            <div class="bg-white rounded-2xl shadow-xl p-6 lg:col-span-5 border-t-4 border-indigo-600">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Configuración de Escenario
                </h2>

                <form method="POST" action="{{ route('probabilidad.calcular') }}" class="space-y-5" onsubmit="document.getElementById('submitBtn').innerText = 'Simulando en JAX...'; document.getElementById('submitBtn').disabled = true; document.getElementById('submitBtn').classList.add('opacity-75', 'cursor-not-allowed');">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="contexto" class="block text-sm font-semibold text-gray-700">Contexto del Día:</label>
                            <select name="contexto" id="contexto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border bg-gray-50 font-medium">
                                <option value="dia_normal" {{ (old('contexto', $datos['contexto'] ?? '') == 'dia_normal') ? 'selected' : '' }}>Día Normal</option>
                                <option value="quincena" {{ (old('contexto', $datos['contexto'] ?? '') == 'quincena') ? 'selected' : '' }}>Día de Quincena (Alto tráfico)</option>
                                <option value="festivo" {{ (old('contexto', $datos['contexto'] ?? '') == 'festivo') ? 'selected' : '' }}>Día Festivo (Medio tráfico)</option>
                            </select>
                        </div>
                        <div>
                            <label for="franja_horaria" class="block text-sm font-semibold text-gray-700">Franja Horaria:</label>
                            <select name="franja_horaria" id="franja_horaria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border bg-gray-50 font-medium">
                                <option value="manana" {{ (old('franja_horaria', $datos['franja_horaria'] ?? '') == 'manana') ? 'selected' : '' }}>Mañana (Bajo estrés)</option>
                                <option value="tarde" {{ (old('franja_horaria', $datos['franja_horaria'] ?? '') == 'tarde') ? 'selected' : '' }}>Tarde (Hora Pico)</option>
                                <option value="noche" {{ (old('franja_horaria', $datos['franja_horaria'] ?? '') == 'noche') ? 'selected' : '' }}>Noche (Muy bajo estrés)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="efectivo_inicial" class="block text-sm font-semibold text-gray-700">Efectivo Inicial en Cajero (COP):</label>
                        <input type="number" name="efectivo_inicial" id="efectivo_inicial"
                               value="{{ old('efectivo_inicial', $datos['efectivo_inicial'] ?? 50000000) }}" min="1000000" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border bg-gray-50 font-medium text-green-700">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="numero_de_clientes" class="block text-sm font-semibold text-gray-700">Clientes a Simular:</label>
                            <input type="number" name="numero_de_clientes" id="numero_de_clientes"
                                   value="{{ old('numero_de_clientes', $datos['numero_de_clientes'] ?? 100) }}" min="1" max="10000" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border bg-gray-50">
                            <p class="mt-1 text-xs text-gray-500">El producto de clientes × simulaciones debe ser como máximo 2.000.000.</p>
                        </div>

                        <div>
                            <label for="simulaciones" class="block text-sm font-semibold text-gray-700">Mundos (Simulaciones):</label>
                            <input type="number" name="simulaciones" id="simulaciones"
                                   value="{{ old('simulaciones', $datos['simulaciones'] ?? 10000) }}" min="1" max="1000000" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border bg-gray-50">
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full mt-4 flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-[1.02]">
                        Lanzar Simulación Estocástica
                    </button>
                </form>

                @if($errors->any())
                    <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                        <ul class="text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Tarjeta de Resultados (Columna Derecha) -->
            <div class="bg-white rounded-2xl shadow-xl p-6 lg:col-span-7 flex flex-col min-h-[400px]">
                @isset($resultados)
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b-2 border-gray-100 pb-3">Tablero de Resultados Analíticos</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                        <!-- Panel: Teoría de Colas -->
                        <div class="bg-amber-50 rounded-xl p-5 border border-amber-100 shadow-sm">
                            <h3 class="text-amber-800 font-bold mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Comportamiento de Cola
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-amber-600 uppercase font-semibold">Tiempo Espera Promedio</p>
                                    <p class="text-2xl font-extrabold text-amber-700">{{ number_format($resultados['metricas_colas']['tiempo_espera_promedio_minutos'], 1) }} <span class="text-sm font-medium">min</span></p>
                                </div>
                                <div>
                                    <p class="text-xs text-amber-600 uppercase font-semibold">Máxima espera promedio por escenario</p>
                                    <p class="text-xl font-bold text-amber-700">{{ number_format($resultados['metricas_colas']['tiempo_espera_maximo_minutos'], 1) }} <span class="text-sm font-medium">min de espera</span></p>
                                    <p class="mt-1 text-xs text-amber-700">Promedio del mayor tiempo de espera observado en cada escenario simulado.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Riesgos del Cajero -->
                        <div class="bg-rose-50 rounded-xl p-5 border border-rose-100 shadow-sm">
                            <h3 class="text-rose-800 font-bold mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Evaluación de Riesgo
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-rose-600 uppercase font-semibold">Prob. Agotar Efectivo</p>
                                    <p class="text-2xl font-extrabold text-rose-700">{{ number_format($resultados['riesgo_cajero']['probabilidad_sin_efectivo'] * 100, 2) }}%</p>
                                    <p class="mt-1 text-xs text-rose-700">Porcentaje estimado de escenarios en los que el efectivo no alcanza para cubrir los retiros.</p>
                                </div>
                                <div>
                                    <p class="text-xs text-rose-600 uppercase font-semibold">Prob. Falla Transaccional</p>
                                    <p class="text-xl font-bold text-rose-700">{{ number_format($resultados['riesgo_cajero']['probabilidad_colapso_transaccional'] * 100, 2) }}%</p>
                                    <p class="mt-1 text-xs text-rose-700">Escenarios donde ocurre al menos una falla entre las transacciones simuladas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Confiabilidad y Uptime -->
                    <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-100 shadow-sm flex items-center justify-between">
                        <div>
                            <h3 class="text-emerald-800 font-bold flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Uptime Operacional (Disponibilidad)
                            </h3>
                            <p class="text-sm text-emerald-700 mt-1">Complemento del riesgo de falla en este modelo; no es una medición histórica del tiempo disponible.</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-4xl font-extrabold text-emerald-600">
                                {{ number_format($resultados['riesgo_cajero']['disponibilidad_operacional_uptime'] * 100, 2) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Detalles Técnicos -->
                    <div class="mt-6 pt-4 border-t border-gray-100 text-sm text-gray-500 flex justify-between">
                        <p>Tasa de llegada: <b>{{ $resultados['parametros_calculados']['tasa_llegada_por_hora'] }} clientes/h</b></p>
                        <p>Retiro prom: <b>COP ${{ number_format($resultados['parametros_calculados']['retiro_promedio_estimado'], 0, ',', '.') }}</b></p>
                    </div>

                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="h-20 w-20 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-lg font-medium text-gray-500">El Motor Estocástico está listo.</p>
                        <p class="text-sm text-gray-400 mt-1 max-w-md text-center">Configura el escenario en el panel izquierdo y descubre métricas de uptime y teoría de colas con millones de simulaciones en JAX.</p>
                    </div>
                @endisset
            </div>
        </div>

        <!-- FASE 2: Historial de Simulaciones (Base de Datos + Excel) -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-green-600 mt-8">
            <div class="flex justify-between items-center mb-6 border-b-2 border-gray-100 pb-3">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    Historial Persistente (Base de Datos)
                </h2>

                <div class="flex space-x-3">
                    <a href="{{ route('probabilidad.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Exportar Excel
                    </a>

                    <form action="{{ route('probabilidad.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-2">
                        @csrf
                        <input type="file" name="archivo_excel" accept=".xlsx,.xls,.csv" required class="text-sm text-gray-500 file:mr-2 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            Importar
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escenario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Simulaciones</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uptime</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Riesgo Dinero</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espera Prom</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($historial ?? [] as $registro)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                    {{ ucfirst($registro->contexto) }} <span class="text-gray-400">({{ $registro->franja_horaria }})</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($registro->simulaciones) }}</td>
                                <td class="px-4 py-3 text-sm font-bold {{ $registro->uptime > 0.9 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($registro->uptime * 100, 2) }}%
                                </td>
                                <td class="px-4 py-3 text-sm text-red-500">{{ number_format($registro->prob_sin_efectivo * 100, 2) }}%</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($registro->tiempo_espera_promedio, 1) }} min</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                    No hay simulaciones guardadas en la base de datos todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FASE 3: Gráficas Estadísticas Dinámicas -->
        @if(isset($historial) && count($historial) > 0)
        <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-purple-600 mt-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center mb-6 border-b-2 border-gray-100 pb-3">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                Dashboard Estadístico
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-center font-semibold text-gray-600 mb-2">Evolución de Tiempos de Espera (Últimas 10)</h3>
                    <canvas id="chartTiempos"></canvas>
                </div>
                <div>
                    <h3 class="text-center font-semibold text-gray-600 mb-2">Uptime vs Riesgo de Quedarse Sin Efectivo</h3>
                    <canvas id="chartRiesgos"></canvas>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const historial = @json($historial->take(10)->reverse()->values());

                const labels = historial.map(h => new Date(h.created_at).toLocaleString('es-CO', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' }) + ' · ' + h.franja_horaria);
                const tiemposPromedio = historial.map(h => h.tiempo_espera_promedio);
                const tiemposMaximos = historial.map(h => h.tiempo_espera_maximo);

                const uptimes = historial.map(h => h.uptime * 100);
                const riesgoEfectivo = historial.map(h => h.prob_sin_efectivo * 100);

                // Gráfico de Tiempos
                new Chart(document.getElementById('chartTiempos'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Espera Promedio (min)',
                                data: tiemposPromedio,
                                borderColor: 'rgb(245, 158, 11)',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Pico Máximo (min)',
                                data: tiemposMaximos,
                                borderColor: 'rgb(220, 38, 38)',
                                borderDash: [5, 5],
                                tension: 0.3
                            }
                        ]
                    },
                    options: { responsive: true }
                });

                // Gráfico de Riesgos
                new Chart(document.getElementById('chartRiesgos'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Uptime (%)',
                                data: uptimes,
                                backgroundColor: 'rgba(16, 185, 129, 0.7)' // Verde
                            },
                            {
                                label: 'Riesgo Sin Efectivo (%)',
                                data: riesgoEfectivo,
                                backgroundColor: 'rgba(239, 68, 68, 0.7)' // Rojo
                            }
                        ]
                    },
                    options: { responsive: true, scales: { y: { max: 100 } } }
                });
            });
        </script>
        @endif

        @isset($analisis_ia)
        <section class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-indigo-600 mt-8 mb-12">
            <h2 class="text-2xl font-bold text-indigo-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                {{ $analisis_ia_fuente ?? 'Interpretación de Inteligencia Artificial' }}
            </h2>
            <div class="text-sm text-gray-700 leading-7 whitespace-pre-line font-medium">
                {{ trim($analisis_ia) }}
            </div>
        </section>
        @endisset

    </div>
</body>
</html>

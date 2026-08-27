<?php

namespace App\Http\Controllers; 
use App\Services\FastApiClient;
use Illuminate\Http\Request;

class ProbabilidadController extends Controller
{
    public function index()
    {
        return view('probabilidad.index');
    }

    public function calcular(Request $request, FastApiClient $fastApiClient)
    {
        $datos = $request->validate([
            'numero_de_clientes' => ['required', 'integer', 'min:1'],
            'probabilidad_de_fallo' => ['required', 'numeric', 'between:0,1'],
            'simulaciones' => ['required', 'integer', 'min:1'],
        ]);

        $resultados = $fastApiClient->calcularProbabilidad($datos);

        return view('probabilidad.index', [
            'resultados' => $resultados,
            'datos' => $datos,
        ]);
    }
}


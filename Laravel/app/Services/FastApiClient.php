<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FastApiClient
{
    public function calcularProbabilidad(array $datos): array
    {
       $respuesta = Http::post
       (config('services.fastapi.url') . '/probabilidad/calcular', 
       $datos
       );

       $respuesta->throw();
       return $respuesta->json();
    }
}
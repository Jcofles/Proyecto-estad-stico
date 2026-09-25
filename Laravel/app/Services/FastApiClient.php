<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FastApiClient
{
    public function calcularProbabilidad(array $datos): array
    {
        set_time_limit(300);
        $respuesta = Http::timeout(300)->post(
            config('services.fastapi.url') . '/probabilidad/calcular',
            $datos
        );

       $respuesta->throw();
       return $respuesta->json();
    }
}
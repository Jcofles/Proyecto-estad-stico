<?php

namespace App\Imports;

use App\Models\Simulacion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SimulacionImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?\Illuminate\Database\Eloquent\Model
    {
        // Ignora si no tiene contexto (fila vacía)
        if (!isset($row['contexto'])) {
            return null;
        }

        return new Simulacion([
            'contexto' => $row['contexto'],
            'franja_horaria' => $row['franja_horaria'],
            'efectivo_inicial' => $row['efectivo_inicial'],
            'numero_de_clientes' => $row['num_clientes'],
            'simulaciones' => $row['simulaciones'],
            'tiempo_espera_promedio' => $row['espera_promedio_min'],
            'tiempo_espera_maximo' => $row['espera_max_min'],
            'prob_sin_efectivo' => $row['prob_sin_efectivo'],
            'prob_colapso' => $row['prob_colapso'],
            'uptime' => $row['uptime'],
        ]);
    }
}

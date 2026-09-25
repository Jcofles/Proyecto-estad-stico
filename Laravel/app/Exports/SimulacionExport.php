<?php

namespace App\Exports;

use App\Models\Simulacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimulacionExport implements FromCollection, WithHeadings
{
    public function collection(): \Illuminate\Support\Collection
    {
        return Simulacion::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Contexto',
            'Franja Horaria',
            'Efectivo Inicial',
            'Numero De Clientes',
            'Simulaciones',
            'Tiempo Espera Promedio',
            'Tiempo Espera Maximo',
            'Prob Sin Efectivo',
            'Prob Colapso',
            'Uptime',
            'Fecha Creacion',
            'Fecha Actualizacion'
        ];
    }
}

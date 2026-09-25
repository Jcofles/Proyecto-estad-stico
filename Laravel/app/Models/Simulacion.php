<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'contexto',
        'franja_horaria',
        'efectivo_inicial',
        'numero_de_clientes',
        'simulaciones',
        'tiempo_espera_promedio',
        'tiempo_espera_maximo',
        'prob_sin_efectivo',
        'prob_colapso',
        'uptime',
    ];
}

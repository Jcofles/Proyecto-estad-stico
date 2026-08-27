<?php

use App\Http\Controllers\ProbabilidadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProbabilidadController::class, 'index']) ->name('probabilidad.index');

Route::post('/probabilidad/calcular', [ProbabilidadController::class, 'calcular']) ->name('probabilidad.calcular');   
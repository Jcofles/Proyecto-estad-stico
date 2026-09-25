<?php

use App\Http\Controllers\ProbabilidadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProbabilidadController::class, 'index']) ->name('probabilidad.index');

Route::post('/probabilidad/calcular', [ProbabilidadController::class, 'calcular'])->name('probabilidad.calcular');
Route::get('/probabilidad/calcular', function () {
    return redirect()->route('probabilidad.index');
});

Route::get('/exportar', [ProbabilidadController::class, 'export'])->name('probabilidad.export');
Route::post('/importar', [ProbabilidadController::class, 'import'])->name('probabilidad.import');
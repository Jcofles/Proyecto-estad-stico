<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('simulacions', function (Blueprint $table) {
            $table->id();
            $table->string('contexto');
            $table->string('franja_horaria');
            $table->decimal('efectivo_inicial', 15, 2);
            $table->integer('numero_de_clientes');
            $table->integer('simulaciones');
            $table->decimal('tiempo_espera_promedio', 8, 2);
            $table->decimal('tiempo_espera_maximo', 8, 2);
            $table->decimal('prob_sin_efectivo', 10, 8);
            $table->decimal('prob_colapso', 10, 8);
            $table->decimal('uptime', 10, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulacions');
    }
};

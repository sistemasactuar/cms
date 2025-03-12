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
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que realiza la acción
            $table->string('accion'); // Descripción de la acción
            $table->string('modelo')->nullable(); // Modelo afectado (si aplica)
            $table->unsignedBigInteger('modelo_id')->nullable(); // ID del modelo afectado (si aplica)
            $table->json('cambios')->nullable(); // Cambios realizados
            $table->ipAddress('ip')->nullable(); // IP del usuario
            $table->string('navegador')->nullable(); // Información del navegador
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};

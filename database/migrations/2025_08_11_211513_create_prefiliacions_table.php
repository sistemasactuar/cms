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
        Schema::create('preafiliacions', function (Blueprint $table) {
            $table->id();
            $table->string('monto_solicitado')->nullable();
            $table->string('cuota_propuesta')->nullable();
            $table->string('destino')->nullable();
            $table->string('nombre');
            $table->string('cedula');
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('telefonos')->nullable();
            $table->string('email')->nullable();
            $table->string('vivienda')->nullable(); // Propia, Arrendada, Familiar
            $table->string('actividad')->nullable();
            $table->string('antiguedad')->nullable();
            $table->boolean('autorizado')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prefiliacions');
    }
};

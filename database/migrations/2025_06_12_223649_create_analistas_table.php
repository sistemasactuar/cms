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
        Schema::create('analistas', function (Blueprint $table) {
            $table->string('Cedula_Prom', 20)->primary();
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->string('Sucursal', 20)->nullable();
            $table->string('AP_Nombre1', 50)->nullable();
            $table->string('AP_Nombre2', 50)->nullable();

            $table->foreign('sede_id')->references('id')->on('sedes')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analistas');
    }
};

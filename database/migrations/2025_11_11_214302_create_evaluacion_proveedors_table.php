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
        Schema::create('evaluacion_proveedors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // evaluador
            $table->date('fecha')->default(now());

            for ($i = 1; $i <= 11; $i++) {
                $table->integer("pregunta_$i")->nullable();
            }

            $table->integer('puntos_obtenidos')->nullable();
            $table->integer('puntos_posibles')->nullable();
            $table->float('calificacion')->nullable();
            $table->string('clasificacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->longText('firma')->nullable();
            $table->boolean('bloqueado')->default(false); // ✅ bloquea si ya firmó
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_proveedors');
    }
};

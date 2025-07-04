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
       Schema::create('plano4111s', function (Blueprint $table) {
            $table->id();
            $table->string('cedula');
            $table->string('asociado')->nullable();
            $table->string('modalidad')->nullable();
            $table->string('calificacion')->nullable();
            $table->string('obligacion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('celular')->nullable();
            $table->string('ciudad')->nullable();
            $table->decimal('saldo_capital', 15, 2)->nullable();
            $table->decimal('capital_vencido', 15, 2)->nullable();
            $table->integer('dias_vencidos')->nullable();
            $table->string('asesor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano4111s');
    }
};

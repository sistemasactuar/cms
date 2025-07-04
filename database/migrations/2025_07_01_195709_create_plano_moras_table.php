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
       Schema::create('plano_moras', function (Blueprint $table) {
        $table->id();
        $table->string('cedula_cliente')->nullable();
        $table->string('nombre_cliente')->nullable();
        $table->string('sucursal')->nullable();
        $table->string('cedula_prom')->nullable();
        $table->string('nombre_prom')->nullable();
        $table->string('obligacion')->nullable();
        $table->string('calificacion')->nullable();
        $table->decimal('monto', 15, 2)->nullable();
        $table->decimal('saldo_actual', 15, 2)->nullable();
        $table->decimal('vencido_linix', 15, 2)->nullable();
        $table->decimal('vencido_menor_77', 15, 2)->nullable(); // "<77" lo renombramos
        $table->decimal('vencido_actuar', 15, 2)->nullable();
        $table->integer('dias_linix')->nullable();
        $table->integer('dias_actuar')->nullable();
        $table->decimal('saldo_total', 15, 2)->nullable();
        $table->string('direccion')->nullable();
        $table->string('barrio')->nullable();
        $table->string('telefono')->nullable();
        $table->string('ciudad')->nullable();
        $table->string('cedula_cod1')->nullable();
        $table->string('nombre_cod1')->nullable();
        $table->string('tel_cod1')->nullable();
        $table->string('direccion_cod1')->nullable();
        $table->string('cedula_cod2')->nullable();
        $table->string('nombre_cod2')->nullable();
        $table->string('tel_cod2')->nullable();
        $table->string('direccion_cod2')->nullable();
        $table->string('cedula_cod3')->nullable();
        $table->string('nombre_cod3')->nullable();
        $table->string('tel_cod3')->nullable();
        $table->string('direccion_cod3')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_moras');
    }
};

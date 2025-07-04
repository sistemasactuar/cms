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
        Schema::create('auditoria_moras', function (Blueprint $table) {
            $table->id();
            $table->string('obligacion');
            $table->string('sucursal')->nullable();
            $table->string('cedula_prom')->nullable();
            $table->string('nombre_prom')->nullable();
            $table->string('calificacion')->nullable();
            $table->decimal('monto', 15, 2)->nullable();
            $table->decimal('saldo_actual', 15, 2)->nullable();
            $table->decimal('vencido_linix', 15, 2)->nullable();
            $table->decimal('vencido_menor_77', 15, 2)->nullable();
            $table->decimal('vencido_actuar', 15, 2)->nullable();
            $table->integer('dias_linix')->nullable();
            $table->integer('dias_actuar')->nullable();
            $table->decimal('saldo_total', 15, 2)->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_modificacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_moras');
    }
};

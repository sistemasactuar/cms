<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plano_saldo_valor_saldos_diarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plano_saldo_valor_id')
                ->nullable()
                ->constrained('plano_saldos_valores')
                ->nullOnDelete();
            $table->string('obligacion');
            $table->string('cc');
            $table->date('fecha_archivo');
            $table->decimal('valor_vencido', 18, 2)->nullable();
            $table->decimal('saldo_capital', 18, 2)->nullable();
            $table->integer('dias_mora')->nullable();
            $table->decimal('valor_cuota', 18, 2)->nullable();
            $table->decimal('valor_reportar', 18, 2)->nullable();
            $table->string('origen_registro', 30)->nullable();
            $table->string('estado_movimiento', 30)->nullable();
            $table->decimal('variacion_valor_vencido', 18, 2)->nullable();
            $table->decimal('variacion_saldo_capital', 18, 2)->nullable();
            $table->timestamps();

            $table->unique(['fecha_archivo', 'cc', 'obligacion'], 'uk_psv_saldos_diarios_fecha_cc_obl');
            $table->index(['cc', 'obligacion'], 'idx_psv_saldos_diarios_cc_obl');
            $table->index(['fecha_archivo', 'estado_movimiento'], 'idx_psv_saldos_diarios_fecha_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plano_saldo_valor_saldos_diarios');
    }
};

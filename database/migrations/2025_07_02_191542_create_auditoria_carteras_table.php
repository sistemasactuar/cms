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
        Schema::create('auditoria_carteras', function (Blueprint $table) {
            $table->id();
            $table->string('id_cliente')->nullable();
            $table->decimal('sld_aportes', 15, 2)->nullable();
            $table->string('tipo_obl')->nullable();
            $table->string('no_obligacion')->nullable();
            $table->decimal('saldo_capital', 15, 2)->nullable();
            $table->decimal('sld_int', 15, 2)->nullable();
            $table->decimal('sld_mora', 15, 2)->nullable();
            $table->integer('dias_vencidos')->nullable();
            $table->decimal('venc_capital', 15, 2)->nullable();
            $table->decimal('tasa_col_namv', 8, 4)->nullable();
            $table->decimal('tasa_peridodo_namv', 8, 4)->nullable();
            $table->decimal('vlr_garantia', 15, 2)->nullable();
            $table->decimal('vlr_cobertura_disponible', 15, 2)->nullable();
            $table->decimal('vlr_prov_capital', 15, 2)->nullable();
            $table->decimal('vlr_prov_interes', 15, 2)->nullable();
            $table->decimal('vlr_aportes_util_en_la_provision', 15, 2)->nullable();
            $table->decimal('vlr_cuota', 15, 2)->nullable();
            $table->integer('no_cuotas')->nullable();
            $table->date('fec_vcto_final')->nullable();
            $table->integer('altura')->nullable();
            $table->date('fec_vencto')->nullable();
            $table->string('calif_antes_ley_arrast')->nullable();
            $table->string('calif_aplicada')->nullable();
            $table->string('calif_mes_ant')->nullable();
            $table->decimal('int_cte_orden', 15, 2)->nullable();
            $table->decimal('int_mora_orden', 15, 2)->nullable();
            $table->date('fec_historico')->nullable();
            $table->decimal('sld_seg_vida', 15, 2)->nullable();
            $table->decimal('sld_seg_patrimonial', 15, 2)->nullable();
            $table->date('fec_ult_pago')->nullable();
            $table->string('cuenta_contable')->nullable();
            $table->string('cod_garantia')->nullable();
            $table->integer('cantidad_garantias')->nullable();
            $table->decimal('porc_cobertura_garant', 8, 2)->nullable();
            $table->decimal('vlr_aplicado_garant', 15, 2)->nullable();
            $table->string('pagare')->nullable();
            $table->string('c_costo_cli')->nullable();
            $table->string('c_costo_obl')->nullable();
            $table->integer('dias_vencidos_int')->nullable();
            $table->string('instan_aprob')->nullable();
            $table->decimal('vencido_int', 15, 2)->nullable();
            $table->integer('dias_vencidos_capital')->nullable();
            $table->decimal('perdida_incumplimiento_sistema', 15, 2)->nullable();
            $table->decimal('perdida_incumplimiento_manual', 15, 2)->nullable();
            $table->decimal('valor_expuesto', 15, 2)->nullable();
            $table->decimal('valor_perdida_esperada_aplicada', 15, 2)->nullable();
            $table->decimal('valor_comercial_activos', 15, 2)->nullable();
            $table->decimal('valor_saldo_pasivos', 15, 2)->nullable();
            $table->string('scoring')->nullable();
            $table->date('fecha_restructuracion')->nullable();
            $table->decimal('valor_garantia', 15, 2)->nullable();
            $table->timestamp('fecha_modificacion')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_carteras');
    }
};

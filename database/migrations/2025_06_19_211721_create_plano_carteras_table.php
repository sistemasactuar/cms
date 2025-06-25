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
        Schema::create('plano_cartera', function (Blueprint $table) {
$table->id();
            $table->string('suc_cliente')->nullable();
            $table->string('id_cliente')->nullable();
            $table->string('nombre')->nullable();
            $table->string('estado')->nullable();
            $table->string('medio_de_pago')->nullable();
            $table->decimal('sld_aportes', 18, 2)->nullable();
            $table->string('tipo_obl')->nullable();
            $table->string('no_obligacion')->nullable();
            $table->decimal('monto_solicitado', 18, 2)->nullable();
            $table->decimal('saldo_capital', 18, 2)->nullable();
            $table->decimal('sld_int', 18, 2)->nullable();
            $table->decimal('sld_mora', 18, 2)->nullable();
            $table->integer('dias_vencidos')->nullable();
            $table->decimal('venc_capital', 18, 2)->nullable();
            $table->decimal('tasa_col_namv', 5, 2)->nullable();
            $table->decimal('tasa_peridodo_namv', 5, 2)->nullable();
            $table->string('clasificacion')->nullable();
            $table->string('linea_de_credito')->nullable();
            $table->string('destinacion')->nullable();
            $table->string('modalidad')->nullable();
            $table->string('medio_de_pago_obligacion')->nullable();
            $table->string('tipo_garantia')->nullable();
            $table->decimal('vlr_garantia', 18, 2)->nullable();
            $table->decimal('vlr_cobertura_disponible', 18, 2)->nullable();
            $table->decimal('vlr_prov_capital', 18, 2)->nullable();
            $table->decimal('vlr_prov_interes', 18, 2)->nullable();
            $table->decimal('vlr_aportes_util_en_la_provision', 18, 2)->nullable();
            $table->date('fec_aprobacion')->nullable();
            $table->date('fec_prestamo')->nullable();
            $table->date('fec_liquidacion')->nullable();
            $table->decimal('vlr_cuota', 18, 2)->nullable();
            $table->integer('no_cuotas')->nullable();
            $table->string('periodicidad')->nullable();
            $table->date('fec_inicio')->nullable();
            $table->date('fec_vcto_final')->nullable();
            $table->string('altura')->nullable();
            $table->date('fec_vencto')->nullable();
            $table->string('calif_antes_ley_arrast')->nullable();
            $table->string('calif_aplicada')->nullable();
            $table->string('calif_mes_ant')->nullable();
            $table->decimal('int_cte_orden', 18, 2)->nullable();
            $table->decimal('int_mora_orden', 18, 2)->nullable();
            $table->date('fec_historico')->nullable();
            $table->decimal('sld_seg_vida', 18, 2)->nullable();
            $table->decimal('sld_seg_patrimonial', 18, 2)->nullable();
            $table->string('forma_de_pago')->nullable();
            $table->date('fec_ult_pago')->nullable();
            $table->string('cuenta_contable')->nullable();
            $table->string('suc_credito')->nullable();
            $table->string('cod_garantia')->nullable();
            $table->integer('cantidad_garantias')->nullable();
            $table->decimal('porc_cobertura_garant', 5, 2)->nullable();
            $table->decimal('vlr_aplicado_garant', 18, 2)->nullable();
            $table->string('codeudores')->nullable();
            $table->string('garantia_real')->nullable();
            $table->string('pagare')->nullable();
            $table->string('c_costo_cli')->nullable();
            $table->string('c_costo_obl')->nullable();
            $table->integer('dias_vencidos_int')->nullable();
            $table->string('instan_aprob')->nullable();
            $table->decimal('vencido_int', 18, 2)->nullable();
            $table->integer('dias_vencidos_capital')->nullable();
            $table->decimal('prob_incumplimiento_sistema', 5, 2)->nullable();
            $table->decimal('prob_incumplimiento_manual', 5, 2)->nullable();
            $table->decimal('perdida_incumplimiento_sistema', 5, 2)->nullable();
            $table->decimal('perdida_incumplimiento_manual', 5, 2)->nullable();
            $table->decimal('valor_expuesto', 18, 2)->nullable();
            $table->decimal('valor_perdida_esperada_aplicada', 18, 2)->nullable();
            $table->decimal('valor_comercial_activos', 18, 2)->nullable();
            $table->decimal('valor_saldo_pasivos', 18, 2)->nullable();
            $table->decimal('scoring', 5, 2)->nullable();
            $table->date('fecha_restructuracion')->nullable();
            $table->decimal('valor_garantia', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_cartera');
    }
};

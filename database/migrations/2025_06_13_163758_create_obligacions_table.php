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
        Schema::create('obligaciones', function (Blueprint $table) {
    $table->string('Obligacion', 20)->primary();
    $table->string('Cedula_Cliente', 20);
    $table->string('Cedula_Prom', 20);
    $table->string('Calificacion', 10)->nullable();
    $table->decimal('Monto', 18, 2)->nullable();
    $table->decimal('Saldo_Actual', 18, 2)->nullable();
    $table->decimal('Saldo_total', 18, 2)->nullable();
    $table->string('Estado', 20)->nullable();
    $table->string('Tipo_Obl', 20);
    $table->decimal('Monto_Solicitado', 18, 2)->nullable();
    $table->decimal('CA_Valor_Cuota', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Capitalizacion', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Capital', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Interes', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Mora', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Seg_Vida', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Seg_Patrimonial', 18, 2)->nullable();
    $table->decimal('CA_Valor_Vencido_Otros_Conceptos', 18, 2)->nullable();
    $table->decimal('CA_Valor_Proximo_Vencimiento', 18, 2)->nullable();
    $table->date('CA_Fecha_Cuota')->nullable();
    $table->integer('CA_Dias_Vencidos')->nullable();
    $table->decimal('CA_Saldo_Capital', 18, 2)->nullable();
    $table->string('CA_Codigo_Modalidad', 20)->nullable();
    $table->string('Linea_de_credito', 50)->nullable();
    $table->string('Destinacion', 100)->nullable();
    $table->string('Medio_de_pago', 50)->nullable();
    $table->string('Medio_de_pago_Obligacion', 50)->nullable();
    $table->decimal('Sld_Aportes', 18, 2)->nullable();
    $table->decimal('Tasa_Col_NAMV', 5, 2)->nullable();
    $table->decimal('Tasa_Periodo_NAMV', 5, 2)->nullable();
    $table->date('Fec_Aprobacion')->nullable();
    $table->date('Fec_Prestamo')->nullable();
    $table->date('Fec_Liquidacion')->nullable();
    $table->integer('No_Cuotas')->nullable();
    $table->string('Periodicidad', 20)->nullable();
    $table->date('Fec_Inicio')->nullable();
    $table->date('Fec_Vcto_Final')->nullable();
    $table->string('Altura', 20)->nullable();
    $table->date('Fec_Vencto')->nullable();
    $table->string('Calif_Antes_Ley_Arrast', 10)->nullable();
    $table->string('Calif_Aplicada', 10)->nullable();
    $table->string('Calif_Mes_Ant', 10)->nullable();
    $table->decimal('Int_Cte_Orden', 18, 2)->nullable();
    $table->decimal('Int_Mora_Orden', 18, 2)->nullable();
    $table->date('Fec_Historico')->nullable();
    $table->string('Forma_de_Pago', 50)->nullable();
    $table->date('Fec_Ult_Pago')->nullable();
    $table->string('Cuenta_Contable', 50)->nullable();
    $table->string('Suc_Credito', 20)->nullable();
    $table->integer('Dias_linix')->nullable();
    $table->integer('Dias_actuar')->nullable();
    $table->decimal('Vencido_linix', 18, 2)->nullable();
    $table->decimal('Vencido_77', 18, 2)->nullable();
    $table->decimal('Vencido_Actuar', 18, 2)->nullable();
    $table->integer('Dias_vencidos_Int')->nullable();
    $table->string('C_Costo_Obl', 20)->nullable();
    $table->string('Instan_Aprob', 20)->nullable();
    $table->decimal('Scoring', 5, 2)->nullable();
    $table->date('Fecha_restructuracion')->nullable();
    $table->integer('usuario_edita')->nullable();

    $table->foreign('Cedula_Cliente')->references('Cedula_Cliente')->on('terceros');
    $table->foreign('Cedula_Prom')->references('Cedula_Prom')->on('analistas');
    $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obligaciones');
    }
};

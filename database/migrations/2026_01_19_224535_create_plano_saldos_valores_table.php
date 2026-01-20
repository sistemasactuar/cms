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
        Schema::create('plano_saldos_valores', function (Blueprint $table) {
            $table->id();
            $table->string('obligacion');
            $table->string('cc');
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->decimal('valor_reportar', 18, 2)->nullable();
            $table->string('modalidad')->nullable();
            $table->string('periodo')->nullable();
            $table->string('observacion')->nullable();
            $table->decimal('saldo_capital', 18, 2)->nullable();
            $table->integer('dias_mora')->nullable();
            $table->date('fecha_vigencia')->nullable();
            
            $table->unique(['cc', 'obligacion'], 'uk_cc_obligacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_saldos_valores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plano_saldos_valores', function (Blueprint $table): void {
            if (!Schema::hasColumn('plano_saldos_valores', 'valor_vencido')) {
                $table->decimal('valor_vencido', 18, 2)->nullable()->after('valor_cuota');
            }

            if (!Schema::hasColumn('plano_saldos_valores', 'origen_registro')) {
                $table->string('origen_registro', 30)->nullable()->after('fecha_nacimiento');
            }

            if (!Schema::hasColumn('plano_saldos_valores', 'fecha_entrada_plano')) {
                $table->date('fecha_entrada_plano')->nullable()->after('origen_registro');
            }

            if (!Schema::hasColumn('plano_saldos_valores', 'estado_registro')) {
                $table->string('estado_registro', 30)->nullable()->after('fecha_entrada_plano');
            }

            if (!Schema::hasColumn('plano_saldos_valores', 'ultima_fecha_saldo_diario')) {
                $table->date('ultima_fecha_saldo_diario')->nullable()->after('fecha_vigencia');
            }

            if (!Schema::hasColumn('plano_saldos_valores', 'ultimo_estado_saldo_diario')) {
                $table->string('ultimo_estado_saldo_diario', 30)->nullable()->after('ultima_fecha_saldo_diario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plano_saldos_valores', function (Blueprint $table): void {
            foreach ([
                'ultimo_estado_saldo_diario',
                'ultima_fecha_saldo_diario',
                'estado_registro',
                'fecha_entrada_plano',
                'origen_registro',
                'valor_vencido',
            ] as $column) {
                if (Schema::hasColumn('plano_saldos_valores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

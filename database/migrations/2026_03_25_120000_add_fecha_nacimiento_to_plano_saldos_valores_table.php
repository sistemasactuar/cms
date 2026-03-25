<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plano_saldos_valores', function (Blueprint $table): void {
            if (!Schema::hasColumn('plano_saldos_valores', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('apellidos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plano_saldos_valores', function (Blueprint $table): void {
            if (Schema::hasColumn('plano_saldos_valores', 'fecha_nacimiento')) {
                $table->dropColumn('fecha_nacimiento');
            }
        });
    }
};

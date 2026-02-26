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
        Schema::table('plano_saldos_valores', function (Blueprint $table) {
            $table->decimal('valor_cuota', 18, 2)->nullable()->after('valor_reportar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plano_saldos_valores', function (Blueprint $table) {
            $table->dropColumn('valor_cuota');
        });
    }
};

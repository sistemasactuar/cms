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
        Schema::table('evaluacion_proveedors', function (Blueprint $table) {
            $table->text('vobo_observaciones')->nullable()->after('bloqueado');
            $table->dateTime('vobo_fecha')->nullable()->after('vobo_observaciones');
            $table->unsignedBigInteger('vobo_user_id')->nullable()->after('vobo_fecha');

            $table->foreign('vobo_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluacion_proveedors', function (Blueprint $table) {
            $table->dropForeign(['vobo_user_id']);
            $table->dropColumn(['vobo_observaciones', 'vobo_fecha', 'vobo_user_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vot_candidatos', function (Blueprint $table): void {
            $table->unsignedBigInteger('aportante_id')->nullable()->after('planilla_id');
        });

        DB::statement("
            UPDATE vot_candidatos vc
            INNER JOIN vot_aportantes va ON va.documento = vc.documento
            SET vc.aportante_id = va.id
            WHERE vc.aportante_id IS NULL
        ");

        Schema::table('vot_candidatos', function (Blueprint $table): void {
            $table->foreign('aportante_id', 'vc_apo_fk')
                ->references('id')
                ->on('vot_aportantes')
                ->nullOnDelete();

            $table->unique(['votacion_id', 'aportante_id'], 'vc_vot_apo_unq');
        });
    }

    public function down(): void
    {
        Schema::table('vot_candidatos', function (Blueprint $table): void {
            $table->dropUnique('vc_vot_apo_unq');
            $table->dropForeign('vc_apo_fk');
            $table->dropColumn('aportante_id');
        });
    }
};

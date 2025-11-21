<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('evaluacion_proveedors', function (Blueprint $table) {

            // 1. Eliminar foreign key si existe
            try {
                $table->dropForeign('evaluacion_proveedors_user_id_foreign');
            } catch (\Exception $e) {}

            // 2. Eliminar índice también si existe
            try {
                $table->dropIndex('evaluacion_proveedors_user_id_foreign');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'area')) {
                $table->string('area', 120)->nullable();
            }
        });

        Schema::table('proc_activofijo', function (Blueprint $table) {
            if (!Schema::hasColumn('proc_activofijo', 'responsable_user_id')) {
                $table->foreignId('responsable_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::create('proc_activofijo_responsable_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_fijo_id')->constrained('proc_activofijo')->cascadeOnDelete();
            $table->foreignId('usuario_anterior_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_nuevo_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsable_anterior', 180)->nullable();
            $table->string('responsable_nuevo', 180)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proc_activofijo_responsable_historial');

        Schema::table('proc_activofijo', function (Blueprint $table) {
            if (Schema::hasColumn('proc_activofijo', 'responsable_user_id')) {
                $table->dropForeign(['responsable_user_id']);
                $table->dropColumn('responsable_user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'area')) {
                $table->dropColumn('area');
            }
        });
    }
};


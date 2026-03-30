<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_planoc_congelamientos', function (Blueprint $table): void {
            $table->id();
            $table->string('legacy_source_table');
            $table->string('source_key', 120);
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->string('legacy_obligacion')->nullable()->index();
            $table->string('legacy_cedula_cliente')->nullable()->index();
            $table->string('legacy_nombre_cliente')->nullable();
            $table->string('legacy_nombre_promotor')->nullable();
            $table->string('legacy_sucursal')->nullable();
            $table->decimal('legacy_saldo_actual', 18, 2)->nullable();
            $table->integer('legacy_dias_linix')->nullable();
            $table->integer('legacy_dias_actuar')->nullable();
            $table->string('legacy_fecha_pago')->nullable();
            $table->string('legacy_email')->nullable();
            $table->string('legacy_whatsapp')->nullable();
            $table->boolean('legacy_activo')->nullable();
            $table->string('legacy_fecadi')->nullable();
            $table->string('legacy_fecmod')->nullable();
            $table->unsignedBigInteger('legacy_usuadi')->nullable();
            $table->unsignedBigInteger('legacy_usumod')->nullable();
            $table->json('payload');
            $table->timestamp('legacy_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['legacy_source_table', 'source_key'], 'uk_legacy_planoc_cong_source_key');
        });

        Schema::create('legacy_planoc_historiales', function (Blueprint $table): void {
            $table->id();
            $table->string('tipo_historial', 50)->index();
            $table->string('legacy_source_table');
            $table->string('source_key', 120);
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->string('legacy_obligacion')->nullable()->index();
            $table->string('legacy_cedula_cliente')->nullable()->index();
            $table->string('legacy_periodo')->nullable()->index();
            $table->boolean('legacy_activo')->nullable();
            $table->string('legacy_fecadi')->nullable();
            $table->unsignedBigInteger('legacy_usuadi')->nullable();
            $table->json('payload');
            $table->timestamp('legacy_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['legacy_source_table', 'source_key'], 'uk_legacy_planoc_hist_source_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_planoc_historiales');
        Schema::dropIfExists('legacy_planoc_congelamientos');
    }
};

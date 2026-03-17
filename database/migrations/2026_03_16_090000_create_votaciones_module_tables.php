<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vot_aportantes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 180);
            $table->string('documento', 40)->unique('va_doc_unq');
            $table->string('correo', 180)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('password');
            $table->timestamp('ultimo_ingreso_at')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('vot_votaciones', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo', 180);
            $table->string('slug', 180)->unique('vv_slug_unq');
            $table->text('descripcion_publica')->nullable();
            $table->string('tipo_votacion', 20)->default('nominal');
            $table->string('logo_path')->nullable();
            $table->unsignedInteger('cupos')->default(1);
            $table->unsignedInteger('max_selecciones')->default(1);
            $table->longText('orden_del_dia')->nullable();
            $table->boolean('aceptacion_obligatoria')->default(true);
            $table->string('estado', 20)->default('borrador');
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('vot_planillas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('votacion_id');
            $table->string('nombre', 180);
            $table->unsignedInteger('numero')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('votacion_id', 'vp_vot_fk')
                ->references('id')
                ->on('vot_votaciones')
                ->cascadeOnDelete();
        });

        Schema::create('vot_candidatos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('votacion_id');
            $table->unsignedBigInteger('planilla_id')->nullable();
            $table->string('nombre', 180);
            $table->string('documento', 40)->nullable();
            $table->string('cargo', 120)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('numero')->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('votacion_id', 'vc_vot_fk')
                ->references('id')
                ->on('vot_votaciones')
                ->cascadeOnDelete();

            $table->foreign('planilla_id', 'vc_pla_fk')
                ->references('id')
                ->on('vot_planillas')
                ->nullOnDelete();
        });

        Schema::create('vot_votos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('votacion_id');
            $table->unsignedBigInteger('aportante_id');
            $table->unsignedBigInteger('planilla_id')->nullable();
            $table->timestamp('acepto_orden_dia_at')->nullable();
            $table->timestamp('voto_emitido_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['votacion_id', 'aportante_id'], 'vv_unq');

            $table->foreign('votacion_id', 'vv_vot_fk')
                ->references('id')
                ->on('vot_votaciones')
                ->cascadeOnDelete();

            $table->foreign('aportante_id', 'vv_apo_fk')
                ->references('id')
                ->on('vot_aportantes')
                ->cascadeOnDelete();

            $table->foreign('planilla_id', 'vv_pla_fk')
                ->references('id')
                ->on('vot_planillas')
                ->nullOnDelete();
        });

        Schema::create('vot_voto_detalles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('voto_id');
            $table->unsignedBigInteger('candidato_id');
            $table->timestamps();

            $table->unique(['voto_id', 'candidato_id'], 'vvd_unq');

            $table->foreign('voto_id', 'vvd_voto_fk')
                ->references('id')
                ->on('vot_votos')
                ->cascadeOnDelete();

            $table->foreign('candidato_id', 'vvd_can_fk')
                ->references('id')
                ->on('vot_candidatos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vot_voto_detalles');
        Schema::dropIfExists('vot_votos');
        Schema::dropIfExists('vot_candidatos');
        Schema::dropIfExists('vot_planillas');
        Schema::dropIfExists('vot_votaciones');
        Schema::dropIfExists('vot_aportantes');
    }
};

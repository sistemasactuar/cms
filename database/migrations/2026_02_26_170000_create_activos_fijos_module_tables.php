<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('para_tipo_activo', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 120);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('proc_activofijo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo')->nullable();
            $table->string('descripcion', 255);
            $table->string('marca', 120)->nullable();
            $table->string('modelo', 120)->nullable();
            $table->string('serie', 120)->nullable();
            $table->string('codigo', 120)->nullable();
            $table->unsignedBigInteger('para_sede_id')->nullable();
            $table->string('responsable', 180)->nullable();
            $table->decimal('valor', 18, 2)->nullable();
            $table->string('condicion', 50)->nullable();
            $table->text('observacion')->nullable();

            $table->unsignedTinyInteger('unidad_cd')->nullable();
            $table->string('hdd1', 120)->nullable();
            $table->unsignedTinyInteger('tipo_disco')->nullable();
            $table->string('hdd2', 120)->nullable();
            $table->unsignedTinyInteger('tipo_disco2')->nullable();
            $table->string('fuente', 120)->nullable();
            $table->string('cargador', 120)->nullable();
            $table->string('procesador', 180)->nullable();
            $table->string('ram', 120)->nullable();
            $table->string('pantalla', 120)->nullable();
            $table->string('pantalla_tam', 120)->nullable();
            $table->string('t_video', 180)->nullable();
            $table->string('teclado', 180)->nullable();
            $table->string('mouse', 180)->nullable();
            $table->string('so', 180)->nullable();
            $table->string('sof', 180)->nullable();
            $table->string('compresor', 180)->nullable();
            $table->string('adobe', 180)->nullable();
            $table->string('antivirus', 180)->nullable();
            $table->string('explorador1', 180)->nullable();
            $table->string('explorador2', 180)->nullable();
            $table->string('explorador3', 180)->nullable();
            $table->text('prog_adicionales')->nullable();
            $table->string('ups_capacidad', 120)->nullable();
            $table->string('telecom_puertos', 120)->nullable();
            $table->string('telecom_pe', 120)->nullable();
            $table->unsignedTinyInteger('vigil_tipo')->nullable();
            $table->string('vigil_puertos', 120)->nullable();
            $table->string('vigil_capacidad', 120)->nullable();
            $table->unsignedTinyInteger('vigil_poe')->nullable();
            $table->string('acces_point_rango', 120)->nullable();

            $table->unsignedBigInteger('por')->nullable();
            $table->string('visto', 120)->nullable();

            $table->unsignedBigInteger('usuadi')->nullable();
            $table->date('fecadi')->nullable();
            $table->time('horadi')->nullable();
            $table->unsignedBigInteger('usumod')->nullable();
            $table->date('fecmod')->nullable();
            $table->time('hormod')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });

        Schema::create('proc_mante_activofijo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('proc_activofijo')->cascadeOnDelete();
            $table->unsignedTinyInteger('tipo_M');
            $table->text('observacion_M');

            $table->unsignedBigInteger('usuadi')->nullable();
            $table->date('fecadi')->nullable();
            $table->time('horadi')->nullable();
            $table->unsignedBigInteger('usumod')->nullable();
            $table->date('fecmod')->nullable();
            $table->time('hormod')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });

        DB::table('para_tipo_activo')->insert([
            ['id' => 1, 'tipo' => 'PC ESCRITORIO', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tipo' => 'TODO EN UNO', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'tipo' => 'PORTATIL', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'tipo' => 'SERVIDOR', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'tipo' => 'UPS', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'tipo' => 'SWITCH', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'tipo' => 'ROUTER / UTM', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'tipo' => 'NVR / DVR', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'tipo' => 'ACCESS POINT', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proc_mante_activofijo');
        Schema::dropIfExists('proc_activofijo');
        Schema::dropIfExists('para_tipo_activo');
    }
};

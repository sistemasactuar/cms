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
        Schema::create('para_macro_tipo_activo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('codigo', 30)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        DB::table('para_macro_tipo_activo')->insert([
            ['nombre' => 'TECNOLOGIA', 'codigo' => 'TEC', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'MUEBLES Y ENSERES', 'codigo' => 'MUE', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'ALMACEN Y DOTACION', 'codigo' => 'ALM', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'SEGURIDAD ELECTRONICA', 'codigo' => 'SEG', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'REDES Y TELECOMUNICACIONES', 'codigo' => 'RED', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'VEHICULOS', 'codigo' => 'VEH', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'OTROS', 'codigo' => 'OTR', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('para_tipo_activo', function (Blueprint $table) {
            $table->foreignId('macro_tipo_id')
                ->nullable()
                ->after('id')
                ->constrained('para_macro_tipo_activo')
                ->nullOnDelete();
        });

        $macroIds = DB::table('para_macro_tipo_activo')->pluck('id', 'codigo');
        $tecnologiaId = $macroIds['TEC'] ?? null;
        $mueblesId = $macroIds['MUE'] ?? null;
        $almacenId = $macroIds['ALM'] ?? null;
        $seguridadId = $macroIds['SEG'] ?? null;
        $redesId = $macroIds['RED'] ?? null;
        $vehiculosId = $macroIds['VEH'] ?? null;
        $otrosId = $macroIds['OTR'] ?? null;

        if ($tecnologiaId !== null) {
            DB::table('para_tipo_activo')
                ->whereIn('id', [1, 2, 3, 4, 5, 6, 7, 8, 9])
                ->update(['macro_tipo_id' => $tecnologiaId, 'updated_at' => now()]);
        }

        $extraTipos = [
            ['tipo' => 'IMPRESORA', 'macro_tipo_id' => $tecnologiaId],
            ['tipo' => 'ESCANER', 'macro_tipo_id' => $tecnologiaId],
            ['tipo' => 'VIDEO BEAM', 'macro_tipo_id' => $tecnologiaId],
            ['tipo' => 'MONITOR', 'macro_tipo_id' => $tecnologiaId],
            ['tipo' => 'TABLET', 'macro_tipo_id' => $tecnologiaId],
            ['tipo' => 'TELEFONO IP', 'macro_tipo_id' => $redesId],
            ['tipo' => 'FIREWALL', 'macro_tipo_id' => $redesId],
            ['tipo' => 'PATCH PANEL', 'macro_tipo_id' => $redesId],
            ['tipo' => 'CAMARA', 'macro_tipo_id' => $seguridadId],
            ['tipo' => 'CONTROL DE ACCESO', 'macro_tipo_id' => $seguridadId],
            ['tipo' => 'ESCRITORIO', 'macro_tipo_id' => $mueblesId],
            ['tipo' => 'SILLA', 'macro_tipo_id' => $mueblesId],
            ['tipo' => 'ARCHIVADOR', 'macro_tipo_id' => $mueblesId],
            ['tipo' => 'MESA', 'macro_tipo_id' => $mueblesId],
            ['tipo' => 'ESTANTERIA', 'macro_tipo_id' => $mueblesId],
            ['tipo' => 'LOCKER', 'macro_tipo_id' => $almacenId],
            ['tipo' => 'RACK', 'macro_tipo_id' => $almacenId],
            ['tipo' => 'CARRO DE CARGA', 'macro_tipo_id' => $almacenId],
            ['tipo' => 'MOTOCICLETA', 'macro_tipo_id' => $vehiculosId],
            ['tipo' => 'AUTOMOVIL', 'macro_tipo_id' => $vehiculosId],
            ['tipo' => 'OTRO', 'macro_tipo_id' => $otrosId],
        ];

        $nuevosTipos = [];
        foreach ($extraTipos as $tipo) {
            if ($tipo['macro_tipo_id'] === null) {
                continue;
            }

            $nuevosTipos[] = [
                'tipo' => $tipo['tipo'],
                'macro_tipo_id' => $tipo['macro_tipo_id'],
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($nuevosTipos as $tipo) {
            $exists = DB::table('para_tipo_activo')
                ->where('tipo', $tipo['tipo'])
                ->exists();

            if (!$exists) {
                DB::table('para_tipo_activo')->insert($tipo);
            }
        }

        if ($otrosId !== null) {
            DB::table('para_tipo_activo')
                ->whereNull('macro_tipo_id')
                ->update(['macro_tipo_id' => $otrosId, 'updated_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('para_tipo_activo', function (Blueprint $table) {
            $table->dropForeign(['macro_tipo_id']);
            $table->dropColumn('macro_tipo_id');
        });

        Schema::dropIfExists('para_macro_tipo_activo');
    }
};


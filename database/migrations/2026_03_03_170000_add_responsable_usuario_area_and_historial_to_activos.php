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
        if (!Schema::hasColumn('users', 'area')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('area', 120)->nullable();
            });
        }

        if (!Schema::hasColumn('proc_activofijo', 'responsable_user_id')) {
            Schema::table('proc_activofijo', function (Blueprint $table) {
                $table->unsignedBigInteger('responsable_user_id')->nullable();
            });
        }

        if (!Schema::hasTable('proc_activofijo_responsable_historial')) {
            Schema::create('proc_activofijo_responsable_historial', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activo_fijo_id');
                $table->unsignedBigInteger('usuario_anterior_id')->nullable();
                $table->unsignedBigInteger('usuario_nuevo_id')->nullable();
                $table->string('responsable_anterior', 180)->nullable();
                $table->string('responsable_nuevo', 180)->nullable();
                $table->string('motivo', 255)->nullable();
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->timestamp('changed_at')->nullable()->index();
                $table->timestamps();
            });
        }

        $this->addForeignIfMissing(
            table: 'proc_activofijo',
            column: 'responsable_user_id',
            referencedTable: 'users',
            referencedColumn: 'id',
            foreignName: 'fk_paf_resp_user',
            onDelete: 'set null',
        );
        $this->addForeignIfMissing(
            table: 'proc_activofijo_responsable_historial',
            column: 'activo_fijo_id',
            referencedTable: 'proc_activofijo',
            referencedColumn: 'id',
            foreignName: 'fk_afrh_activo',
            onDelete: 'cascade',
        );
        $this->addForeignIfMissing(
            table: 'proc_activofijo_responsable_historial',
            column: 'usuario_anterior_id',
            referencedTable: 'users',
            referencedColumn: 'id',
            foreignName: 'fk_afrh_uant',
            onDelete: 'set null',
        );
        $this->addForeignIfMissing(
            table: 'proc_activofijo_responsable_historial',
            column: 'usuario_nuevo_id',
            referencedTable: 'users',
            referencedColumn: 'id',
            foreignName: 'fk_afrh_unuevo',
            onDelete: 'set null',
        );
        $this->addForeignIfMissing(
            table: 'proc_activofijo_responsable_historial',
            column: 'changed_by_user_id',
            referencedTable: 'users',
            referencedColumn: 'id',
            foreignName: 'fk_afrh_cby',
            onDelete: 'set null',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignIfExists('proc_activofijo', 'fk_paf_resp_user');
        $this->dropForeignIfExists('proc_activofijo_responsable_historial', 'fk_afrh_activo');
        $this->dropForeignIfExists('proc_activofijo_responsable_historial', 'fk_afrh_uant');
        $this->dropForeignIfExists('proc_activofijo_responsable_historial', 'fk_afrh_unuevo');
        $this->dropForeignIfExists('proc_activofijo_responsable_historial', 'fk_afrh_cby');

        Schema::dropIfExists('proc_activofijo_responsable_historial');

        if (Schema::hasColumn('proc_activofijo', 'responsable_user_id')) {
            Schema::table('proc_activofijo', function (Blueprint $table) {
                $table->dropColumn('responsable_user_id');
            });
        }

        if (Schema::hasColumn('users', 'area')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('area');
            });
        }
    }

    private function addForeignIfMissing(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $foreignName,
        string $onDelete = 'cascade'
    ): void {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        if ($this->foreignExists($table, $foreignName) || $this->foreignOnColumnExists($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use (
            $column,
            $referencedTable,
            $referencedColumn,
            $foreignName,
            $onDelete
        ): void {
            $foreign = $blueprint->foreign($column, $foreignName)
                ->references($referencedColumn)
                ->on($referencedTable);

            if ($onDelete === 'set null') {
                $foreign->nullOnDelete();
            } elseif ($onDelete === 'cascade') {
                $foreign->cascadeOnDelete();
            } else {
                $foreign->onDelete($onDelete);
            }
        });
    }

    private function dropForeignIfExists(string $table, string $foreignName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!$this->foreignExists($table, $foreignName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($foreignName): void {
            $blueprint->dropForeign($foreignName);
        });
    }

    private function foreignExists(string $table, string $foreignName): bool
    {
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = ?
               AND CONSTRAINT_NAME = ?
             LIMIT 1',
            [$table, 'FOREIGN KEY', $foreignName]
        );

        return $row !== null;
    }

    private function foreignOnColumnExists(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$table, $column]
        );

        return $row !== null;
    }
};

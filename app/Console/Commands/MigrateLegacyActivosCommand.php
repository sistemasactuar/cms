<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyActivosCommand extends Command
{
    protected $signature = 'activos:migrar-legacy
        {--legacy-host= : Host de la base legacy}
        {--legacy-port= : Puerto de la base legacy}
        {--legacy-database= : Nombre de la base legacy}
        {--legacy-username= : Usuario de la base legacy}
        {--legacy-password= : Password de la base legacy}
        {--legacy-prefix= : Prefijo de tablas legacy}
        {--chunk=500 : Tamano de lote para migracion}
        {--dry-run : Solo mostrar conteos, sin escribir}
        {--truncate : Vaciar tablas destino antes de migrar}
        {--force : No pedir confirmacion en acciones destructivas}';

    protected $description = 'Migra Activos Fijos y Mantenimientos desde base legacy (CodeIgniter) al modulo nuevo.';

    public function handle(): int
    {
        try {
            $legacyConnection = $this->configureLegacyConnection();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $targetConnection = config('database.default');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $tables = [
            'tipos' => $this->legacyTableName('para_tipo_activo'),
            'activos' => $this->legacyTableName('proc_activofijo'),
            'mantenimientos' => $this->legacyTableName('proc_mante_activofijo'),
        ];

        if (!$this->validateSourceTables($legacyConnection, $tables)) {
            return self::FAILURE;
        }

        $this->info('Conexion legacy OK.');
        $this->line("Fuente: {$legacyConnection} | Destino: {$targetConnection}");

        $sourceCounts = [
            'tipos' => DB::connection($legacyConnection)->table($tables['tipos'])->count(),
            'activos' => DB::connection($legacyConnection)->table($tables['activos'])->count(),
            'mantenimientos' => DB::connection($legacyConnection)->table($tables['mantenimientos'])->count(),
        ];

        $this->table(
            ['Tabla', 'Registros fuente'],
            [
                ['para_tipo_activo', $sourceCounts['tipos']],
                ['proc_activofijo', $sourceCounts['activos']],
                ['proc_mante_activofijo', $sourceCounts['mantenimientos']],
            ]
        );

        if ($this->option('dry-run')) {
            $this->comment('Dry-run finalizado. No se realizaron cambios.');
            return self::SUCCESS;
        }

        if ($this->option('truncate')) {
            if (!$this->option('force') && !$this->confirm('Se vaciaran tablas destino. Desea continuar?')) {
                $this->warn('Operacion cancelada.');
                return self::INVALID;
            }
            $this->truncateTargetTables();
        }

        $tiposStats = $this->migrateTableById(
            sourceConnection: $legacyConnection,
            sourceTable: $tables['tipos'],
            targetTable: 'para_tipo_activo',
            chunkSize: $chunkSize,
            validateRow: null,
        );

        $activosStats = $this->migrateTableById(
            sourceConnection: $legacyConnection,
            sourceTable: $tables['activos'],
            targetTable: 'proc_activofijo',
            chunkSize: $chunkSize,
            validateRow: null,
        );

        $mantenimientosStats = $this->migrateTableById(
            sourceConnection: $legacyConnection,
            sourceTable: $tables['mantenimientos'],
            targetTable: 'proc_mante_activofijo',
            chunkSize: $chunkSize,
            validateRow: function (array $row): bool {
                if (empty($row['equipo_id'])) {
                    return false;
                }

                return DB::table('proc_activofijo')->where('id', $row['equipo_id'])->exists();
            },
        );

        $this->table(
            ['Tabla', 'Procesados', 'Nuevos', 'Actualizados', 'Ignorados'],
            [
                ['para_tipo_activo', $tiposStats['processed'], $tiposStats['created'], $tiposStats['updated'], $tiposStats['skipped']],
                ['proc_activofijo', $activosStats['processed'], $activosStats['created'], $activosStats['updated'], $activosStats['skipped']],
                ['proc_mante_activofijo', $mantenimientosStats['processed'], $mantenimientosStats['created'], $mantenimientosStats['updated'], $mantenimientosStats['skipped']],
            ]
        );

        $this->info('Migracion finalizada.');

        return self::SUCCESS;
    }

    private function configureLegacyConnection(): string
    {
        $connection = 'legacy_import';

        $legacyHost = $this->option('legacy-host') ?: env('LEGACY_DB_HOST', env('DB_HOST', '127.0.0.1'));
        $legacyPort = $this->option('legacy-port') ?: env('LEGACY_DB_PORT', env('DB_PORT', '3306'));
        $legacyDatabase = $this->option('legacy-database') ?: env('LEGACY_DB_DATABASE');
        $legacyUsername = $this->option('legacy-username') ?: env('LEGACY_DB_USERNAME', env('DB_USERNAME'));
        $legacyPassword = $this->option('legacy-password') ?: env('LEGACY_DB_PASSWORD', env('DB_PASSWORD'));

        if (empty($legacyDatabase)) {
            throw new \RuntimeException('Debes indicar la BD legacy con --legacy-database o LEGACY_DB_DATABASE.');
        }

        config([
            "database.connections.{$connection}" => [
                'driver' => 'mysql',
                'host' => $legacyHost,
                'port' => $legacyPort,
                'database' => $legacyDatabase,
                'username' => $legacyUsername,
                'password' => $legacyPassword,
                'charset' => env('LEGACY_DB_CHARSET', 'utf8mb4'),
                'collation' => env('LEGACY_DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => false,
                'engine' => 'InnoDB',
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [],
            ],
        ]);

        DB::connection($connection)->getPdo();

        return $connection;
    }

    private function legacyTableName(string $baseTable): string
    {
        $prefix = (string) ($this->option('legacy-prefix') ?: env('LEGACY_DB_PREFIX', ''));
        return $prefix . $baseTable;
    }

    private function validateSourceTables(string $legacyConnection, array $tables): bool
    {
        foreach ($tables as $label => $table) {
            if (!Schema::connection($legacyConnection)->hasTable($table)) {
                $this->error("No existe tabla fuente {$table} ({$label}).");
                return false;
            }
        }

        return true;
    }

    private function truncateTargetTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('proc_mante_activofijo')->truncate();
        DB::table('proc_activofijo')->truncate();
        DB::table('para_tipo_activo')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->warn('Tablas destino vaciadas.');
    }

    /**
     * @param callable(array):bool|null $validateRow
     */
    private function migrateTableById(
        string $sourceConnection,
        string $sourceTable,
        string $targetTable,
        int $chunkSize,
        ?callable $validateRow = null
    ): array {
        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        $targetColumns = Schema::getColumnListing($targetTable);
        $sourceColumns = Schema::connection($sourceConnection)->getColumnListing($sourceTable);
        $commonColumns = array_values(array_intersect($sourceColumns, $targetColumns));

        if (!in_array('id', $commonColumns, true)) {
            throw new \RuntimeException("La tabla {$sourceTable} no tiene columna id compatible.");
        }

        $updateColumns = array_values(array_filter($commonColumns, fn($column) => $column !== 'id'));
        if (in_array('updated_at', $targetColumns, true) && !in_array('updated_at', $updateColumns, true)) {
            $updateColumns[] = 'updated_at';
        }

        DB::connection($sourceConnection)
            ->table($sourceTable)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use (
                &$stats,
                $commonColumns,
                $updateColumns,
                $targetColumns,
                $targetTable,
                $validateRow
            ) {
                $payload = [];

                foreach ($rows as $row) {
                    $item = [];
                    foreach ($commonColumns as $column) {
                        $item[$column] = $this->normalizeValue($column, $row->{$column} ?? null);
                    }

                    if (in_array('created_at', $targetColumns, true) && !array_key_exists('created_at', $item)) {
                        $item['created_at'] = now();
                    }
                    if (in_array('updated_at', $targetColumns, true) && !array_key_exists('updated_at', $item)) {
                        $item['updated_at'] = now();
                    }

                    if ($validateRow && !$validateRow($item)) {
                        $stats['skipped']++;
                        continue;
                    }

                    $payload[] = $item;
                }

                if ($payload === []) {
                    return;
                }

                $ids = array_values(array_unique(array_map(fn($item) => $item['id'], $payload)));
                $existingIds = DB::table($targetTable)->whereIn('id', $ids)->pluck('id')->all();
                $existingIdsLookup = array_fill_keys($existingIds, true);

                foreach ($payload as $item) {
                    if (isset($existingIdsLookup[$item['id']])) {
                        $stats['updated']++;
                    } else {
                        $stats['created']++;
                    }
                }

                DB::table($targetTable)->upsert($payload, ['id'], $updateColumns);

                $stats['processed'] += count($payload);
            }, 'id');

        return $stats;
    }

    private function normalizeValue(string $column, mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                $value = null;
            }
        }

        if ($value === null) {
            return null;
        }

        if (str_ends_with($column, '_at')) {
            return $value;
        }

        if (in_array($column, ['fecadi', 'fecmod'], true)) {
            return $value;
        }

        if (in_array($column, ['horadi', 'hormod'], true)) {
            return $value;
        }

        return $value;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyPlanocCommand extends Command
{
    protected $signature = 'planoc:migrar-legacy
        {--legacy-host= : Host de la base legacy}
        {--legacy-port= : Puerto de la base legacy}
        {--legacy-database= : Nombre de la base legacy}
        {--legacy-username= : Usuario de la base legacy}
        {--legacy-password= : Password de la base legacy}
        {--legacy-prefix= : Prefijo de tablas legacy}
        {--chunk=500 : Tamano de lote para migracion}
        {--only=all : base,traslados,restructuras,reprogramaciones,sosemp}
        {--dry-run : Solo mostrar conteos, sin escribir}
        {--truncate : Vaciar staging antes de migrar}
        {--force : No pedir confirmacion en acciones destructivas}';

    protected $description = 'Migra temporalmente datos legacy de cartera/planoc desde CodeIgniter a tablas staging del proyecto nuevo.';

    public function handle(): int
    {
        try {
            $legacyConnection = $this->configureLegacyConnection();
            $imports = $this->resolveImports();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        if (!$this->validateTargetTables()) {
            return self::FAILURE;
        }

        if (!$this->validateSourceTables($legacyConnection, $imports)) {
            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $targetConnection = config('database.default');

        $this->info('Conexion legacy OK.');
        $this->line("Fuente: {$legacyConnection} | Destino: {$targetConnection}");

        $sourceRows = [];
        foreach ($imports as $key => $definition) {
            $sourceRows[] = [
                $definition['label'],
                $definition['source_table'],
                DB::connection($legacyConnection)->table($definition['source_table'])->count(),
            ];
        }

        $this->table(['Segmento', 'Tabla fuente', 'Registros fuente'], $sourceRows);

        if ($this->option('dry-run')) {
            $this->comment('Dry-run finalizado. No se realizaron cambios.');
            return self::SUCCESS;
        }

        if ($this->option('truncate')) {
            if (!$this->option('force') && !$this->confirm('Se vaciaran las tablas staging seleccionadas. Desea continuar?')) {
                $this->warn('Operacion cancelada.');
                return self::INVALID;
            }

            $this->truncateTargetTables($imports);
        }

        $statsRows = [];

        foreach ($imports as $definition) {
            $stats = $this->migrateSourceTable(
                sourceConnection: $legacyConnection,
                sourceTable: $definition['source_table'],
                targetTable: $definition['target_table'],
                chunkSize: $chunkSize,
                definition: $definition,
            );

            $statsRows[] = [
                $definition['label'],
                $stats['processed'],
                $stats['created'],
                $stats['updated'],
                $stats['skipped'],
            ];
        }

        $this->table(['Segmento', 'Procesados', 'Nuevos', 'Actualizados', 'Ignorados'], $statsRows);
        $this->info('Migracion temporal de planoc finalizada.');

        return self::SUCCESS;
    }

    private function configureLegacyConnection(): string
    {
        $connection = 'legacy_import';
        $mysqlConfig = config('database.connections.mysql', []);

        $legacyHost = $this->option('legacy-host')
            ?: env('LEGACY_DB_HOST')
            ?: ($mysqlConfig['host'] ?? '127.0.0.1');
        $legacyPort = $this->option('legacy-port')
            ?: env('LEGACY_DB_PORT')
            ?: ($mysqlConfig['port'] ?? '3306');
        $legacyDatabase = $this->option('legacy-database') ?: env('LEGACY_DB_DATABASE');
        $legacyUsername = $this->option('legacy-username')
            ?: env('LEGACY_DB_USERNAME')
            ?: ($mysqlConfig['username'] ?? '');
        $legacyPassword = $this->option('legacy-password')
            ?: env('LEGACY_DB_PASSWORD')
            ?: ($mysqlConfig['password'] ?? '');
        $legacyCharset = env('LEGACY_DB_CHARSET') ?: ($mysqlConfig['charset'] ?? 'utf8mb4');
        $legacyCollation = env('LEGACY_DB_COLLATION') ?: ($mysqlConfig['collation'] ?? 'utf8mb4_unicode_ci');

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
                'charset' => $legacyCharset,
                'collation' => $legacyCollation,
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

    private function resolveImports(): array
    {
        $available = [
            'base' => [
                'label' => 'Base congelamiento',
                'source_table' => $this->legacyTableName('act_cartera_congelamiento'),
                'target_table' => 'legacy_planoc_congelamientos',
                'history_type' => null,
            ],
            'traslados' => [
                'label' => 'Historico traslados',
                'source_table' => $this->legacyTableName('act_cartera_hist_traslados'),
                'target_table' => 'legacy_planoc_historiales',
                'history_type' => 'traslados',
            ],
            'restructuras' => [
                'label' => 'Historico reestructuras',
                'source_table' => $this->legacyTableName('act_cartera_hist_restructura'),
                'target_table' => 'legacy_planoc_historiales',
                'history_type' => 'restructuras',
            ],
            'reprogramaciones' => [
                'label' => 'Historico reprogramaciones',
                'source_table' => $this->legacyTableName('act_cartera_hist_reprograma'),
                'target_table' => 'legacy_planoc_historiales',
                'history_type' => 'reprogramaciones',
            ],
            'sosemp' => [
                'label' => 'Historico sostenibilidad',
                'source_table' => $this->legacyTableName('act_cartera_hist_sost_empre'),
                'target_table' => 'legacy_planoc_historiales',
                'history_type' => 'sostenibilidad',
            ],
        ];

        $selected = trim((string) $this->option('only'));
        if ($selected === '' || $selected === 'all') {
            return $available;
        }

        $segments = array_values(array_filter(array_map(
            static fn(string $item): string => trim(strtolower($item)),
            explode(',', $selected)
        )));

        $imports = [];
        foreach ($segments as $segment) {
            if (!isset($available[$segment])) {
                $valid = implode(', ', array_keys($available));
                throw new \RuntimeException("Segmento [{$segment}] no soportado. Usa: {$valid}.");
            }

            $imports[$segment] = $available[$segment];
        }

        return $imports;
    }

    private function legacyTableName(string $baseTable): string
    {
        $prefix = (string) ($this->option('legacy-prefix') ?: env('LEGACY_DB_PREFIX', ''));
        return $prefix . $baseTable;
    }

    private function validateTargetTables(): bool
    {
        foreach (['legacy_planoc_congelamientos', 'legacy_planoc_historiales'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->error("No existe tabla destino {$table}. Ejecuta php artisan migrate.");
                return false;
            }
        }

        return true;
    }

    private function validateSourceTables(string $legacyConnection, array $imports): bool
    {
        foreach ($imports as $definition) {
            if (!Schema::connection($legacyConnection)->hasTable($definition['source_table'])) {
                $this->error("No existe tabla fuente {$definition['source_table']}.");
                return false;
            }
        }

        return true;
    }

    private function truncateTargetTables(array $imports): void
    {
        $targets = array_values(array_unique(array_column($imports, 'target_table')));

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($targets as $target) {
            DB::table($target)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->warn('Tablas staging seleccionadas vaciadas.');
    }

    private function migrateSourceTable(
        string $sourceConnection,
        string $sourceTable,
        string $targetTable,
        int $chunkSize,
        array $definition
    ): array {
        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        $sourceColumns = Schema::connection($sourceConnection)->getColumnListing($sourceTable);
        $orderColumn = in_array('id', $sourceColumns, true) ? 'id' : ($sourceColumns[0] ?? null);

        if ($orderColumn === null) {
            return $stats;
        }

        $targetColumns = Schema::getColumnListing($targetTable);
        $updateColumns = array_values(array_filter($targetColumns, static fn(string $column): bool => !in_array($column, ['id', 'created_at'], true)));

        $processChunk = function ($rows) use (&$stats, $targetTable, $updateColumns, $definition): void {
            $payload = [];

            foreach ($rows as $row) {
                $item = $this->buildStagingPayload((array) $row, $definition);

                if ($item === null) {
                    $stats['skipped']++;
                    continue;
                }

                $payload[] = $item;
            }

            if ($payload === []) {
                return;
            }

            $existingLookup = DB::table($targetTable)
                ->where('legacy_source_table', $definition['source_table'])
                ->whereIn('source_key', array_column($payload, 'source_key'))
                ->pluck('source_key')
                ->all();

            $existingLookup = array_fill_keys($existingLookup, true);

            foreach ($payload as $item) {
                if (isset($existingLookup[$item['source_key']])) {
                    $stats['updated']++;
                } else {
                    $stats['created']++;
                }
            }

            DB::table($targetTable)->upsert($payload, ['legacy_source_table', 'source_key'], $updateColumns);
            $stats['processed'] += count($payload);
        };

        $builder = DB::connection($sourceConnection)
            ->table($sourceTable)
            ->orderBy($orderColumn);

        if ($orderColumn === 'id') {
            $builder->chunkById($chunkSize, $processChunk, 'id');
        } else {
            $builder->chunk($chunkSize, $processChunk);
        }

        return $stats;
    }

    private function buildStagingPayload(array $row, array $definition): ?array
    {
        $payload = $this->normalizePayload($row);
        $sourceKey = $this->buildSourceKey($payload, $definition['source_table']);

        if ($sourceKey === '') {
            return null;
        }

        $timestamp = now();

        if ($definition['target_table'] === 'legacy_planoc_congelamientos') {
            return [
                'legacy_source_table' => $definition['source_table'],
                'source_key' => $sourceKey,
                'legacy_id' => $this->extractInteger($payload, ['id']),
                'legacy_obligacion' => $this->extractString($payload, ['Obligacion', 'obligacion']),
                'legacy_cedula_cliente' => $this->extractString($payload, ['Cedula_Cliente', 'cedula_cliente', 'documento']),
                'legacy_nombre_cliente' => $this->extractString($payload, ['Nombre_Cliente', 'nombre_cliente', 'nombre']),
                'legacy_nombre_promotor' => $this->extractString($payload, ['Npmbre_Prom', 'Nombre_Prom', 'nombre_prom', 'promotor']),
                'legacy_sucursal' => $this->extractString($payload, ['Sucursal', 'sucursal']),
                'legacy_saldo_actual' => $this->extractDecimal($payload, ['Saldo_Actual', 'saldo_actual']),
                'legacy_dias_linix' => $this->extractInteger($payload, ['Dias_linix', 'dias_linix']),
                'legacy_dias_actuar' => $this->extractInteger($payload, ['Dias_actuar', 'dias_actuar']),
                'legacy_fecha_pago' => $this->extractString($payload, ['fecha_pago', 'Fecha_Pago']),
                'legacy_email' => $this->extractString($payload, ['email', 'Email']),
                'legacy_whatsapp' => $this->extractString($payload, ['whatsapp', 'Whatsapp']),
                'legacy_activo' => $this->extractBoolean($payload, ['activo']),
                'legacy_fecadi' => $this->extractString($payload, ['fecadi', 'Fecadi']),
                'legacy_fecmod' => $this->extractString($payload, ['fecmod', 'Fecmod']),
                'legacy_usuadi' => $this->extractInteger($payload, ['usuadi', 'Usuadi']),
                'legacy_usumod' => $this->extractInteger($payload, ['usumod', 'Usumod']),
                'payload' => $this->encodePayload($payload),
                'legacy_synced_at' => $timestamp,
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ];
        }

        return [
            'tipo_historial' => $definition['history_type'],
            'legacy_source_table' => $definition['source_table'],
            'source_key' => $sourceKey,
            'legacy_id' => $this->extractInteger($payload, ['id']),
            'legacy_obligacion' => $this->extractString($payload, ['Obligacion', 'obligacion']),
            'legacy_cedula_cliente' => $this->extractString($payload, ['Cedula_Cliente', 'cedula_cliente', 'documento']),
            'legacy_periodo' => $this->extractString($payload, ['periodo', 'Periodo']),
            'legacy_activo' => $this->extractBoolean($payload, ['activo']),
            'legacy_fecadi' => $this->extractString($payload, ['fecadi', 'Fecadi']),
            'legacy_usuadi' => $this->extractInteger($payload, ['usuadi', 'Usuadi']),
            'payload' => $this->encodePayload($payload),
            'legacy_synced_at' => $timestamp,
            'updated_at' => $timestamp,
            'created_at' => $timestamp,
        ];
    }

    private function buildSourceKey(array $payload, string $sourceTable): string
    {
        $legacyId = $this->extractInteger($payload, ['id']);
        if ($legacyId !== null) {
            return 'id:' . $legacyId;
        }

        $obligacion = $this->extractString($payload, ['Obligacion', 'obligacion']);
        $cedula = $this->extractString($payload, ['Cedula_Cliente', 'cedula_cliente', 'documento']);
        $periodo = $this->extractString($payload, ['periodo', 'Periodo']);
        $activo = $this->extractString($payload, ['activo']);

        $key = implode('|', [$sourceTable, $obligacion, $cedula, $periodo, $activo]);
        $key = trim($key, '|');

        if ($key === '' || $key === $sourceTable) {
            return '';
        }

        return 'hash:' . sha1($key);
    }

    private function normalizePayload(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function encodePayload(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)
            ?: '{}';
    }

    private function extractString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            if ($value === null) {
                return null;
            }

            $value = trim((string) $value);
            return $value === '' ? null : $value;
        }

        return null;
    }

    private function extractInteger(array $payload, array $keys): ?int
    {
        $value = $this->extractString($payload, $keys);

        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^\d-]/', '', $value);
        if ($normalized === null || $normalized === '' || $normalized === '-') {
            return null;
        }

        return (int) $normalized;
    }

    private function extractDecimal(array $payload, array $keys): ?float
    {
        $value = $this->extractString($payload, $keys);

        if ($value === null) {
            return null;
        }

        $normalized = str_replace(['$', ' '], '', $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/[^0-9.\-]/', '', $normalized);

        if ($normalized === null || $normalized === '' || $normalized === '-' || $normalized === '.') {
            return null;
        }

        return round((float) $normalized, 2);
    }

    private function extractBoolean(array $payload, array $keys): ?bool
    {
        $value = $this->extractString($payload, $keys);

        if ($value === null) {
            return null;
        }

        return match (strtolower($value)) {
            '1', 'true', 'si', 'sí', 'yes' => true,
            '0', 'false', 'no' => false,
            default => is_numeric($value) ? ((int) $value) === 1 : null,
        };
    }
}

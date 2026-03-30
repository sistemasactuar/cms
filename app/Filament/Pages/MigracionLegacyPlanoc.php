<?php

namespace App\Filament\Pages;

use App\Models\LegacyPlanocCongelamiento;
use App\Models\LegacyPlanocHistorial;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MigracionLegacyPlanoc extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Migracion Legacy Planoc';
    protected static ?string $title = 'Migracion Legacy Planoc';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.migracion-legacy-planoc';

    public ?string $legacy_host = null;
    public ?string $legacy_port = null;
    public ?string $legacy_database = null;
    public ?string $legacy_username = null;
    public ?string $legacy_password = null;
    public ?string $legacy_prefix = null;
    public array $segmentos = ['base', 'traslados', 'restructuras', 'reprogramaciones', 'sosemp'];
    public bool $truncate = false;
    public string $lastOutput = '';
    public array $stagingStats = [];

    public function mount(): void
    {
        $mysqlConfig = config('database.connections.mysql', []);

        $this->form->fill([
            'legacy_host' => $mysqlConfig['host'] ?? '127.0.0.1',
            'legacy_port' => (string) ($mysqlConfig['port'] ?? '3306'),
            'legacy_database' => env('LEGACY_DB_DATABASE'),
            'legacy_username' => $mysqlConfig['username'] ?? null,
            'legacy_password' => $mysqlConfig['password'] ?? null,
            'legacy_prefix' => env('LEGACY_DB_PREFIX'),
            'segmentos' => $this->segmentos,
            'truncate' => false,
        ]);

        $this->refreshStagingStats();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()?->hasAnyRole(['admin', 'Superadmin', 'superadmin']);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()?->hasAnyRole(['admin', 'Superadmin', 'superadmin']);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Conexion legacy')
                ->description('Este formulario carga temporalmente datos del modulo planoc del sistema viejo a tablas staging del proyecto nuevo.')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('legacy_host')
                                ->label('Host')
                                ->required(),
                            Forms\Components\TextInput::make('legacy_port')
                                ->label('Puerto')
                                ->required(),
                            Forms\Components\TextInput::make('legacy_database')
                                ->label('Base legacy')
                                ->helperText('Si la base legacy no esta en este mismo MySQL, ajusta host, usuario y password.')
                                ->required(),
                            Forms\Components\TextInput::make('legacy_username')
                                ->label('Usuario'),
                            Forms\Components\TextInput::make('legacy_password')
                                ->label('Password')
                                ->password()
                                ->revealable(),
                            Forms\Components\TextInput::make('legacy_prefix')
                                ->label('Prefijo tablas')
                                ->placeholder('Opcional'),
                        ]),
                ]),
            Forms\Components\Section::make('Segmentos a migrar')
                ->schema([
                    Forms\Components\CheckboxList::make('segmentos')
                        ->label('Segmentos')
                        ->options([
                            'base' => 'Base congelamiento',
                            'traslados' => 'Historico traslados',
                            'restructuras' => 'Historico reestructuras',
                            'reprogramaciones' => 'Historico reprogramaciones',
                            'sosemp' => 'Historico sostenibilidad',
                        ])
                        ->columns(2)
                        ->required(),
                    Forms\Components\Toggle::make('truncate')
                        ->label('Vaciar staging antes de migrar')
                        ->helperText('Solo aplica en la migracion real. El modo Probar no escribe datos.')
                        ->default(false),
                ]),
        ];
    }

    public function dryRun(): void
    {
        $this->runMigration(true);
    }

    public function migrateData(): void
    {
        $this->runMigration(false);
    }

    private function runMigration(bool $dryRun): void
    {
        $state = $this->form->getState();
        $segmentos = array_values(array_filter($state['segmentos'] ?? []));

        if ($segmentos === []) {
            Notification::make()
                ->title('Selecciona al menos un segmento')
                ->warning()
                ->send();

            return;
        }

        $parameters = [
            '--legacy-host' => $state['legacy_host'] ?: null,
            '--legacy-port' => $state['legacy_port'] ?: null,
            '--legacy-database' => $state['legacy_database'] ?: null,
            '--legacy-username' => $state['legacy_username'] ?: null,
            '--legacy-password' => $state['legacy_password'] ?: null,
            '--legacy-prefix' => $state['legacy_prefix'] ?: null,
            '--only' => implode(',', $segmentos),
        ];

        if ($dryRun) {
            $parameters['--dry-run'] = true;
        } elseif (!empty($state['truncate'])) {
            $parameters['--truncate'] = true;
            $parameters['--force'] = true;
        }

        $parameters = array_filter($parameters, static fn($value): bool => $value !== null && $value !== '');

        $exitCode = Artisan::call('planoc:migrar-legacy', $parameters);
        $this->lastOutput = trim(Artisan::output());
        $this->refreshStagingStats();

        Notification::make()
            ->title($exitCode === 0 ? ($dryRun ? 'Prueba completada' : 'Migracion completada') : 'La migracion reporto un error')
            ->body($this->lastOutput !== '' ? $this->firstOutputLine($this->lastOutput) : null)
            ->color($exitCode === 0 ? 'success' : 'danger')
            ->send();
    }

    private function refreshStagingStats(): void
    {
        $this->stagingStats = [
            'congelamientos' => Schema::hasTable('legacy_planoc_congelamientos')
                ? LegacyPlanocCongelamiento::query()->count()
                : 0,
            'traslados' => Schema::hasTable('legacy_planoc_historiales')
                ? LegacyPlanocHistorial::query()->where('tipo_historial', 'traslados')->count()
                : 0,
            'restructuras' => Schema::hasTable('legacy_planoc_historiales')
                ? LegacyPlanocHistorial::query()->where('tipo_historial', 'restructuras')->count()
                : 0,
            'reprogramaciones' => Schema::hasTable('legacy_planoc_historiales')
                ? LegacyPlanocHistorial::query()->where('tipo_historial', 'reprogramaciones')->count()
                : 0,
            'sosemp' => Schema::hasTable('legacy_planoc_historiales')
                ? LegacyPlanocHistorial::query()->where('tipo_historial', 'sostenibilidad')->count()
                : 0,
        ];
    }

    private function firstOutputLine(string $output): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($output)) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                return $line;
            }
        }

        return 'Proceso finalizado.';
    }
}

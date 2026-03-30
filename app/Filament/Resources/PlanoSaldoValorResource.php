<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanoSaldoValorResource\Pages;
use App\Http\Controllers\PlanoSaldoValorExportDownloadController;
use App\Models\PlanoSaldoValor;
use App\Services\PlanoSaldoValorCardImageService;
use App\Services\PlanoSaldoValorImportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PlanoSaldoValorResource extends Resource
{
    protected static ?string $model = PlanoSaldoValor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Reporte Saldos y Valores';
    protected static ?string $modelLabel = 'Saldos y Valores';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('obligacion')
                    ->label('Numero de credito')
                    ->required(),
                Forms\Components\TextInput::make('cc')
                    ->label('Documento')
                    ->required(),
                Forms\Components\TextInput::make('nombres')
                    ->label('Nombres'),
                Forms\Components\TextInput::make('apellidos')
                    ->label('Apellidos'),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->label('Fecha de nacimiento')
                    ->helperText('Opcional, pero recomendada si el portal publico validara por documento y fecha de nacimiento.'),
                Forms\Components\TextInput::make('valor_reportar')
                    ->label('Valor a reportar')
                    ->numeric(),
                Forms\Components\TextInput::make('valor_cuota')
                    ->label('Valor cuota')
                    ->numeric(),
                Forms\Components\TextInput::make('valor_vencido')
                    ->label('Valor vencido')
                    ->numeric(),
                Forms\Components\TextInput::make('saldo_capital')
                    ->label('Saldo capital')
                    ->numeric(),
                Forms\Components\TextInput::make('dias_mora')
                    ->label('Dias mora')
                    ->numeric(),
                Forms\Components\TextInput::make('modalidad')
                    ->label('Modalidad'),
                Forms\Components\TextInput::make('periodo')
                    ->label('Periodo'),
                Forms\Components\TextInput::make('origen_registro')
                    ->label('Origen registro'),
                Forms\Components\DatePicker::make('fecha_entrada_plano')
                    ->label('Fecha entrada plano'),
                Forms\Components\TextInput::make('estado_registro')
                    ->label('Estado tecnico'),
                Forms\Components\TextInput::make('observacion')
                    ->label('Observacion'),
                Forms\Components\DatePicker::make('fecha_vigencia')
                    ->label('Fecha vigencia'),
                Forms\Components\DatePicker::make('ultima_fecha_saldo_diario')
                    ->label('Ultimo saldo diario'),
                Forms\Components\TextInput::make('ultimo_estado_saldo_diario')
                    ->label('Ultimo estado saldo diario'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('obligacion')->label('Credito')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cc')->searchable()->sortable()->label('Documento'),
                Tables\Columns\TextColumn::make('nombres')->searchable(),
                Tables\Columns\TextColumn::make('apellidos')->searchable(),
                Tables\Columns\TextColumn::make('fecha_nacimiento')->date()->sortable()->label('F. nacimiento'),
                Tables\Columns\TextColumn::make('valor_reportar')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_cuota')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_vencido')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('saldo_capital')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('dias_mora')->numeric()->sortable()->label('Dias mora'),
                Tables\Columns\TextColumn::make('estado_seguimiento')
                    ->label('Estado cartera')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => static::getEstadoSeguimientoLabel($state))
                    ->color(fn(string $state): string => static::getEstadoSeguimientoColor($state)),
                Tables\Columns\TextColumn::make('modalidad')->sortable(),
                Tables\Columns\TextColumn::make('origen_registro')->badge()->sortable(),
                Tables\Columns\TextColumn::make('estado_registro')
                    ->label('Estado tecnico')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'saldo_cero' ? 'warning' : 'primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_entrada_plano')->date()->sortable()->label('Ingreso plano'),
                Tables\Columns\TextColumn::make('periodo')->sortable(),
                Tables\Columns\TextColumn::make('fecha_vigencia')->date()->sortable(),
                Tables\Columns\TextColumn::make('ultima_fecha_saldo_diario')->date()->sortable()->label('Ultimo saldo diario'),
                Tables\Columns\TextColumn::make('ultimo_estado_saldo_diario')->badge()->sortable()->label('Ultimo mov. diario'),
                Tables\Columns\TextColumn::make('observacion')->wrap(),
            ])
            ->filters([
                Tables\Filters\Filter::make('fecha_vigencia')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_vigencia', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_vigencia', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('estado_seguimiento')
                    ->label('Estado cartera')
                    ->options(static::getEstadoSeguimientoOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'saldo_vencido' => $query->conSaldoVencido(),
                            'al_dia' => $query->alDia(),
                            'saldo_cero' => $query->conSaldoCero(),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('origen_registro')
                    ->options([
                        'mensual' => 'Mensual',
                        'post_cierre' => 'Post cierre',
                        'saldos_diario' => 'Solo saldos diario',
                    ]),
                Tables\Filters\SelectFilter::make('estado_registro')
                    ->label('Estado tecnico')
                    ->options([
                        'activo' => 'Activo',
                        'saldo_cero' => 'Saldo capital 0',
                    ]),
                Tables\Filters\SelectFilter::make('ultimo_estado_saldo_diario')
                    ->label('Ultimo mov. diario')
                    ->options([
                        'nuevo' => 'Nuevo',
                        'disminuyo' => 'Disminuyo',
                        'aumento' => 'Aumento',
                        'mixto' => 'Mixto',
                        'sin_cambio' => 'Sin cambio',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('descargarTarjeta')
                    ->label('Descargar tarjeta')
                    ->icon('heroicon-o-photo')
                    ->color('success')
                    ->action(function (PlanoSaldoValor $record) {
                        try {
                            $png = app(PlanoSaldoValorCardImageService::class)->generate($record);
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('No fue posible generar la tarjeta')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();

                            return null;
                        }

                        $obligacion = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $record->obligacion);
                        $fileSuffix = $obligacion !== '' ? $obligacion : (string) $record->id;
                        $fileName = "tarjeta_digital_{$fileSuffix}.png";

                        return response()->streamDownload(
                            fn() => print($png),
                            $fileName,
                            [
                                'Content-Type' => 'image/png',
                                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                            ]
                        );
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importarPlano')
                    ->label('Importar Plano')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('archivo_cartera')
                            ->label('Archivo de cartera (.csv)')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->disk('public')
                            ->directory('planos/saldos')
                            ->helperText('De este archivo se toma el valor de la cuota.')
                            ->required(),
                        Forms\Components\FileUpload::make('archivo_saldos')
                            ->label('Archivo de saldos diarios Aicoll (.csv)')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->disk('public')
                            ->directory('planos/saldos')
                            ->helperText('Este archivo representa la foto diaria de saldos. Se usa para actualizar el consolidado y guardar el historial diario.')
                            ->required(),
                        Forms\Components\FileUpload::make('archivo_post_cierre')
                            ->label('Creditos posteriores al cierre (.txt/.csv)')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->disk('public')
                            ->directory('planos/saldos')
                            ->helperText('Opcional. Complementa la cartera mensual con creditos nuevos que entraron despues del cierre. El archivo debe venir separado por |.')
                            ->required(false),
                        Forms\Components\DatePicker::make('fecha_archivo')
                            ->label('Fecha Vigencia Plano')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $carteraPath = Storage::disk('public')->path($data['archivo_cartera']);
                        $saldosPath = Storage::disk('public')->path($data['archivo_saldos']);
                        $postCierrePath = null;

                        if (!empty($data['archivo_post_cierre'])) {
                            $postCierrePath = Storage::disk('public')->path($data['archivo_post_cierre']);
                        }

                        if (
                            !file_exists($carteraPath)
                            || !file_exists($saldosPath)
                            || ($postCierrePath !== null && !file_exists($postCierrePath))
                        ) {
                            Notification::make()
                                ->title('Error')
                                ->body('Uno o varios archivos no fueron encontrados.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $resultado = app(PlanoSaldoValorImportService::class)->import(
                                $carteraPath,
                                $saldosPath,
                                $data['fecha_archivo'],
                                $postCierrePath,
                            );
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('Error al procesar archivos')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        $procesados = (int) ($resultado['procesados'] ?? 0);
                        $creados = (int) ($resultado['creados'] ?? 0);
                        $actualizados = (int) ($resultado['actualizados'] ?? 0);
                        $ignoradosIguales = (int) ($resultado['ignorados_iguales'] ?? 0);
                        $sinCoincidencia = (int) ($resultado['sin_coincidencia_saldos'] ?? 0);
                        $zipPath = $resultado['zip_path'] ?? null;

                        Notification::make()
                            ->title('Proceso completado')
                            ->body("Procesados: {$procesados}. Nuevos: {$creados}. Actualizados: {$actualizados}. Ignorados iguales: {$ignoradosIguales}. Sin cruce con saldos: {$sinCoincidencia}.")
                            ->success()
                            ->send();

                        if (is_string($zipPath) && file_exists($zipPath)) {
                            $token = (string) Str::uuid();
                            $downloadName = basename($zipPath);

                            Cache::put(
                                PlanoSaldoValorExportDownloadController::makeCacheKey($token),
                                [
                                    'path' => $zipPath,
                                    'name' => $downloadName,
                                ],
                                now()->addMinutes(15),
                            );

                            return redirect(URL::temporarySignedRoute(
                                'admin.plano-saldo-valors.download',
                                now()->addMinutes(15),
                                ['token' => $token],
                            ));
                        }

                        Notification::make()
                            ->title('Proceso finalizado')
                            ->body("Se procesaron {$procesados} registros, pero no se generaron archivos de salida.")
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlanoSaldoValors::route('/'),
        ];
    }

    public static function getEstadoSeguimientoOptions(): array
    {
        return [
            'saldo_vencido' => 'Con saldo vencido',
            'al_dia' => 'Al dia',
            'saldo_cero' => 'Saldo capital 0',
        ];
    }

    public static function getEstadoSeguimientoLabel(string $state): string
    {
        return static::getEstadoSeguimientoOptions()[$state] ?? 'Sin clasificar';
    }

    public static function getEstadoSeguimientoColor(string $state): string
    {
        return match ($state) {
            'saldo_vencido' => 'danger',
            'al_dia' => 'success',
            'saldo_cero' => 'warning',
            default => 'gray',
        };
    }
}

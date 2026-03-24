<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanoSaldoValorResource\Pages;
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
use Illuminate\Support\Facades\Storage;

class PlanoSaldoValorResource extends Resource
{
    protected static ?string $model = PlanoSaldoValor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Plano Saldos y Valores';
    protected static ?string $modelLabel = 'Saldos y Valores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('obligacion')->required(),
                Forms\Components\TextInput::make('cc')->required(),
                Forms\Components\TextInput::make('nombres'),
                Forms\Components\TextInput::make('apellidos'),
                Forms\Components\TextInput::make('valor_reportar')->numeric(),
                Forms\Components\TextInput::make('valor_cuota')->numeric(),
                Forms\Components\TextInput::make('modalidad'),
                Forms\Components\TextInput::make('periodo'),
                Forms\Components\TextInput::make('observacion'),
                Forms\Components\TextInput::make('saldo_capital')->numeric(),
                Forms\Components\TextInput::make('dias_mora')->numeric(),
                Forms\Components\DatePicker::make('fecha_vigencia'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('obligacion')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cc')->searchable()->sortable()->label('Documento'),
                Tables\Columns\TextColumn::make('nombres')->searchable(),
                Tables\Columns\TextColumn::make('apellidos')->searchable(),
                Tables\Columns\TextColumn::make('valor_reportar')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_cuota')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('modalidad')->sortable(),
                Tables\Columns\TextColumn::make('periodo')->sortable(),
                Tables\Columns\TextColumn::make('fecha_vigencia')->date()->sortable(),
                Tables\Columns\TextColumn::make('observacion')->limit(30),
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
                    })
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
                            ->label('Archivo de saldos Aicoll (.csv)')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->disk('public')
                            ->directory('planos/saldos')
                            ->helperText('De este archivo se toman el valor vencido y el saldo capital.')
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_archivo')
                            ->label('Fecha Vigencia Plano')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $carteraPath = Storage::disk('public')->path($data['archivo_cartera']);
                        $saldosPath = Storage::disk('public')->path($data['archivo_saldos']);

                        if (!file_exists($carteraPath) || !file_exists($saldosPath)) {
                            Notification::make()
                                ->title('Error')
                                ->body('Uno o ambos archivos no fueron encontrados.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $resultado = app(PlanoSaldoValorImportService::class)->import(
                                $carteraPath,
                                $saldosPath,
                                $data['fecha_archivo'],
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
                            return response()->download($zipPath)->deleteFileAfterSend(true);
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
}

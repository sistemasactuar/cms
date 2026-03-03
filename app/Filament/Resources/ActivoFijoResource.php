<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivoFijoResource\Pages;
use App\Filament\Resources\ActivoFijoResource\RelationManagers\MaintenimientosRelationManager;
use App\Models\ActivoFijo;
use App\Models\MacroTipoActivo;
use App\Models\Sede;
use App\Models\TipoActivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivoFijoResource extends Resource
{
    protected static ?string $model = ActivoFijo::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Gestion';
    protected static ?string $navigationLabel = 'Activos Fijos';
    protected static ?string $modelLabel = 'Activo Fijo';
    protected static ?string $pluralModelLabel = 'Activos Fijos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Generales')
                    ->schema([
                        Forms\Components\Select::make('macro_tipo_id')
                            ->label('Macrotipo')
                            ->options(fn() => MacroTipoActivo::query()->where('activo', 1)->orderBy('nombre')->pluck('nombre', 'id')->all())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateHydrated(function ($state, Set $set, Get $get): void {
                                if (filled($state)) {
                                    return;
                                }

                                $tipoId = $get('tipo');
                                if (blank($tipoId)) {
                                    return;
                                }

                                $macroId = TipoActivo::query()->whereKey($tipoId)->value('macro_tipo_id');
                                if ($macroId !== null) {
                                    $set('macro_tipo_id', $macroId);
                                }
                            })
                            ->afterStateUpdated(function (Set $set): void {
                                $set('tipo', null);
                            }),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options(function (Get $get): array {
                                $macroTipoId = $get('macro_tipo_id');

                                return TipoActivo::query()
                                    ->where('activo', 1)
                                    ->when(
                                        filled($macroTipoId),
                                        fn($query) => $query->where('macro_tipo_id', $macroTipoId)
                                    )
                                    ->orderBy('tipo')
                                    ->pluck('tipo', 'id')
                                    ->all();
                            })
                            ->required()
                            ->disabled(fn(Get $get): bool => blank($get('macro_tipo_id')) && blank($get('tipo')))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (blank($state)) {
                                    return;
                                }

                                $macroId = TipoActivo::query()->whereKey($state)->value('macro_tipo_id');
                                if ($macroId !== null) {
                                    $set('macro_tipo_id', $macroId);
                                }
                            }),
                        Forms\Components\TextInput::make('codigo')
                            ->label('Codigo Inventario')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripcion')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('marca')
                            ->label('Marca')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('modelo')
                            ->label('Modelo')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('serie')
                            ->label('Nro de Serie')
                            ->maxLength(120),
                        Forms\Components\Select::make('para_sede_id')
                            ->label('Oficina')
                            ->options(fn() => Sede::query()->where('activo', 1)->orderBy('NombreSede')->pluck('NombreSede', 'id')->all())
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('responsable')
                            ->label('Responsable')
                            ->maxLength(180),
                        Forms\Components\TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('condicion')
                            ->label('Condicion')
                            ->options([
                                'Usado' => 'Usado',
                                'Nuevo' => 'Nuevo',
                                'Remanufacturado' => 'Remanufacturado',
                            ]),
                        Forms\Components\Textarea::make('observacion')
                            ->label('Observacion')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('activo')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Ficha Tecnica')
                    ->description('Los campos visibles cambian segun el tipo de activo.')
                    ->schema([
                        Forms\Components\Select::make('unidad_cd')
                            ->label('Unidad CD')
                            ->options(self::opcionesSiNo())
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('hdd1')
                            ->label('Disco Duro 1')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\Select::make('tipo_disco')
                            ->label('Tipo Disco 1')
                            ->options(self::opcionesTipoDisco())
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('hdd2')
                            ->label('Disco Duro 2')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\Select::make('tipo_disco2')
                            ->label('Tipo Disco 2')
                            ->options(self::opcionesTipoDisco())
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('fuente')
                            ->label('Fuente')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('cargador')
                            ->label('Cargador')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [2, 3], true)),
                        Forms\Components\TextInput::make('procesador')
                            ->label('Procesador')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('ram')
                            ->label('RAM')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('pantalla')
                            ->label('Pantalla')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 4], true)),
                        Forms\Components\TextInput::make('pantalla_tam')
                            ->label('Tamano Pantalla')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('t_video')
                            ->label('Tarjeta Video')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('teclado')
                            ->label('Teclado')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('mouse')
                            ->label('Mouse')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('so')
                            ->label('Sistema Operativo')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('sof')
                            ->label('Sistema Ofimatico')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('compresor')
                            ->label('Compresor')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('adobe')
                            ->label('Adobe Reader')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('antivirus')
                            ->label('Antivirus')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('explorador1')
                            ->label('Explorador 1')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('explorador2')
                            ->label('Explorador 2')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('explorador3')
                            ->label('Explorador 3')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\Textarea::make('prog_adicionales')
                            ->label('Programas Adicionales')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [1, 2, 3, 4], true)),
                        Forms\Components\TextInput::make('ups_capacidad')
                            ->label('UPS Capacidad')
                            ->visible(fn(Get $get): bool => (int) $get('tipo') === 5),
                        Forms\Components\TextInput::make('telecom_puertos')
                            ->label('Cantidad Puertos')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [6, 7], true)),
                        Forms\Components\TextInput::make('telecom_pe')
                            ->label('POE')
                            ->visible(fn(Get $get): bool => in_array((int) $get('tipo'), [6, 7], true)),
                        Forms\Components\Select::make('vigil_tipo')
                            ->label('Tipo Vigilancia')
                            ->options(self::opcionesTipoVigilancia())
                            ->visible(fn(Get $get): bool => (int) $get('tipo') === 8),
                        Forms\Components\TextInput::make('vigil_puertos')
                            ->label('Cantidad Canales')
                            ->visible(fn(Get $get): bool => (int) $get('tipo') === 8),
                        Forms\Components\TextInput::make('vigil_capacidad')
                            ->label('Capacidad')
                            ->visible(fn(Get $get): bool => (int) $get('tipo') === 8),
                        Forms\Components\Select::make('vigil_poe')
                            ->label('POE Vigilancia')
                            ->options(self::opcionesSiNo())
                            ->visible(fn(Get $get): bool => (int) $get('tipo') === 8),
                        Forms\Components\TextInput::make('acces_point_rango')
                            ->label('Rango Access Point')
                            ->visible(fn(Get $get): bool => (int) $get('tipo') === 9),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripcion')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('tipoActivo.macroTipo.nombre')
                    ->label('Macrotipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipoActivo.tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sede.NombreSede')
                    ->label('Oficina')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsable')
                    ->label('Responsable')
                    ->searchable(),
                Tables\Columns\TextColumn::make('valor')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mantenimientos_max_fecadi')
                    ->label('Ultimo Mantenimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\Action::make('estado')
                    ->label(fn(ActivoFijo $record): string => $record->activo ? 'Dar de Baja' : 'Activar')
                    ->icon(fn(ActivoFijo $record): string => $record->activo ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(ActivoFijo $record): string => $record->activo ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (ActivoFijo $record): void {
                        $record->update(['activo' => !$record->activo]);

                        Notification::make()
                            ->title($record->activo ? 'Activo habilitado' : 'Activo dado de baja')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('mantenimiento')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->url(fn(ActivoFijo $record): string => ActivoMantenimientoResource::getUrl('create', ['equipo_id' => $record->id])),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (ActivoFijo $record) {
                        $record->load(['tipoActivo', 'sede', 'mantenimientos.creador']);

                        $tiposMantenimiento = ActivoMantenimientoResource::tipoMantenimientoOptions();

                        $pdf = Pdf::loadView('pdf.activo-ficha', [
                            'activo' => $record,
                            'tiposMantenimiento' => $tiposMantenimiento,
                        ]);

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'ficha_activo_' . $record->id . '.pdf'
                        );
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MaintenimientosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivoFijos::route('/'),
            'create' => Pages\CreateActivoFijo::route('/create'),
            'edit' => Pages\EditActivoFijo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['tipoActivo.macroTipo', 'sede'])
            ->withMax('mantenimientos', 'fecadi');
    }

    public static function opcionesSiNo(): array
    {
        return [
            0 => 'SI',
            1 => 'NO',
        ];
    }

    public static function opcionesTipoDisco(): array
    {
        return [
            0 => 'HDD',
            1 => 'SSD',
            2 => 'M2',
            3 => 'N/A',
        ];
    }

    public static function opcionesTipoVigilancia(): array
    {
        return [
            0 => 'DVR',
            1 => 'NVR',
        ];
    }
}

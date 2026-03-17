<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VotacionResource\Pages;
use App\Filament\Resources\VotacionResource\RelationManagers\CandidatosRelationManager;
use App\Filament\Resources\VotacionResource\RelationManagers\PlanillasRelationManager;
use App\Filament\Resources\VotacionResource\RelationManagers\VotosRelationManager;
use App\Models\Votacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class VotacionResource extends Resource
{
    protected static ?string $model = Votacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Votaciones';
    protected static ?string $navigationLabel = 'Configuracion';
    protected static ?string $modelLabel = 'Votacion';
    protected static ?string $pluralModelLabel = 'Votaciones';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuracion general')
                    ->schema([
                        Forms\Components\TextInput::make('titulo')
                            ->label('Titulo')
                            ->required()
                            ->maxLength(180)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, ?Votacion $record): void {
                                if ($record) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(180)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('tipo_votacion')
                            ->label('Tipo de votacion')
                            ->options([
                                'nominal' => 'Nominal',
                                'planilla' => 'Por planilla',
                            ])
                            ->required()
                            ->default('nominal')
                            ->native(false)
                            ->live(),
                        Forms\Components\Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'borrador' => 'Borrador',
                                'publicada' => 'Publicada',
                                'cerrada' => 'Cerrada',
                            ])
                            ->required()
                            ->default('borrador')
                            ->native(false),
                        Forms\Components\TextInput::make('cupos')
                            ->label('Cupos a elegir')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('En nominal es la cantidad de cargos o cupos. En planilla se reparten por porcentaje.'),
                        Forms\Components\TextInput::make('max_selecciones')
                            ->label('Maximo de candidatos por voto')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->visible(fn (Get $get): bool => $get('tipo_votacion') === 'nominal')
                            ->helperText('Para votacion nominal. Si elige 3, cada aportante podra marcar hasta 3 candidatos.'),
                        Forms\Components\DateTimePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->seconds(false),
                        Forms\Components\DateTimePicker::make('fecha_fin')
                            ->label('Fecha de cierre')
                            ->seconds(false),
                        Forms\Components\Toggle::make('aceptacion_obligatoria')
                            ->label('Exigir aceptacion de orden del dia')
                            ->default(true),
                        Forms\Components\Toggle::make('activo')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Portal publico')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('votaciones/logos')
                            ->disk('public'),
                        Forms\Components\Textarea::make('descripcion_publica')
                            ->label('Mensaje introductorio')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('orden_del_dia')
                            ->label('Orden del dia')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('portal_url')
                            ->label('URL del portal')
                            ->content(fn (): string => url('/votaciones')),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Carga de opciones de voto')
                    ->schema([
                        Forms\Components\Placeholder::make('guia_carga_opciones')
                            ->label('Donde se crean los candidatos o planillas')
                            ->content(function (?Votacion $record, Get $get): string {
                                $tipo = $record?->tipo_votacion ?? $get('tipo_votacion') ?? 'nominal';

                                if (!$record) {
                                    return $tipo === 'planilla'
                                        ? 'Primero guarda la votacion. Despues entraras a editarla y al final veras la seccion "1. Crear planillas" y luego "2. Asignar candidatos".'
                                        : 'Primero guarda la votacion. Despues entraras a editarla y al final veras la seccion "Candidatos para voto nominal" para cargar las personas que recibiran votos.';
                                }

                                return $tipo === 'planilla'
                                    ? 'En esta pantalla, mas abajo, veras "1. Crear planillas" para crear cada planilla y luego "2. Asignar candidatos a planilla" para vincular las personas a la planilla correspondiente.'
                                    : 'En esta pantalla, mas abajo, veras "Candidatos para voto nominal". Alli creas las personas que recibiran votos directamente.';
                            }),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Votacion')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('tipo_votacion')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => $state === 'planilla' ? 'Planilla' : 'Nominal')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'planilla' ? 'warning' : 'primary'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'publicada' => 'success',
                        'cerrada' => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('cupos')
                    ->label('Cupos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidatos_count')
                    ->label('Candidatos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('planillas_count')
                    ->label('Planillas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('votos_emitidos_count')
                    ->label('Votos emitidos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin definir'),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin definir'),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_votacion')
                    ->label('Tipo')
                    ->options([
                        'nominal' => 'Nominal',
                        'planilla' => 'Planilla',
                    ]),
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'publicada' => 'Publicada',
                        'cerrada' => 'Cerrada',
                    ]),
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo'),
            ])
            ->actions([
                Tables\Actions\Action::make('portal')
                    ->label('Portal')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (): string => url('/votaciones'))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            PlanillasRelationManager::class,
            CandidatosRelationManager::class,
            VotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVotacions::route('/'),
            'create' => Pages\CreateVotacion::route('/create'),
            'edit' => Pages\EditVotacion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'candidatos',
                'planillas',
                'votos as votos_emitidos_count' => fn (Builder $query) => $query->whereNotNull('voto_emitido_at'),
            ]);
    }
}

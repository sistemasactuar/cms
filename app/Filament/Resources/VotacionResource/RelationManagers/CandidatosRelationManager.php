<?php

namespace App\Filament\Resources\VotacionResource\RelationManagers;

use App\Models\Aportante;
use App\Models\Votacion;
use App\Models\VotacionCandidato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CandidatosRelationManager extends RelationManager
{
    protected static string $relationship = 'candidatos';

    protected static ?string $title = '2. Asignar integrantes a planilla';
    protected static ?string $modelLabel = 'Integrante';
    protected static ?string $pluralModelLabel = 'Integrantes';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Votacion;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->tipo_votacion === 'planilla'
            ? '2. Asignar integrantes a la Plancha'
            : 'Candidatos para voto nominal';
    }

    public function form(Form $form): Form
    {
        $ownerRecord = $this->getOwnerRecord();

        return $form->schema([
            Forms\Components\Select::make('aportante_id')
                ->label('Aportante')
                ->relationship(
                    name: 'aportante',
                    titleAttribute: 'nombre',
                    modifyQueryUsing: fn ($query) => $query
                        ->where('activo', true)
                        ->orderBy('nombre')
                )
                ->getOptionLabelFromRecordUsing(fn (Aportante $aportante): string => "{$aportante->nombre} - {$aportante->documento}")
                ->searchable(['nombre', 'documento', 'correo'])
                ->preload()
                ->required()
                ->helperText('Selecciona el participante que sera integrante dentro de esta votacion.')
                ->live()
                ->afterStateHydrated(function ($state, Set $set, Get $get): void {
                    if (blank($state)) {
                        return;
                    }

                    $aportante = Aportante::query()->find($state);

                    if (!$aportante) {
                        return;
                    }

                    if (blank($get('nombre'))) {
                        $set('nombre', $aportante->nombre);
                    }

                    if (blank($get('documento'))) {
                        $set('documento', $aportante->documento);
                    }
                })
                ->afterStateUpdated(function ($state, Set $set): void {
                    $aportante = Aportante::query()->find($state);

                    if (!$aportante) {
                        $set('nombre', null);
                        $set('documento', null);

                        return;
                    }

                    $set('nombre', $aportante->nombre);
                    $set('documento', $aportante->documento);
                }),
            Forms\Components\TextInput::make('numero')
                ->label('Numero')
                ->numeric()
                ->minValue(1),
            Forms\Components\TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->readOnly()
                ->maxLength(180),
            Forms\Components\TextInput::make('documento')
                ->label('Documento')
                ->readOnly()
                ->maxLength(40),
            Forms\Components\TextInput::make('cargo')
                ->label('Cargo')
                ->maxLength(120),
            Forms\Components\Select::make('planilla_id')
                ->label('Planilla')
                ->options(fn (): array => $ownerRecord->planillas()->where('activo', true)->orderBy('numero')->orderBy('nombre')->pluck('nombre', 'id')->all())
                ->searchable()
                ->preload()
                ->required(fn (): bool => $ownerRecord->tipo_votacion === 'planilla')
                ->helperText(fn (): ?string => $ownerRecord->tipo_votacion === 'planilla'
                    ? 'Selecciona la plancha a la que pertenece este integrante.'
                    : null)
                ->visible(fn (): bool => $ownerRecord->tipo_votacion === 'planilla'),
            Forms\Components\FileUpload::make('foto_path')
                ->label('Foto')
                ->image()
                ->directory('votaciones/candidatos')
                ->disk('public'),
            Forms\Components\Textarea::make('descripcion')
                ->label('Perfil o presentacion')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Toggle::make('activo')
                ->label('Activo')
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['planilla', 'aportante'])->withCount('detallesVoto'))
            ->columns([
                Tables\Columns\ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('aportante.documento')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nro')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('planilla.nombre')
                    ->label('Plancha')
                    ->placeholder('Sin plancha'),
                Tables\Columns\TextColumn::make('detalles_voto_count')
                    ->label('Votos'),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('createCandidato')
                    ->label('Agregar integrante')
                    ->modalHeading('Crear integrante'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}

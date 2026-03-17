<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VotacionPlanillaResource\Pages;
use App\Filament\Resources\VotacionPlanillaResource\RelationManagers;
use App\Models\VotacionPlanilla;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VotacionPlanillaResource extends Resource
{
    protected static ?string $model = VotacionPlanilla::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Votaciones';
    protected static ?string $navigationLabel = 'Gestión de Planchas';
    protected static ?string $modelLabel = 'Plancha';
    protected static ?string $pluralModelLabel = 'Planchas';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return true; 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('votacion_id')
                    ->label('Votacion / Vigencia')
                    ->relationship('votacion', 'titulo', fn ($query) => $query->where('tipo_votacion', 'planilla'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $nextNum = VotacionPlanilla::where('votacion_id', $state)->max('numero') + 1;
                            $set('numero', $nextNum ?: 1);
                        }
                    }),
                Forms\Components\TextInput::make('numero')
                    ->label('Numero')
                    ->numeric()
                    ->default(function (Forms\Get $get) {
                        $votacionId = $get('votacion_id');
                        if ($votacionId) {
                            return VotacionPlanilla::where('votacion_id', $votacionId)->max('numero') + 1;
                        }
                        return null;
                    }),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la Plancha')
                    ->required()
                    ->maxLength(180),
                Forms\Components\ColorPicker::make('color')
                    ->label('Color identificador'),
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logo')
                    ->image()
                    ->directory('votaciones/planillas')
                    ->disk('public'),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripcion')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('votacion.titulo')
                    ->label('Votacion')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nro')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Plancha')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('votacion_id')
                    ->label('Votacion')
                    ->relationship('votacion', 'titulo', fn ($query) => $query->where('tipo_votacion', 'planilla')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVotacionPlanillas::route('/'),
        ];
    }
}

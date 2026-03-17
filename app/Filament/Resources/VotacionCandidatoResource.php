<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VotacionCandidatoResource\Pages;
use App\Filament\Resources\VotacionCandidatoResource\RelationManagers;
use App\Models\VotacionCandidato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VotacionCandidatoResource extends Resource
{
    protected static ?string $model = VotacionCandidato::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Votaciones';
    protected static ?string $navigationLabel = 'Gestión de Integrantes';
    protected static ?string $modelLabel = 'Integrante';
    protected static ?string $pluralModelLabel = 'Integrantes';
    protected static ?int $navigationSort = 4;

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
                    ->relationship('votacion', 'titulo')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('planilla_id')
                    ->label('Plancha (Si aplica)')
                    ->relationship('planilla', 'nombre', fn ($query, Forms\Get $get) => 
                        $query->where('votacion_id', $get('votacion_id'))
                    )
                    ->searchable()
                    ->placeholder('Dejar vacío para Voto Nominal')
                    ->preload(),
                Forms\Components\Select::make('aportante_id')
                    ->label('Participante')
                    ->relationship('aportante', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre Completo')
                    ->required()
                    ->maxLength(180),
                Forms\Components\TextInput::make('documento')
                    ->label('Documento')
                    ->maxLength(40),
                Forms\Components\TextInput::make('cargo')
                    ->label('Cargo/Rol')
                    ->maxLength(120),
                Forms\Components\FileUpload::make('foto_path')
                    ->label('Foto')
                    ->image()
                    ->directory('votaciones/candidatos')
                    ->disk('public'),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Reseña/Perfil')
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
                    ->label('Vigencia')
                    ->sortable(),
                Tables\Columns\TextColumn::make('planilla.nombre')
                    ->label('Plancha')
                    ->placeholder('Voto Nominal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('documento')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('votacion_id')
                    ->label('Votacion')
                    ->relationship('votacion', 'titulo'),
                Tables\Filters\SelectFilter::make('planilla_id')
                    ->label('Plancha')
                    ->relationship('planilla', 'nombre'),
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
            'index' => Pages\ManageVotacionCandidatos::route('/'),
        ];
    }
}

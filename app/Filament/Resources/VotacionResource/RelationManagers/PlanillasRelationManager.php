<?php

namespace App\Filament\Resources\VotacionResource\RelationManagers;

use App\Models\Votacion;
use App\Models\VotacionPlanilla;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlanillasRelationManager extends RelationManager
{
    protected static string $relationship = 'planillas';

    protected static ?string $title = '1. Crear planchas';
    protected static ?string $modelLabel = 'Plancha';
    protected static ?string $pluralModelLabel = 'Planchas';
    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Votacion && $ownerRecord->tipo_votacion === 'planilla';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('numero')
                ->label('Numero')
                ->numeric()
                ->minValue(1),
            Forms\Components\TextInput::make('nombre')
                ->label('Nombre')
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
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'candidatos',
                'votos as votos_emitidos_count' => fn ($subQuery) => $subQuery->whereNotNull('voto_emitido_at'),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nro')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Plancha')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidatos_count')
                    ->label('Integrantes'),
                Tables\Columns\TextColumn::make('votos_emitidos_count')
                    ->label('Votos'),
                Tables\Columns\TextColumn::make('porcentaje')
                    ->label('%')
                    ->state(function (VotacionPlanilla $record): string {
                        $total = (int) $this->getOwnerRecord()
                            ->votos()
                            ->whereNotNull('voto_emitido_at')
                            ->count();

                        if ($total === 0) {
                            return '0 %';
                        }

                        return number_format(($record->votos_emitidos_count / $total) * 100, 2, ',', '.') . ' %';
                    }),
                Tables\Columns\TextColumn::make('cupos_asignados')
                    ->label('Cargos obtenidos')
                    ->state(function (VotacionPlanilla $record): int {
                        $resultado = $this->getOwnerRecord()->calcularDistribucionPlanchas();

                        return (int) ($resultado[$record->id]['cupos'] ?? 0);
                    }),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('createPlanilla')
                    ->label('Agregar plancha')
                    ->modalHeading('Crear plancha'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}

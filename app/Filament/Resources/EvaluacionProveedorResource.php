<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluacionProveedorResource\Pages;
use App\Models\EvaluacionProveedor;
use App\Models\Proveedores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EvaluacionProveedorResource extends Resource
{
    protected static ?string $model = EvaluacionProveedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Gestión de Proveedores';
    protected static ?string $modelLabel = 'Evaluación de Proveedor';
    protected static ?string $pluralModelLabel = 'Evaluaciones de Proveedores';

    public static function form(Form $form): Form
    {
        $preguntas = [
            1 => '¿Tiene precios competitivos para su servicio?',
            2 => '¿Sus tiempos de respuesta se adecuan a nuestras necesidades?',
            3 => '¿Suministra información técnica apropiada?',
            4 => '¿Brinda la asesoría requerida?',
            5 => '¿Conoce bien su servicio?',
            6 => '¿Asiste a reuniones solicitadas específicamente?',
            7 => '¿Plantea innovaciones y mejoras en su servicio?',
            8 => '¿Es oportuno en la solución de quejas o reclamos?',
            9 => '¿Ofrece garantía de los productos y/o servicios?',
            10 => '¿Es amable en la atención del servicio?',
            11 => '¿La calidad del servicio cumple con lo requerido?',
        ];

        return $form->schema([
            Forms\Components\Select::make('proveedor_id')
                ->label('Proveedor')
                ->options(Proveedores::pluck('nombre', 'id'))
                ->disabled(fn($record) => $record?->bloqueado)
                ->required(),

            Forms\Components\DatePicker::make('fecha')
                ->label('Fecha de evaluación')
                ->default(now())
                ->disabled(fn($record) => $record?->bloqueado),

            Forms\Components\Section::make('Ítems a evaluar')
                ->schema(array_map(
                    fn($i, $texto) =>
                    Forms\Components\Select::make("pregunta_$i")
                        ->label("$i. $texto")
                        ->options([
                            'na' => 'N/A',
                            0 => 'No cumple',
                            1 => 'Cumple parcialmente',
                            2 => 'Cumple',
                        ])
                        ->native(false)
                        ->disabled(fn($record) => $record?->bloqueado)
                        ->reactive(),
                    array_keys($preguntas),
                    $preguntas
                )),

            Forms\Components\Textarea::make('observaciones')
                ->label('Observaciones')
                ->rows(3)
                ->disabled(fn($record) => $record?->bloqueado),

            Forms\Components\ViewField::make('firma')
                ->label('Firma digital')
                ->view('filament.forms.components.signature-field')
                ->visible(fn($record) => ! $record?->bloqueado),

            Forms\Components\Hidden::make('responsable_id')
                ->default(fn() => auth()->user()?->responsable_id)
                ->dehydrated(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proveedor.nombre')->label('Proveedor')->searchable(),
                Tables\Columns\TextColumn::make('responsable.nombre')->label('Responsable')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha')->date(),
                Tables\Columns\TextColumn::make('calificacion')->label('Calif.')->suffix('%'),
                Tables\Columns\BadgeColumn::make('clasificacion')
                    ->label('Clasificación')
                    ->colors([
                        'success' => 'EXCELENTE',
                        'warning' => 'BUENO',
                        'info' => 'ACEPTABLE',
                        'danger' => 'NO ACEPTABLE',
                    ]),
                Tables\Columns\IconColumn::make('bloqueado')
                    ->label('Firmado')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('bloqueado')
                    ->label('Estado de firma')
                    ->placeholder('Todos')
                    ->trueLabel('Firmados')
                    ->falseLabel('Pendientes'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => EvaluacionProveedorResource::getUrl('view', ['record' => $record]))
                    ->color('gray'),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        // Admin o superadmin ve todo
        if ($user->hasRole('superadministrador') || $user->hasRole('admin')) {
            return parent::getEloquentQuery();
        }

        // Responsables ven solo sus evaluaciones
        return parent::getEloquentQuery()->where('user_id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvaluacionProveedors::route('/'),
            'create' => Pages\CreateEvaluacionProveedor::route('/create'),
            'edit'   => Pages\EditEvaluacionProveedor::route('/{record}/edit'),
            'view'   => Pages\ViewEvaluacionProveedor::route('/{record}'),
        ];
    }
}

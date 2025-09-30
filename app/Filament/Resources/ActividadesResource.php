<?php

namespace App\Filament\Resources;

use App\Enums\EstadoActividad;
use App\Filament\Resources\ActividadesResource\Pages;
use App\Models\Actividades;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;

class ActividadesResource extends Resource
{
    protected static ?string $model = Actividades::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Gestión';
    protected static ?string $modelLabel = 'Actividad';
    protected static ?string $pluralModelLabel = 'Actividades';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make()->columns(2)->schema([
                Forms\Components\DateTimePicker::make('fecha_programada')
                    ->label('Fecha y hora')
                    ->seconds(false)
                    ->native(false)
                    ->required(),

                Forms\Components\TextInput::make('titulo')
                    ->label('Título')
                    ->required(),

                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options(EstadoActividad::labels())
                    ->default(EstadoActividad::EnCurso->value),


                Forms\Components\Select::make('descripcion')
                    ->label('Programación')
                    ->options([
                        'programada'     => 'Visita programada',
                        'no_programada'  => 'No programada',
                        'otro'           => 'Otro',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('tipo_aviso')
                    ->label('Tipo (si es "Otro")')
                    ->options([
                        'notas'      => 'Notas',
                        'cartas'     => 'Cartas',
                        'pre_avisos' => 'Pre avisos',
                    ])
                    ->visible(fn (Get $get) => $get('programacion') == 'otro'),

                // Asigna al usuario logueado
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->dehydrated(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario'),
                // Ajusta según si usas DatePicker (date) o DateTimePicker (dateTime)
                Tables\Columns\TextColumn::make('fecha_programada')->label('Fecha')->date(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(function ($record) {
                        $state = $record->estado;
                        return $state instanceof \App\Enums\EstadoActividad ? $state->value : (string) $state;
                    })
                    ->colors([
                        'warning' => \App\Enums\EstadoActividad::EnCurso->value,
                        'success' => \App\Enums\EstadoActividad::Ejecutada->value,
                        'gray'    => \App\Enums\EstadoActividad::Finalizada->value,
                    ])
                    ->formatStateUsing(fn (string $state) => \App\Enums\EstadoActividad::labels()[$state] ?? $state)
                    ->sortable(),
            ])
            ->defaultSort('fecha_programada', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListActividades::route('/'),
            'create' => Pages\CreateActividades::route('/create'),
            'edit'   => Pages\EditActividades::route('/{record}/edit'),
        ];
    }
}

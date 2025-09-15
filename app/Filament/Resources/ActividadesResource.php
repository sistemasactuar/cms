<?php

namespace App\Filament\Resources;

use App\Enums\EstadoActividad;
use App\Filament\Resources\ActividadesResource\Pages;
use App\Models\Actividades;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

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
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('titulo')
                    ->label('Título')
                    ->required(),

                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options(EstadoActividad::labels())
                    ->default(EstadoActividad::EnCurso->value),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id())
                    ->dehydrated(),
            ]),
            Forms\Components\TextInput::make('full_address')
                ->label('Dirección completa')
                ->columnSpanFull()
                ->required(),

            Forms\Components\TextInput::make('latitud')
                ->label('Latitud')
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $set('location', [
                        'lat' => floatVal($state),
                        'lng' => floatVal($get('longitud')),
                    ]);
                })
                ->reactive()
                ->lazy(), // important to use lazy, to avoid updates as you type
            Forms\Components\TextInput::make('longitud')
                ->label('Longitud')
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    $set('location', [
                        'lat' => floatval($get('latitud')),
                        'lng' => floatVal($state),
                    ]);
                })
                ->reactive()
                ->lazy(),
            Forms\Components\Section::make('Ubicación')->schema([
                Map::make('location') // ← usa la propiedad computada del modelo
                    ->label('Mapa')
                    ->mapControls([
                        'mapTypeControl'    => true,
                        'scaleControl'      => true,
                        'streetViewControl' => true,
                        'rotateControl'     => true,
                        'fullscreenControl' => true,
                        'searchBoxControl'  => true, // creates geocomplete field inside map
                        'zoomControl'       => true,
                    ])
                    ->defaultLocation([4.536154, -75.668694]) // Armenia (ajusta)
                    ->defaultZoom(16)
                    ->height('400px')
                    ->draggable()
                    ->clickable()
                    ->geolocate()      // botón "usar mi ubicación"
                    ->autocomplete('full_address') // opcional si agregas un TextInput con ese name
                    ->autocompleteReverse(true)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('latitud', $state['lat']);
                        $set('longitud', $state['lng']);
                    }),   // opcional: escribe la dirección al mover el pin
            ])->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario'),
                Tables\Columns\TextColumn::make('fecha_programada')->label('Fecha')->dateTime(),
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
                    ->formatStateUsing(fn(string $state) => \App\Enums\EstadoActividad::labels()[$state] ?? $state)
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

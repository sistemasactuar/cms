<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Actividades;
use Illuminate\Http\Response;
use App\Enums\EstadoActividad;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ActividadesResource\Pages;

class ActividadesResource extends Resource
{
    protected static ?string $model = Actividades::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Gestión';
    protected static ?string $modelLabel = 'Programador de Actividades';
    protected static ?string $pluralModelLabel = 'Programador';

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
                    ->label('Nombre del Cliente')
                    ->required(),

                    Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->options(EstadoActividad::labels())
                    ->default(EstadoActividad::EnCurso->value)
                    ->required()
                    ->reactive(),


                Forms\Components\Select::make('descripcion')
                    ->label('Programación')
                    ->options([
                        'visita_credito'     => 'Visita Crédito',
                        'visita_cobro'  => 'Visita Cobro',
                        'recoger_documentos'      => 'Recoger Documentos',
                        'postcredito'     => 'Postcredito',
                        'otro'           => 'Otro',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\Textarea::make('detalle_programacion')
                    ->label('Detalle (si eliges "Otro")')
                    ->rows(3)
                    ->visible(fn (Get $get) => $get('descripcion') === 'otro'),

                Forms\Components\Textarea::make('resultado_visita')
                    ->label('Resultado de la visita')
                    ->rows(4)
                    ->columnSpanFull() // ocupa todo el ancho
                    ->visible(fn (Get $get) =>
                        in_array($get('estado'), [
                            EstadoActividad::Ejecutada->value,
                            EstadoActividad::Finalizada->value,
                        ])
                    ),
                // Asigna al usuario logueado
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->dehydrated(),

                Forms\Components\TextInput::make('direccion')
                    ->label('Dirección'),

                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono'),

            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Nombre del Cliente')->searchable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario'),
                // Ajusta según si usas DatePicker (date) o DateTimePicker (dateTime)
                Tables\Columns\TextColumn::make('fecha_programada')->label('Fecha y hora')->dateTime(),

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
                Tables\Columns\TextColumn::make('descripcion')->label('Programación')->searchable(),
                Tables\Columns\TextColumn::make('resultado_visita')->label('Resultado de la visita')->limit(50)->wrap(),
                Tables\Columns\TextColumn::make('direccion')->label('Dirección')->searchable(),
                Tables\Columns\TextColumn::make('telefono')->label('Teléfono')->searchable(),
            ])
            ->defaultSort('fecha_programada', 'desc')
                        ->actions([
                            Tables\Actions\EditAction::make(),
                            Tables\Actions\Action::make('descargar_calendario')
                                ->label('Agregar compromiso (.ics)')
                                ->icon('heroicon-o-calendar-days')
                                ->color('success')
                                ->action(function ($record) {
                                    // Construye el contenido del evento en formato iCalendar (.ics)
                                    $titulo       = addslashes($record->titulo);
                                    $descripcion  = addslashes($record->detalle_programacion ?? $record->descripcion ?? '');
                                    $fechaInicio  = $record->fecha_programada->format('Ymd\THis');
                                    $fechaFin     = $record->fecha_programada->addHour()->format('Ymd\THis'); // +1 hora por defecto
                                    $uid          = uniqid();
                                    $contenido = <<<ICS
                                    BEGIN:VCALENDAR
                                    VERSION:2.0
                                    PRODID:-//AppActuarFamiempresas//Actividades//ES
                                    BEGIN:VEVENT
                                    UID:$uid
                                    DTSTAMP:$fechaInicio
                                    DTSTART:$fechaInicio
                                    DTEND:$fechaFin
                                    SUMMARY:$titulo
                                    DESCRIPTION:$descripcion
                                    END:VEVENT
                                    END:VCALENDAR
                                    ICS;

                                    // Devuelve descarga directa
                                    return response()->streamDownload(
                                        fn () => print($contenido),
                                        "actividad_{$record->id}.ics",
                                        [
                                            'Content-Type' => 'text/calendar; charset=utf-8',
                                            'Content-Disposition' => 'attachment; filename="actividad_'.$record->id.'.ics"',
                                        ]
                                    );
                                }),
                        ])
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
    public static function getEloquentQuery(): Builder
{
    /** @var \App\Models\User $u */
    $u = auth()->user();

    $query = parent::getEloquentQuery();

    // SUPERADMIN ve todo
    if ($u->hasRole('superadministrador')) {
        return $query;
    }

    // DIRECTORES: ven TODO lo de su SEDE
    // (director de cartera, director quindio, director valle, director risaralda)
    if ($u->hasAnyRole([
        'director de cartera',
        'director quindio',
        'director valle',
        'director risaralda',
    ])) {
        return $query->whereHas('usuario', fn (Builder $q) =>
            $q->where('sede_id', $u->sede_id)
        );
    }

    // Resto de usuarios: solo lo propio
    return $query->where('user_id', $u->id);
}
}

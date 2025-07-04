<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanoMoraResource\Pages;
use App\Filament\Resources\PlanoMoraResource\RelationManagers;
use App\Models\Plano_mora;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use App\Models\AuditoriaMora;
use Illuminate\Support\Facades\Auth;

class PlanoMoraResource extends Resource
{
    protected static ?string $model = Plano_mora::class;
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Plano de Mora';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->columns([
                    Tables\Columns\TextColumn::make('cedula_cliente')->label('Cédula Cliente')->searchable(),
                    Tables\Columns\TextColumn::make('nombre_cliente')->label('Nombre Cliente')->searchable(),
                    Tables\Columns\TextColumn::make('sucursal')->sortable(),
                    Tables\Columns\TextColumn::make('cedula_prom')->label('Cédula Promotor'),
                    Tables\Columns\TextColumn::make('nombre_prom')->label('Nombre Promotor'),
                    Tables\Columns\TextColumn::make('obligacion'),
                    Tables\Columns\TextColumn::make('calificacion')->sortable(),
                    Tables\Columns\TextColumn::make('monto')->money('COP', true)->sortable(),
                    Tables\Columns\TextColumn::make('saldo_actual')->money('COP', true)->sortable(),
                    Tables\Columns\TextColumn::make('vencido_linix')->money('COP', true),
                    Tables\Columns\TextColumn::make('vencido_menor_77')->money('COP', true),
                    Tables\Columns\TextColumn::make('vencido_actuar')->money('COP', true),
                    Tables\Columns\TextColumn::make('dias_linix'),
                    Tables\Columns\TextColumn::make('dias_actuar'),
                    Tables\Columns\TextColumn::make('saldo_total')->money('COP', true)->sortable(),
                    Tables\Columns\TextColumn::make('direccion'),
                    Tables\Columns\TextColumn::make('barrio'),
                    Tables\Columns\TextColumn::make('telefono'),
                    Tables\Columns\TextColumn::make('ciudad')->sortable(),

                    Tables\Columns\TextColumn::make('cedula_cod1')->label('Cédula Codeudor 1'),
                    Tables\Columns\TextColumn::make('nombre_cod1')->label('Nombre Codeudor 1'),
                    Tables\Columns\TextColumn::make('tel_cod1')->label('Tel. Codeudor 1'),
                    Tables\Columns\TextColumn::make('direccion_cod1')->label('Dirección Codeudor 1'),

                    Tables\Columns\TextColumn::make('cedula_cod2')->label('Cédula Codeudor 2'),
                    Tables\Columns\TextColumn::make('nombre_cod2')->label('Nombre Codeudor 2'),
                    Tables\Columns\TextColumn::make('tel_cod2')->label('Tel. Codeudor 2'),
                    Tables\Columns\TextColumn::make('direccion_cod2')->label('Dirección Codeudor 2'),

                    Tables\Columns\TextColumn::make('cedula_cod3')->label('Cédula Codeudor 3'),
                    Tables\Columns\TextColumn::make('nombre_cod3')->label('Nombre Codeudor 3'),
                    Tables\Columns\TextColumn::make('tel_cod3')->label('Tel. Codeudor 3'),
                    Tables\Columns\TextColumn::make('direccion_cod3')->label('Dirección Codeudor 3'),
                ])


           ->filters([
                Tables\Filters\SelectFilter::make('sucursal')
                    ->label('Sucursal')
                    ->options(
                        Plano_mora::query()
                            ->distinct()
                            ->pluck('sucursal', 'sucursal')
                            ->filter()
                            ->toArray()
                    ),

                    Tables\Filters\SelectFilter::make('calificacion')
                        ->label('Calificación')
                        ->options(
                            Plano_mora::query()
                                ->distinct()
                                ->pluck('calificacion', 'calificacion')
                                ->filter()
                                ->toArray()
                        ),

                    Tables\Filters\SelectFilter::make('ciudad')
                        ->label('Ciudad')
                        ->options(
                            Plano_mora::query()
                                ->distinct()
                                ->pluck('ciudad', 'ciudad')
                                ->filter()
                                ->toArray()
                        ),
                ])

            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importarTxt')
                    ->label('Importar desde TXT')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->color('primary')
                    ->form([
                        FileUpload::make('archivo_txt')
                            ->label('Archivo TXT delimitado por |')
                            ->disk('local')
                            ->directory('importaciones')
                            ->acceptedFileTypes(['text/plain'])
                            ->required()
                            ->visibility('private'),
                    ])
                   ->action(function (array $data): void {
                        $ruta = $data['archivo_txt'] ?? null;

                        if (!$ruta || !Storage::disk('local')->exists($ruta)) {
                            Notification::make()->title('Error')->body('El archivo no se pudo procesar.')->danger()->send();
                            return;
                        }

                        $contenido = Storage::disk('local')->get($ruta);
                        if (empty($contenido)) {
                            Notification::make()->title('Error')->body('El archivo está vacío o no se pudo leer.')->danger()->send();
                            return;
                        }

                        $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
                        $lineas = explode("\n", trim($contenido));
                        $lineas = array_filter($lineas); // Quita líneas vacías

                        // Saltar encabezado si lo hay
                        if (str_contains($lineas[0], 'Cedula_Cliente')) {
                            array_shift($lineas);
                        }

                        $importados = 0;

                        foreach ($lineas as $linea) {
                            $campos = explode('|', str_replace("\r", '', trim($linea)));
                            $campos = array_pad($campos, 31, null);

                            // luego ya puedes continuar con Plano_mora::create([...])
                            if (count($campos) < 31) {
                                continue;
                            }

                            $obligacion = $campos[5];
                            $registroExistente = Plano_mora::where('obligacion', $obligacion)->first();

                            if ($registroExistente) {
                                // Guardar auditoría
                                AuditoriaMora::create([
                                    'obligacion'         => $registroExistente->obligacion,
                                    'sucursal'           => $registroExistente->sucursal,
                                    'cedula_prom'        => $registroExistente->cedula_prom,
                                    'nombre_prom'        => $registroExistente->nombre_prom,
                                    'calificacion'       => $registroExistente->calificacion,
                                    'monto'              => $registroExistente->monto,
                                    'saldo_actual'       => $registroExistente->saldo_actual,
                                    'vencido_linix'      => $registroExistente->vencido_linix,
                                    'vencido_menor_77'   => $registroExistente->vencido_menor_77,
                                    'vencido_actuar'     => $registroExistente->vencido_actuar,
                                    'dias_linix'         => $registroExistente->dias_linix,
                                    'dias_actuar'        => $registroExistente->dias_actuar,
                                    'saldo_total'        => $registroExistente->saldo_total,
                                    'user_id'            => Auth::id(),
                                    'fecha_modificacion' => now(),
                                ]);
                            // Actualizar campos
                                $registroExistente->update([
                                    'sucursal'           => $campos[2],
                                    'cedula_prom'        => $campos[3],
                                    'nombre_prom'        => $campos[4],
                                    'calificacion'       => $campos[6],
                                    'monto'              => floatval($campos[7]),
                                    'saldo_actual'       => floatval($campos[8]),
                                    'vencido_linix'      => floatval($campos[9]),
                                    'vencido_menor_77'   => floatval($campos[10]),
                                    'vencido_actuar'     => floatval($campos[11]),
                                    'dias_linix'         => intval($campos[12]),
                                    'dias_actuar'        => intval($campos[13]),
                                    'saldo_total'        => floatval($campos[14]),
                                ]);
                            } else {
                            Plano_mora::create([
                                'cedula_cliente'     => $campos[0] ?? null,
                                'nombre_cliente'     => $campos[1] ?? null,
                                'sucursal'           => $campos[2] ?? null,
                                'cedula_prom'        => $campos[3] ?? null,
                                'nombre_prom'        => $campos[4] ?? null,
                                'obligacion'         => $campos[5] ?? null,
                                'calificacion'       => $campos[6] ?? null,
                                'monto'              => floatval($campos[7] ?? 0),
                                'saldo_actual'       => floatval($campos[8] ?? 0),
                                'vencido_linix'      => floatval($campos[9] ?? 0),
                                'vencido_menor_77'   => floatval($campos[10] ?? 0),
                                'vencido_actuar'     => floatval($campos[11] ?? 0),
                                'dias_linix'         => intval($campos[12] ?? 0),
                                'dias_actuar'        => intval($campos[13] ?? 0),
                                'saldo_total'        => floatval($campos[14] ?? 0),
                                'direccion'          => $campos[15] ?? null,
                                'barrio'             => $campos[16] ?? null,
                                'telefono'           => $campos[17] ?? null,
                                'ciudad'             => $campos[18] ?? null,
                                'cedula_cod1'        => $campos[19] ?? null,
                                'nombre_cod1'        => $campos[20] ?? null,
                                'tel_cod1'           => $campos[21] ?? null,
                                'direccion_cod1'     => $campos[22] ?? null,
                                'cedula_cod2'        => $campos[23] ?? null,
                                'nombre_cod2'        => $campos[24] ?? null,
                                'tel_cod2'           => $campos[25] ?? null,
                                'direccion_cod2'     => $campos[26] ?? null,
                                'cedula_cod3'        => $campos[27] ?? null,
                                'nombre_cod3'        => $campos[28] ?? null,
                                'tel_cod3'           => $campos[29] ?? null,
                                'direccion_cod3'     => $campos[30] ?? null,
                            ]);

                            $importados++;
                            }
                        }

                        Notification::make()
                            ->title('Importación Exitosa')
                            ->body("Se importaron {$importados} registros correctamente.")
                            ->success()
                            ->send();
                    }),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanoMoras::route('/'),
            'create' => Pages\CreatePlanoMora::route('/create'),
            'edit' => Pages\EditPlanoMora::route('/{record}/edit'),
        ];
    }

    function parseFecha(?string $fecha): ?string {
        $fecha = trim($fecha ?? '');

        if (!$fecha || $fecha === '0000-00-00') {
            return null;
        }

        // Formato mm/dd/yyyy
        $dt = \DateTime::createFromFormat('m/d/Y', $fecha);
        return $dt ? $dt->format('Y-m-d') : null;
    }

}

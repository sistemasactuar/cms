<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanoSaldoValorResource\Pages;
use App\Filament\Resources\PlanoSaldoValorResource\RelationManagers;
use App\Models\PlanoSaldoValor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanoSaldoValorResource extends Resource
{
    protected static ?string $model = PlanoSaldoValor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Plano Saldos y Valores';
    protected static ?string $modelLabel = 'Saldos y Valores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('obligacion')->required(),
                Forms\Components\TextInput::make('cc')->required(),
                Forms\Components\TextInput::make('nombres'),
                Forms\Components\TextInput::make('apellidos'),
                Forms\Components\TextInput::make('valor_reportar')->numeric(),
                Forms\Components\TextInput::make('modalidad'),
                Forms\Components\TextInput::make('periodo'),
                Forms\Components\TextInput::make('observacion'),
                Forms\Components\TextInput::make('saldo_capital')->numeric(),
                Forms\Components\TextInput::make('dias_mora')->numeric(),
                Forms\Components\DatePicker::make('fecha_vigencia'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('obligacion')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cc')->searchable()->sortable()->label('Documento'),
                Tables\Columns\TextColumn::make('nombres')->searchable(),
                Tables\Columns\TextColumn::make('apellidos')->searchable(),
                Tables\Columns\TextColumn::make('valor_reportar')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('modalidad')->sortable(),
                Tables\Columns\TextColumn::make('periodo')->sortable(),
                Tables\Columns\TextColumn::make('fecha_vigencia')->date()->sortable(),
                Tables\Columns\TextColumn::make('observacion')->limit(30),
            ])
            ->filters([
                Tables\Filters\Filter::make('fecha_vigencia')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_vigencia', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_vigencia', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importarPlano')
                    ->label('Importar Plano')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('archivo')
                            ->label('Archivo Plano (.txt)')
                            ->acceptedFileTypes(['text/plain'])
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_archivo')
                            ->label('Fecha Vigencia Plano')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $file = \Illuminate\Support\Facades\Storage::disk('public')->path($data['archivo']);
                        if (!file_exists($file)) {
                            \Filament\Notifications\Notification::make()->title('Error')->body('Archivo no encontrado.')->danger()->send();
                            return;
                        }

                        $fecha_archivo = $data['fecha_archivo'];
                        $timestamp = strtotime($fecha_archivo);
                        $fecha_conv_archivo = date('Ymd', $timestamp);
                        $fecha_hoy_anio = date("Y");
                        $fechaManana = date('Ymd', strtotime($fecha_hoy_anio . ' +1 day'));

                        $contenido = file_get_contents($file);
                        // Convert from ISO-8859-1 to UTF-8 to handle Spanish characters like Ñ
                        $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
                        $lineas = explode("\n", $contenido);

                        $datos_Re = [];
                        $datos_Gou = [];
                        $procesados = 0;

                        for ($i = 1; $i < count($lineas); $i++) {
                            if (empty(trim($lineas[$i]))) continue;

                            $valores = explode("|", $lineas[$i]);
                            if (count($valores) < 19) continue;

                            $Vven_capitali = (float)trim($valores[4]) + (float)trim($valores[17]);
                            $Vven_capital = (float)trim($valores[5]);
                            $Vven_interes = (float)trim($valores[6]);
                            $Vven_mora = (float)trim($valores[7]);
                            $Vven_segvida = (float)trim($valores[8]);
                            $Otros_conceptos = (float)trim($valores[18]);

                            if ($Otros_conceptos <= 0) $Otros_conceptos = 0;

                            $ValorVencido = $Vven_capitali + $Vven_capital + $Vven_interes + $Vven_mora + $Vven_segvida + $Otros_conceptos;

                            $ValorCuota = (float)trim($valores[9]);
                            $DiasMora = (int)trim($valores[11]);
                            $ValorProxVencimiento = (float)trim($valores[9]);
                            $Modalidad = trim($valores[2]);
                            $SaldoCapital = (float)trim($valores[12]);
                            $fecha_cuota_str = trim($valores[10]);

                            if ($SaldoCapital < 1) continue;

                            $valorreporte = 0;
                            $observacion = '';

                            if ($DiasMora == 0) {
                                $valorreporte = $ValorProxVencimiento;
                                $observacion = 'Valores 0';
                            } elseif ($DiasMora > 0 && $DiasMora < 30) {
                                $ts_cuota = strtotime($fecha_cuota_str);
                                $dia_cuota = date('j', $ts_cuota);
                                $dia_actual = date('j');

                                if ($dia_cuota <= $dia_actual) {
                                    $valorreporte = $ValorProxVencimiento;
                                    $observacion = 'Vencido <30 con dias';
                                } else {
                                    if ($ValorVencido < $ValorProxVencimiento) {
                                        $valorreporte = $ValorProxVencimiento + $ValorVencido;
                                    } else {
                                        $valorreporte = $ValorVencido;
                                    }
                                    $observacion = 'Vencido <30 ';
                                }
                            } elseif ($DiasMora > 29) {
                                $valorreporte = $ValorVencido;
                                $observacion = 'Vencido > 29';
                            } else {
                                $observacion = 'Error en diasvencidos';
                            }

                            if ($valorreporte == 0) {
                                $valorreporte = $ValorVencido;
                            }

                            if ($valorreporte <= 0 && $ValorVencido == 0) continue;
                            if ($valorreporte <= 0) continue;

                            $nombre1 = trim($valores[15]);
                            $nombre2 = trim($valores[16]);
                            $apellido1 = trim($valores[13]);
                            $apellido2 = trim($valores[14]);

                            if ($nombre1 == "" && $nombre2 == "") $nombre1 = "EMPRESA";
                            if ($apellido1 == "" && $apellido2 == "") $apellido1 = "EMPRESA";

                            $obligacion = trim($valores[0]);
                            $cc = trim($valores[1]);

                            PlanoSaldoValor::updateOrCreate(
                                ['cc' => $cc, 'obligacion' => $obligacion],
                                [
                                    'nombres' => $nombre1 . ' ' . $nombre2,
                                    'apellidos' => $apellido1 . ' ' . $apellido2,
                                    'valor_reportar' => $valorreporte,
                                    'modalidad' => $Modalidad,
                                    'periodo' => date('Ym'),
                                    'observacion' => $observacion,
                                    'saldo_capital' => $SaldoCapital,
                                    'dias_mora' => $DiasMora,
                                    'fecha_vigencia' => $fecha_archivo,
                                ]
                            );

                            $datos_Re[] = [
                                'ID_ENTIDAD' => 9,
                                'ID_SUCURSAL' => 1,
                                'A_OBLIGA' => $obligacion,
                                'NOMBRE_CLIENTE' => $nombre1 . ' ' . $nombre2,
                                'APELLIDO_CLIENTE' => $apellido1 . ' ' . $apellido2,
                                'GRADO' => " ",
                                'V_CUOTA' => $valorreporte,
                                'RECARGO' => " ",
                                'PERIODO' => date('Ym'),
                                'DIA_CORTE' => " ",
                                'TIPO_PAGO' => 3,
                                'C.C' => $cc,
                                'observacion' => $observacion,
                            ];

                            $datos_Gou[] = [
                                'obligacion' => $obligacion,
                                'cc' => $cc,
                                'cc1' => $cc,
                                'nombres' => $nombre1 . ' ' . $nombre2 . ' ' . $apellido1 . ' ' . $apellido2,
                                'valor_reportar' => $valorreporte,
                                'periodo' => $fecha_conv_archivo,
                                'valor_recargo' => '00000',
                                'periodofin' => $fechaManana,
                                'tipo_pago' => 0,
                            ];

                            $procesados++;
                        }

                        if (count($datos_Re) > 0) {
                            $zip = new \ZipArchive();
                            $zipFileName = 'planos_procesados_' . now()->format('Ymd_His') . '.zip';
                            $zipPath = storage_path('app/public/' . $zipFileName);

                            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                                $csvRe = fopen('php://temp', 'r+');
                                fwrite($csvRe, "\xEF\xBB\xBF");
                                fputcsv($csvRe, array_keys($datos_Re[0]));
                                foreach ($datos_Re as $row) fputcsv($csvRe, $row);
                                rewind($csvRe);
                                $zip->addFromString('archivo_Re.csv', stream_get_contents($csvRe));
                                fclose($csvRe);

                                $csvGou = fopen('php://temp', 'r+');
                                fwrite($csvGou, "\xEF\xBB\xBF");
                                $sumatotal = 0;
                                foreach ($datos_Gou as $dg) {
                                    $sumatotal += $dg['valor_reportar'];
                                }
                                $headerGou = [$fecha_conv_archivo, '1000', 'A', '8000803428', count($datos_Gou), '0', 'RECAUDOS MICROSITIO CERRADO', $sumatotal . '00'];
                                fputcsv($csvGou, $headerGou);
                                foreach ($datos_Gou as $dg) {
                                    $dg['valor_reportar'] .= '00';
                                    fputcsv($csvGou, $dg);
                                }
                                rewind($csvGou);
                                $zip->addFromString('archivo_Gou.csv', stream_get_contents($csvGou));
                                fclose($csvGou);

                                $zip->close();

                                \Filament\Notifications\Notification::make()->title('Proceso Completado')->body("Se procesaron {$procesados} registros.")->success()->send();

                                return response()->download($zipPath)->deleteFileAfterSend(true);
                            }
                        }

                        \Filament\Notifications\Notification::make()->title('Proceso Finalizado')->body("Se procesaron {$procesados} registros, pero no se generaron archivos.")->warning()->send();
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlanoSaldoValors::route('/'),
        ];
    }
}

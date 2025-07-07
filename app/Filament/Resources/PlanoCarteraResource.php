<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Plano_cartera;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Pages\ImportarPlanoCartera;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PlanoCarteraResource\Pages;
use App\Filament\Resources\PlanoCarteraResource\RelationManagers;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use App\Models\AuditoriaCartera;
use Illuminate\Support\Facades\Auth;


class PlanoCarteraResource extends Resource
{
    protected static ?string $model = plano_cartera::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Plano de Cartera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Información del Reporte Plano de Cartera')->schema([
                    TextInput::make('suc_cliente'),
                    TextInput::make('id_cliente'),
                    TextInput::make('nombre'),
                    TextInput::make('estado'),
                    TextInput::make('medio_de_pago'),
                    TextInput::make('sld_aportes')->numeric(),
                    TextInput::make('tipo_obl'),
                    TextInput::make('no_obligacion'),
                    TextInput::make('monto_solicitado')->numeric(),
                    TextInput::make('saldo_capital')->numeric(),
                    TextInput::make('sld_int')->numeric(),
                    TextInput::make('sld_mora')->numeric(),
                    TextInput::make('dias_vencidos')->numeric(),
                    TextInput::make('venc_capital')->numeric(),
                    TextInput::make('tasa_col_namv')->numeric(),
                    TextInput::make('tasa_peridodo_namv')->numeric(),
                    TextInput::make('clasificacion'),
                    TextInput::make('linea_de_credito'),
                    TextInput::make('destinacion'),
                    TextInput::make('modalidad'),
                    TextInput::make('medio_de_pago_obligacion'),
                    TextInput::make('tipo_garantia'),
                    TextInput::make('vlr_garantia')->numeric(),
                    TextInput::make('vlr_cobertura_disponible')->numeric(),
                    TextInput::make('vlr_prov_capital')->numeric(),
                    TextInput::make('vlr_prov_interes')->numeric(),
                    TextInput::make('vlr_aportes_util_en_la_provision')->numeric(),
                    DatePicker::make('fec_aprobacion'),
                    DatePicker::make('fec_prestamo'),
                    DatePicker::make('fec_liquidacion'),
                    TextInput::make('vlr_cuota')->numeric(),
                    TextInput::make('no_cuotas')->numeric(),
                    TextInput::make('periodicidad'),
                    DatePicker::make('fec_inicio'),
                    DatePicker::make('fec_vcto_final'),
                    TextInput::make('altura'),
                    DatePicker::make('fec_vencto'),
                    TextInput::make('calif_antes_ley_arrast'),
                    TextInput::make('calif_aplicada'),
                    TextInput::make('calif_mes_ant'),
                    TextInput::make('int_cte_orden')->numeric(),
                    TextInput::make('int_mora_orden')->numeric(),
                    DatePicker::make('fec_historico'),
                    TextInput::make('sld_seg_vida')->numeric(),
                    TextInput::make('sld_seg_patrimonial')->numeric(),
                    TextInput::make('forma_de_pago'),
                    DatePicker::make('fec_ult_pago'),
                    TextInput::make('cuenta_contable'),
                    TextInput::make('suc_credito'),
                    TextInput::make('cod_garantia'),
                    TextInput::make('cantidad_garantias')->numeric(),
                    TextInput::make('porc_cobertura_garant')->numeric(),
                    TextInput::make('vlr_aplicado_garant')->numeric(),
                    TextInput::make('codeudores'),
                    TextInput::make('garantia_real'),
                    TextInput::make('pagare'),
                    TextInput::make('c_costo_cli'),
                    TextInput::make('c_costo_obl'),
                    TextInput::make('dias_vencidos_int')->numeric(),
                    TextInput::make('instan_aprob'),
                    TextInput::make('vencido_int')->numeric(),
                    TextInput::make('dias_vencidos_capital')->numeric(),
                    TextInput::make('prob_incumplimiento_sistema')->numeric(),
                    TextInput::make('prob_incumplimiento_manual')->numeric(),
                    TextInput::make('perdida_incumplimiento_sistema')->numeric(),
                    TextInput::make('perdida_incumplimiento_manual')->numeric(),
                    TextInput::make('valor_expuesto')->numeric(),
                    TextInput::make('valor_perdida_esperada_aplicada')->numeric(),
                    TextInput::make('valor_comercial_activos')->numeric(),
                    TextInput::make('valor_saldo_pasivos')->numeric(),
                    TextInput::make('scoring')->numeric(),
                    DatePicker::make('fecha_restructuracion'),
                    TextInput::make('valor_garantia')->numeric(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('suc_cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('id_cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('estado')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('medio_de_pago')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sld_aportes')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('tipo_obl')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('no_obligacion')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('monto_solicitado')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('saldo_capital')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('sld_int')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('sld_mora')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dias_vencidos')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('venc_capital')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tasa_col_namv')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('tasa_peridodo_namv')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('clasificacion')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('linea_de_credito')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('destinacion')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('modalidad')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('medio_de_pago_obligacion')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tipo_garantia')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('vlr_garantia')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('vlr_cobertura_disponible')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('vlr_prov_capital')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('vlr_prov_interes')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('vlr_aportes_util_en_la_provision')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('fec_aprobacion')->date(),
                Tables\Columns\TextColumn::make('fec_prestamo')->date(),
                Tables\Columns\TextColumn::make('fec_liquidacion')->date(),
                Tables\Columns\TextColumn::make('vlr_cuota')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('no_cuotas')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('periodicidad')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fec_inicio')->date(),
                Tables\Columns\TextColumn::make('fec_vcto_final')->date(),
                Tables\Columns\TextColumn::make('altura')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fec_vencto')->date(),
                Tables\Columns\TextColumn::make('calif_antes_ley_arrast')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('calif_aplicada')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('calif_mes_ant')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('int_cte_orden')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('int_mora_orden')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('fec_historico')->date(),
                Tables\Columns\TextColumn::make('sld_seg_vida')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sld_seg_patrimonial')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('forma_de_pago')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fec_ult_pago')->date(),
                Tables\Columns\TextColumn::make('cuenta_contable')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('suc_credito')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cod_garantia')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cantidad_garantias')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('porc_cobertura_garant')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('vlr_aplicado_garant')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('codeudores')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('garantia_real')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pagare')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('c_costo_cli')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('c_costo_obl')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dias_vencidos_int')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('instan_aprob')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('vencido_int')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('dias_vencidos_capital')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prob_incumplimiento_sistema')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prob_incumplimiento_manual')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('perdida_incumplimiento_sistema')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('perdida_incumplimiento_manual')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('valor_expuesto')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_perdida_esperada_aplicada')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_comercial_activos')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('valor_saldo_pasivos')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('scoring')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('fecha_restructuracion')->date(),
                Tables\Columns\TextColumn::make('valor_garantia')->money('COP')->sortable(),

            ])
            ->filters([
                //
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
                            ->disk('local') // guarda en storage/app/importaciones
                            ->directory('importaciones')
                            ->acceptedFileTypes(['text/plain'])
                            ->required()
                            ->visibility('private'),
                    ])
                    ->action(function (array $data): void {
                        $archivo = $data['archivo'];

                        if ($archivo instanceof \Illuminate\Http\UploadedFile) {
                            $contenido = file($archivo->getRealPath());

                            $importados = 0;

                            foreach ($contenido as $linea) {
                                $campos = explode('|', trim($linea));

                                if (count($campos) < 73) {
                                    continue; // Línea incompleta
                                }

                                $registroExistente = PlanoCartera::where('no_obligacion', $campos[7])->first();

                                if ($registroExistente) {
                                    // Guardar en la tabla auditoría
                                    AuditoriaCartera::create(array_merge(
                                        $registroExistente->only([
                                            'id_cliente',
                                            'tipo_obl',
                                            'no_obligacion',
                                            'saldo_capital',
                                            'sld_aportes',
                                            'sld_int',
                                            'sld_mora',
                                            'dias_vencidos',
                                            'venc_capital',
                                            'tasa_col_namv',
                                            'tasa_peridodo_namv',
                                            'vlr_garantia',
                                            'vlr_cobertura_disponible',
                                            'vlr_prov_capital',
                                            'vlr_prov_interes',
                                            'vlr_aportes_util_en_la_provision',
                                            'vlr_cuota',
                                            'no_cuotas',
                                            'fec_vcto_final',
                                            'altura',
                                            'fec_vencto',
                                            'calif_antes_ley_arrast',
                                            'calif_aplicada',
                                            'calif_mes_ant',
                                            'int_cte_orden',
                                            'int_mora_orden',
                                            'fec_historico',
                                            'sld_seg_vida',
                                            'sld_seg_patrimonial',
                                            'fec_ult_pago',
                                            'cuenta_contable',
                                            'cod_garantia',
                                            'cantidad_garantias',
                                            'porc_cobertura_garant',
                                            'vlr_aplicado_garant',
                                            'pagare',
                                            'c_costo_cli',
                                            'c_costo_obl',
                                            'dias_vencidos_int',
                                            'instan_aprob',
                                            'vencido_int',
                                            'dias_vencidos_capital',
                                            'perdida_incumplimiento_sistema',
                                            'perdida_incumplimiento_manual',
                                            'valor_expuesto',
                                            'valor_perdida_esperada_aplicada',
                                            'valor_comercial_activos',
                                            'valor_saldo_pasivos',
                                            'scoring',
                                            'fecha_restructuracion',
                                            'valor_garantia',
                                        ]),
                                        [
                                            'fecha_modificacion' => now(),
                                            'user_id' => auth()->id(),
                                        ]
                                    ));

                                    // Actualizar el registro existente
                                    $registroExistente->update([
                                        'id_cliente' => $campos[1],
                                        'tipo_obl' => $campos[6],
                                        'saldo_capital' => $campos[9],
                                        'sld_aportes' => $campos[5],
                                        'sld_int' => $campos[10],
                                        'sld_mora' => $campos[11],
                                        'dias_vencidos' => $campos[12],
                                        'venc_capital' => $campos[13],
                                        'tasa_col_namv' => $campos[14],
                                        'tasa_peridodo_namv' => $campos[15],
                                        'vlr_garantia' => $campos[22],
                                        'vlr_cobertura_disponible' => $campos[23],
                                        'vlr_prov_capital' => $campos[24],
                                        'vlr_prov_interes' => $campos[25],
                                        'vlr_aportes_util_en_la_provision' => $campos[26],
                                        'vlr_cuota' => $campos[30],
                                        'no_cuotas' => $campos[31],
                                        'fec_vcto_final' => $campos[34],
                                        'altura' => $campos[35],
                                        'fec_vencto' => $campos[36],
                                        'calif_antes_ley_arrast' => $campos[37],
                                        'calif_aplicada' => $campos[38],
                                        'calif_mes_ant' => $campos[39],
                                        'int_cte_orden' => $campos[40],
                                        'int_mora_orden' => $campos[41],
                                        'fec_historico' => $campos[42],
                                        'sld_seg_vida' => $campos[43],
                                        'sld_seg_patrimonial' => $campos[44],
                                        'fec_ult_pago' => $campos[46],
                                        'cuenta_contable' => $campos[47],
                                        'cod_garantia' => $campos[49],
                                        'cantidad_garantias' => $campos[50],
                                        'porc_cobertura_garant' => $campos[51],
                                        'vlr_aplicado_garant' => $campos[52],
                                        'pagare' => $campos[55],
                                        'c_costo_cli' => $campos[56],
                                        'c_costo_obl' => $campos[57],
                                        'dias_vencidos_int' => $campos[58],
                                        'instan_aprob' => $campos[59],
                                        'vencido_int' => $campos[60],
                                        'dias_vencidos_capital' => $campos[61],
                                        'perdida_incumplimiento_sistema' => $campos[64],
                                        'perdida_incumplimiento_manual' => $campos[65],
                                        'valor_expuesto' => $campos[66],
                                        'valor_perdida_esperada_aplicada' => $campos[67],
                                        'valor_comercial_activos' => $campos[68],
                                        'valor_saldo_pasivos' => $campos[69],
                                        'scoring' => $campos[70],
                                        'fecha_restructuracion' => $campos[71],
                                        'valor_garantia' => $campos[72],
                                    ]);

                                    $importados++;
                                } else {
                                    PlanoCartera::create([
                                        'suc_cliente' => $campos[0],
                                        'id_cliente' => $campos[1],
                                        'nombre' => $campos[2],
                                        'estado' => $campos[3],
                                        'medio_de_pago' => $campos[4],
                                        'sld_aportes' => floatval($campos[5]),
                                        'tipo_obl' => $campos[6],
                                        'no_obligacion' => $campos[7],
                                        'monto_solicitado' => floatval($campos[8]),
                                        'saldo_capital' => floatval($campos[9]),
                                        'sld_int' => floatval($campos[10]),
                                        'sld_mora' => floatval($campos[11]),
                                        'dias_vencidos' => intval($campos[12]),
                                        'venc_capital' => floatval($campos[13]),
                                        'tasa_col_namv' => floatval($campos[14]),
                                        'tasa_peridodo_namv' => floatval($campos[15]),
                                        'clasificacion' => $campos[16],
                                        'linea_de_credito' => $campos[17],
                                        'destinacion' => $campos[18],
                                        'modalidad' => $campos[19],
                                        'medio_de_pago_obligacion' => $campos[20],
                                        'tipo_garantia' => $campos[21],
                                        'vlr_garantia' => floatval($campos[22]),
                                        'vlr_cobertura_disponible' => floatval($campos[23]),
                                        'vlr_prov_capital' => floatval($campos[24]),
                                        'vlr_prov_interes' => floatval($campos[25]),
                                        'vlr_aportes_util_en_la_provision' => floatval($campos[26]),
                                        'fec_aprobacion' => parseFecha($campos[27]),
                                        'fec_prestamo' => parseFecha($campos[28]),
                                        'fec_liquidacion' => parseFecha($campos[29]),
                                        'vlr_cuota' => floatval($campos[30]),
                                        'no_cuotas' => intval($campos[31]),
                                        'periodicidad' => $campos[32],
                                        'fec_inicio' => parseFecha($campos[33]),
                                        'fec_vcto_final' => parseFecha($campos[34]),
                                        'altura' => $campos[35],
                                        'fec_vencto' => parseFecha($campos[36]),
                                        'calif_antes_ley_arrast' => $campos[37],
                                        'calif_aplicada' => $campos[38],
                                        'calif_mes_ant' => $campos[39],
                                        'int_cte_orden' => floatval($campos[40]),
                                        'int_mora_orden' => floatval($campos[41]),
                                        'fec_historico' => parseFecha($campos[42]),
                                        'sld_seg_vida' => floatval($campos[43]),
                                        'sld_seg_patrimonial' => floatval($campos[44]),
                                        'forma_de_pago' => $campos[45],
                                        'fec_ult_pago' => parseFecha($campos[46]),
                                        'cuenta_contable' => $campos[47],
                                        'suc_credito' => $campos[48],
                                        'cod_garantia' => $campos[49],
                                        'cantidad_garantias' => intval($campos[50]),
                                        'porc_cobertura_garant' => floatval($campos[51]),
                                        'vlr_aplicado_garant' => floatval($campos[52]),
                                        'codeudores' => $campos[53],
                                        'garantia_real' => $campos[54],
                                        'pagare' => $campos[55],
                                        'c_costo_cli' => $campos[56],
                                        'c_costo_obl' => $campos[57],
                                        'dias_vencidos_int' => floatval($campos[58]),
                                        'instan_aprob' => $campos[59],
                                        'vencido_int' => floatval($campos[60]),
                                        'dias_vencidos_capital' => intval($campos[61]),
                                        'prob_incumplimiento_sistema' => floatval($campos[62]),
                                        'prob_incumplimiento_manual' => floatval($campos[63]),
                                        'perdida_incumplimiento_sistema' => floatval($campos[64]),
                                        'perdida_incumplimiento_manual' => floatval($campos[65]),
                                        'valor_expuesto' => floatval($campos[66]),
                                        'valor_perdida_esperada_aplicada' => floatval($campos[67]),
                                        'valor_comercial_activos' => floatval($campos[68]),
                                        'valor_saldo_pasivos' => floatval($campos[69]),
                                        'scoring' => floatval($campos[70]),
                                        'fecha_restructuracion' => parseFecha($campos[71]),
                                        'valor_garantia' => floatval($campos[72]),
                                 ]);

                             $importados++;
                            }
                        }

                        Notification::make()
                            ->title("Importación completada: {$importados} registros importados")
                            ->success()
                            ->send();
                        }
})
                    ->after(function () {
                        redirect(request()->header('Referer') ?? route(static::getPages()['index']));
                    })
                    ->modalHeading('Importar Cartera desde TXT')
                    ->modalSubmitActionLabel('Importar')
                    ->modalWidth('lg'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPlanoCarteras::route('/'),
            'create' => Pages\CreatePlanoCartera::route('/create'),
            'edit' => Pages\EditPlanoCartera::route('/{record}/edit'),
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

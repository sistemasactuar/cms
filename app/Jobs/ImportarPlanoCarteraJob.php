<?php

namespace App\Jobs;

use App\Models\AuditoriaCartera;
use App\Models\plano_cartera;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportarPlanoCarteraJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $rutaArchivo;
    protected int $userId;

    public function __construct(string $rutaArchivo, int $userId)
    {
        $this->rutaArchivo = $rutaArchivo;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $contenido = file(storage_path("app/{$this->rutaArchivo}"));

        foreach ($contenido as $linea) {
            $campos = explode('|', trim($linea));

            if (count($campos) < 73) {
                continue; // Línea incompleta
            }

            $registroExistente = plano_cartera::where('no_obligacion', $campos[7])->first();

            if ($registroExistente) {
                // Auditoría antes de modificar
                AuditoriaCartera::create(array_merge(
                    $registroExistente->only([
                        'id_cliente', 'sld_aportes', 'tipo_obl', 'no_obligacion', 'saldo_capital',
                        'sld_int', 'sld_mora', 'dias_vencidos', 'venc_capital', 'tasa_col_namv',
                        'tasa_peridodo_namv', 'vlr_garantia', 'vlr_cobertura_disponible',
                        'vlr_prov_capital', 'vlr_prov_interes', 'vlr_aportes_util_en_la_provision',
                        'vlr_cuota', 'no_cuotas', 'fec_vcto_final', 'altura', 'fec_vencto',
                        'calif_antes_ley_arrast', 'calif_aplicada', 'calif_mes_ant', 'int_cte_orden',
                        'int_mora_orden', 'fec_historico', 'sld_seg_vida', 'sld_seg_patrimonial',
                        'fec_ult_pago', 'cuenta_contable', 'cod_garantia', 'cantidad_garantias',
                        'porc_cobertura_garant', 'vlr_aplicado_garant', 'pagare', 'c_costo_cli',
                        'c_costo_obl', 'dias_vencidos_int', 'instan_aprob', 'vencido_int',
                        'dias_vencidos_capital', 'perdida_incumplimiento_sistema',
                        'perdida_incumplimiento_manual', 'valor_expuesto',
                        'valor_perdida_esperada_aplicada', 'valor_comercial_activos',
                        'valor_saldo_pasivos', 'scoring', 'fecha_restructuracion', 'valor_garantia',
                    ]),
                    [
                        'fecha_modificacion' => now(),
                        'user_id' => $this->userId,
                    ]
                ));

                // Actualizar registro
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
                    'fec_vcto_final' => parseFecha($campos[34]),
                    'altura' => $campos[35],
                    'fec_vencto' => parseFecha($campos[36]),
                    'calif_antes_ley_arrast' => $campos[37],
                    'calif_aplicada' => $campos[38],
                    'calif_mes_ant' => $campos[39],
                    'int_cte_orden' => $campos[40],
                    'int_mora_orden' => $campos[41],
                    'fec_historico' => parseFecha($campos[42]),
                    'sld_seg_vida' => $campos[43],
                    'sld_seg_patrimonial' => $campos[44],
                    'fec_ult_pago' => parseFecha($campos[46]),
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
                    'fecha_restructuracion' => parseFecha($campos[71]),
                    'valor_garantia' => $campos[72],
                ]);
            } else {
                // Crear nuevo registro
                plano_cartera::create([
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
            }
        }
    }

    function parseFecha($fecha)
    {
        return $fecha ? \Carbon\Carbon::createFromFormat('d/m/Y', $fecha) : null;
    }
}

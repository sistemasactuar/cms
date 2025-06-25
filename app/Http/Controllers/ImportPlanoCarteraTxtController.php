<?php

namespace App\Http\Controllers;

use App\Models\Plano_cartera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportPlanoCarteraTxtController extends Controller
{
    public function form()
    {
        return view('import.plano_cartera');
    }

    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:txt',
        ]);

        $file = $request->file('archivo');
        $lines = file($file->getRealPath());

        $headers = [];
        foreach ($lines as $index => $line) {
            $fields = array_map('trim', explode('|', $line));

            // La primera línea tiene los encabezados
            if ($index === 0) {
                $headers = $fields;
                continue;
            }

            $data = array_combine($headers, $fields);

           Plano_cartera::create([
                'sucursal_cliente' => $data['Suc. Cliente'] ?? null,
                'id_cliente' => $data['Id. Cliente'] ?? null,
                'nombre' => $data['Nombre'] ?? null,
                'estado' => $data['Estado'] ?? null,
                'medio_pago' => $data['Medio de pago'] ?? null,
                'sld_aportes' => $data['Sld Aportes'] ?? null,
                'tipo_obligacion' => $data['Tipo Obl.'] ?? null,
                'numero_obligacion' => $data['No. Obligación'] ?? null,
                'monto_solicitado' => $data['Monto Solicitado'] ?? null,
                'saldo_capital' => $data['Saldo Capital'] ?? null,
                'sld_int' => $data['Sld Int'] ?? null,
                'sld_mora' => $data['Sld Mora'] ?? null,
                'dias_vencidos' => $data['Dias Vencidos'] ?? null,
                'venc_capital' => $data['Venc.Capital'] ?? null,
                'tasa_col_namv' => $data['Tasa Col.(NAMV)'] ?? null,
                'tasa_periodo_namv' => $data['Tasa Peridodo(NAMV)'] ?? null,
                'clasificacion' => $data['Clasificacion'] ?? null,
                'linea_credito' => $data['Linea de credito'] ?? null,
                'destinacion' => $data['Destinacion'] ?? null,
                'modalidad' => $data['Modalidad'] ?? null,
                'medio_pago_obligacion' => $data['Medio de pago Obligación'] ?? null,
                'tipo_garantia' => $data['Tipo Garantia'] ?? null,
                'valor_garantia' => $data['Vlr Garantia'] ?? null,
                'valor_cobertura_disponible' => $data['Vlr.Cobertura Disponible'] ?? null,
                'valor_prov_capital' => $data['Vlr Prov.Capital'] ?? null,
                'valor_prov_interes' => $data['Vlr. Prov.Interes'] ?? null,
                'valor_aportes_util_provision' => $data['Vlr Aportes Util. en la Provision'] ?? null,
                'fec_aprobacion' => $data['Fec.Aprobacion'] ?? null,
                'fec_prestamo' => $data['Fec. Prestamo'] ?? null,
                'fec_liquidacion' => $data['Fec.Liquidacion'] ?? null,
                'valor_cuota' => $data['Vlr Cuota'] ?? null,
                'numero_cuotas' => $data['No.Cuotas'] ?? null,
                'periodicidad' => $data['Periodicidad'] ?? null,
                'fec_inicio' => $data['Fec Inicio'] ?? null,
                'fec_vcto_final' => $data['Fec. Vcto Final'] ?? null,
                'altura' => $data['Altura'] ?? null,
                'fec_vencto' => $data['Fec Vencto'] ?? null,
                'calif_antes_ley_arrast' => $data['Calif.Antes Ley Arrast.'] ?? null,
                'calif_aplicada' => $data['Calif. Aplicada'] ?? null,
                'calif_mes_ant' => $data['Calif.Mes Ant.'] ?? null,
                'int_cte_orden' => $data['Int Cte Orden'] ?? null,
                'int_mora_orden' => $data['Int Mora Orden'] ?? null,
                'fec_historico' => $data['Fec.Historico'] ?? null,
                'sld_seg_vida' => $data['Sld Seg Vida'] ?? null,
                'sld_seg_patrimonial' => $data['Sld Seg Patrimonial'] ?? null,
                'forma_pago' => $data['Forma de Pago'] ?? null,
                'fec_ult_pago' => $data['Fec Ult.Pago'] ?? null,
                'cuenta_contable' => $data['Cuenta Contable'] ?? null,
                'suc_credito' => $data['Suc.Credito'] ?? null,
                'codigo_garantia' => $data['Cód. Garantía'] ?? null,
                'cantidad_garantias' => $data['Cantidad Garantías'] ?? null,
                'porc_cobertura_garantia' => $data['%Cobertura Garant.'] ?? null,
                'valor_aplicado_garantia' => $data['Vlr.aplicado Garant.'] ?? null,
                'codeudores' => $data['Codeudores'] ?? null,
                'garantia_real' => $data['Garantía Real'] ?? null,
                'pagare' => $data['Pagare'] ?? null,
                'c_costo_cli' => $data['C Costo Cli.'] ?? null,
                'c_costo_obl' => $data['C Costo Obl'] ?? null,
                'dias_vencidos_int' => $data['Dias vencidos Int'] ?? null,
                'instan_aprob' => $data['Instan.Aprob.'] ?? null,
                'vencido_int' => $data['Vencido Int'] ?? null,
                'dias_vencidos_capital' => $data['Dias Vencidos Capital'] ?? null,
                'prob_incumplimiento_sistema' => $data['Probabilidad de incumplimiento sistema'] ?? null,
                'prob_incumplimiento_manual' => $data['Probabilidad de incumplimiento manual'] ?? null,
                'perdida_incumplimiento_sistema' => $data['Perdida dado incumplimiento sistema'] ?? null,
                'perdida_incumplimiento_manual' => $data['Perdida dado incumplimiento manual'] ?? null,
                'valor_expuesto' => $data['Valor expuesto'] ?? null,
                'valor_perdida_esperada_aplicada' => $data['valor perdida esperada aplicada'] ?? null,
                'valor_comercial_activos' => $data['Valor comercial activos'] ?? null,
                'valor_saldo_pasivos' => $data['Valor saldo pasivos'] ?? null,
                'scoring' => $data['Scoring'] ?? null,
                'fecha_restructuracion' => $data['Fecha restructuracion'] ?? null,
                'valor_garantia_total' => $data['Valor garantia'] ?? null,
            ]);
        }

        return redirect()->route('filament.admin.resources.plano-carteras.index')->with('success', 'Importación completada.');
    }
}


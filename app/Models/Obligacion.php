<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Obligacion extends Model
{
    protected $table = 'obligaciones';

    protected $primaryKey = 'Obligacion';
    public $incrementing = false; // Ya que es string y personalizado
    protected $keyType = 'string';

    protected $fillable = [
        'Obligacion',
        'Cedula_Cliente',
        'Cedula_Prom',
        'Calificacion',
        'Monto',
        'Saldo_Actual',
        'Saldo_total',
        'Estado',
        'Tipo_Obl',
        'Monto_Solicitado',
        'CA_Valor_Cuota',
        'CA_Valor_Vencido_Capitalizacion',
        'CA_Valor_Vencido_Capital',
        'CA_Valor_Vencido_Interes',
        'CA_Valor_Vencido_Mora',
        'CA_Valor_Vencido_Seg_Vida',
        'CA_Valor_Vencido_Seg_Patrimonial',
        'CA_Valor_Vencido_Otros_Conceptos',
        'CA_Valor_Proximo_Vencimiento',
        'CA_Fecha_Cuota',
        'CA_Dias_Vencidos',
        'CA_Saldo_Capital',
        'CA_Codigo_Modalidad',
        'Linea_de_credito',
        'Destinacion',
        'Medio_de_pago',
        'Medio_de_pago_Obligacion',
        'Sld_Aportes',
        'Tasa_Col_NAMV',
        'Tasa_Periodo_NAMV',
        'Fec_Aprobacion',
        'Fec_Prestamo',
        'Fec_Liquidacion',
        'No_Cuotas',
        'Periodicidad',
        'Fec_Inicio',
        'Fec_Vcto_Final',
        'Altura',
        'Fec_Vencto',
        'Calif_Antes_Ley_Arrast',
        'Calif_Aplicada',
        'Calif_Mes_Ant',
        'Int_Cte_Orden',
        'Int_Mora_Orden',
        'Fec_Historico',
        'Forma_de_Pago',
        'Fec_Ult_Pago',
        'Cuenta_Contable',
        'Suc_Credito',
        'Dias_linix',
        'Dias_actuar',
        'Vencido_linix',
        'Vencido_77',
        'Vencido_Actuar',
        'Dias_vencidos_Int',
        'C_Costo_Obl',
        'Instan_Aprob',
        'Scoring',
        'Fecha_restructuracion',
        'usuario_edita',
    ];
}

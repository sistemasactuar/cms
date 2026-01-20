<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoSaldoValor extends Model
{
    protected $table = 'plano_saldos_valores';

    protected $fillable = [
        'obligacion',
        'cc',
        'nombres',
        'apellidos',
        'valor_reportar',
        'modalidad',
        'periodo',
        'observacion',
        'saldo_capital',
        'dias_mora',
        'fecha_vigencia',
    ];
}

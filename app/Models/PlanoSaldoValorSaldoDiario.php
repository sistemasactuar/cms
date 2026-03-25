<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanoSaldoValorSaldoDiario extends Model
{
    protected $table = 'plano_saldo_valor_saldos_diarios';

    protected $fillable = [
        'plano_saldo_valor_id',
        'obligacion',
        'cc',
        'fecha_archivo',
        'valor_vencido',
        'saldo_capital',
        'dias_mora',
        'valor_cuota',
        'valor_reportar',
        'origen_registro',
        'estado_movimiento',
        'variacion_valor_vencido',
        'variacion_saldo_capital',
    ];

    protected function casts(): array
    {
        return [
            'fecha_archivo' => 'date',
        ];
    }

    public function planoSaldoValor(): BelongsTo
    {
        return $this->belongsTo(PlanoSaldoValor::class, 'plano_saldo_valor_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanoSaldoValor extends Model
{
    protected $table = 'plano_saldos_valores';

    protected $fillable = [
        'obligacion',
        'cc',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'valor_reportar',
        'valor_cuota',
        'valor_vencido',
        'origen_registro',
        'fecha_entrada_plano',
        'estado_registro',
        'modalidad',
        'periodo',
        'observacion',
        'saldo_capital',
        'dias_mora',
        'fecha_vigencia',
        'ultima_fecha_saldo_diario',
        'ultimo_estado_saldo_diario',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vigencia' => 'date',
            'fecha_nacimiento' => 'date',
            'fecha_entrada_plano' => 'date',
            'ultima_fecha_saldo_diario' => 'date',
        ];
    }

    public function saldosDiarios(): HasMany
    {
        return $this->hasMany(PlanoSaldoValorSaldoDiario::class, 'plano_saldo_valor_id');
    }
}

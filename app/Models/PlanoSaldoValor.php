<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function getEstadoSeguimientoAttribute(): string
    {
        if ($this->estado_registro === 'saldo_cero' || (float) ($this->saldo_capital ?? 0) <= 0) {
            return 'saldo_cero';
        }

        return (float) ($this->valor_vencido ?? 0) > 0
            ? 'saldo_vencido'
            : 'al_dia';
    }

    public function scopeConSaldoVencido(Builder $query): Builder
    {
        return $query
            ->where('saldo_capital', '>', 0)
            ->where('valor_vencido', '>', 0);
    }

    public function scopeAlDia(Builder $query): Builder
    {
        return $query
            ->where('saldo_capital', '>', 0)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('valor_vencido')
                    ->orWhere('valor_vencido', '<=', 0);
            });
    }

    public function scopeConSaldoCero(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder
                ->where('estado_registro', 'saldo_cero')
                ->orWhere('saldo_capital', '<=', 0);
        });
    }
}

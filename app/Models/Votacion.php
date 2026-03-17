<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Votacion extends Model
{
    use HasFactory;

    protected $table = 'vot_votaciones';

    protected $fillable = [
        'titulo',
        'slug',
        'descripcion_publica',
        'tipo_votacion',
        'logo_path',
        'cupos',
        'max_selecciones',
        'orden_del_dia',
        'aceptacion_obligatoria',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'aceptacion_obligatoria' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function planillas(): HasMany
    {
        return $this->hasMany(VotacionPlanilla::class, 'votacion_id')->orderBy('numero');
    }

    public function candidatos(): HasMany
    {
        return $this->hasMany(VotacionCandidato::class, 'votacion_id')->orderBy('numero')->orderBy('nombre');
    }

    public function votos(): HasMany
    {
        return $this->hasMany(VotacionVoto::class, 'votacion_id');
    }

    public function scopeDisponiblesPortal(Builder $query): Builder
    {
        return $query
            ->where('activo', true)
            ->where('estado', 'publicada')
            ->orderBy('fecha_inicio')
            ->orderBy('titulo');
    }

    public function getTipoVotacionLabelAttribute(): string
    {
        return $this->tipo_votacion === 'planilla' ? 'Planilla' : 'Nominal';
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'publicada' => 'Publicada',
            'cerrada' => 'Cerrada',
            default => 'Borrador',
        };
    }

    public function getLogoUrlAttribute(): string
    {
        if (
            filled($this->logo_path) &&
            str_starts_with($this->logo_path, 'votaciones/') &&
            Storage::disk('public')->exists($this->logo_path)
        ) {
            return route('portal.media', ['path' => $this->logo_path]);
        }

        return asset('images/LOGO-03.png');
    }

    public function estaAbiertaAhora(): bool
    {
        if (!$this->activo || $this->estado !== 'publicada') {
            return false;
        }

        $now = now();

        if ($this->fecha_inicio && $now->lt($this->fecha_inicio)) {
            return false;
        }

        if ($this->fecha_fin && $now->gt($this->fecha_fin)) {
            return false;
        }

        return true;
    }

    public function maxSeleccionesPermitidas(): int
    {
        if ($this->tipo_votacion !== 'nominal') {
            return 1;
        }

        return max(1, (int) ($this->max_selecciones ?: $this->cupos ?: 1));
    }

    public function calcularDistribucionPlanillas(): array
    {
        $planillas = $this->planillas()
            ->withCount([
                'votos as votos_emitidos_count' => fn (Builder $query) => $query->whereNotNull('voto_emitido_at'),
            ])
            ->get();

        $totalVotos = (int) $planillas->sum('votos_emitidos_count');
        $cupos = max(1, (int) $this->cupos);
        $resultado = [];

        if ($totalVotos === 0) {
            foreach ($planillas as $planilla) {
                $resultado[$planilla->id] = [
                    'votos' => 0,
                    'porcentaje' => 0,
                    'cupos' => 0,
                ];
            }

            return $resultado;
        }

        $cuposAsignados = 0;

        foreach ($planillas as $planilla) {
            $votos = (int) $planilla->votos_emitidos_count;
            $exacto = ($votos / $totalVotos) * $cupos;
            $base = (int) floor($exacto);
            $resultado[$planilla->id] = [
                'votos' => $votos,
                'porcentaje' => round(($votos / $totalVotos) * 100, 2),
                'cupos' => $base,
                'residuo' => $exacto - $base,
            ];
            $cuposAsignados += $base;
        }

        $faltantes = $cupos - $cuposAsignados;

        if ($faltantes > 0) {
            $ordenados = collect($resultado)
                ->sort(function (array $left, array $right): int {
                    if ($left['residuo'] === $right['residuo']) {
                        if ($left['votos'] === $right['votos']) {
                            return 0;
                        }

                        return $left['votos'] < $right['votos'] ? 1 : -1;
                    }

                    return $left['residuo'] < $right['residuo'] ? 1 : -1;
                })
                ->keys()
                ->values();

            for ($i = 0; $i < $faltantes; $i++) {
                $planillaId = $ordenados[$i] ?? null;

                if ($planillaId === null) {
                    break;
                }

                $resultado[$planillaId]['cupos']++;
            }
        }

        foreach ($resultado as $planillaId => $item) {
            unset($resultado[$planillaId]['residuo']);
        }

        return $resultado;
    }
}

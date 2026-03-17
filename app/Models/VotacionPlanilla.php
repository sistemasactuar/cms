<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class VotacionPlanilla extends Model
{
    use HasFactory;

    protected $table = 'vot_planillas';

    protected $fillable = [
        'votacion_id',
        'nombre',
        'numero',
        'descripcion',
        'color',
        'logo_path',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function votacion(): BelongsTo
    {
        return $this->belongsTo(Votacion::class, 'votacion_id');
    }

    public function candidatos(): HasMany
    {
        return $this->hasMany(VotacionCandidato::class, 'planilla_id')->orderBy('numero')->orderBy('nombre');
    }

    public function votos(): HasMany
    {
        return $this->hasMany(VotacionVoto::class, 'planilla_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (
            filled($this->logo_path) &&
            str_starts_with($this->logo_path, 'votaciones/') &&
            Storage::disk('public')->exists($this->logo_path)
        ) {
            return route('portal.media', ['path' => $this->logo_path]);
        }

        return null;
    }
}

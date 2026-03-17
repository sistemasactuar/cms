<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VotacionCandidato extends Model
{
    use HasFactory;

    protected $table = 'vot_candidatos';

    protected $fillable = [
        'votacion_id',
        'planilla_id',
        'aportante_id',
        'nombre',
        'documento',
        'cargo',
        'descripcion',
        'numero',
        'foto_path',
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

    public function planilla(): BelongsTo
    {
        return $this->belongsTo(VotacionPlanilla::class, 'planilla_id');
    }

    public function aportante(): BelongsTo
    {
        return $this->belongsTo(Aportante::class, 'aportante_id');
    }

    public function detallesVoto(): HasMany
    {
        return $this->hasMany(VotacionVotoDetalle::class, 'candidato_id');
    }
}

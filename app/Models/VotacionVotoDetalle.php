<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotacionVotoDetalle extends Model
{
    use HasFactory;

    protected $table = 'vot_voto_detalles';

    protected $fillable = [
        'voto_id',
        'candidato_id',
    ];

    public function voto(): BelongsTo
    {
        return $this->belongsTo(VotacionVoto::class, 'voto_id');
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(VotacionCandidato::class, 'candidato_id');
    }
}

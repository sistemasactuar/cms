<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aportante extends Model
{
    use HasFactory;

    protected $table = 'vot_aportantes';

    protected $fillable = [
        'nombre',
        'documento',
        'correo',
        'telefono',
        'password',
        'ultimo_ingreso_at',
        'activo',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_ingreso_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function votos(): HasMany
    {
        return $this->hasMany(VotacionVoto::class, 'aportante_id');
    }

    public function candidaturas(): HasMany
    {
        return $this->hasMany(VotacionCandidato::class, 'aportante_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Analistas;

class Sede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombresede', // SEDE_DESC Nombre de la sede
        'activo',     // SEDE_EST  Indica si está activa
    ];

    public function analistas()
    {
        return $this->hasMany(Analistas::class);
    }
}

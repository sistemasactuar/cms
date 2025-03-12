<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombresede', // SEDE_DESC Nombre de la sede
        'activo',     // SEDE_EST  Indica si está activa
    ];

    public function puntos()
    {
        return $this->hasMany(Punto::class, 'idSede');
    }
}

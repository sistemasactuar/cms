<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano4111 extends Model
{
    protected $fillable = [
        'cedula',
        'asociado',
        'modalidad',
        'calificacion',
        'obligacion',
        'telefono',
        'celular',
        'ciudad',
        'saldo_capital',
        'capital_vencido',
        'dias_vencidos',
        'asesor',
    ];
}

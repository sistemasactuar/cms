<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analistas extends Model
{
    protected $primaryKey = 'Cedula_Prom';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Cedula_Prom',
        'sede_id',
        'Sucursal',
        'AP_Nombre1',
        'AP_Nombre2',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }
}


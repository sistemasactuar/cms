<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividades extends Model
{
    protected $table = 'actividades';

    protected $fillable = [
        'titulo', 'descripcion', 'fecha_programada', 'estado',
        'latitud', 'longitud', 'user_id',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'fecha_programada' => 'datetime',
    ];

    // 👇 importante para el Map::make('location')
    protected $appends = ['location'];

    public function getLocationAttribute(): ?array
    {
        if (is_null($this->latitud) || is_null($this->longitud)) {
            return null;
        }

        return [
            'lat' => (float) $this->latitud,
            'lng' => (float) $this->longitud,
        ];
    }

    public function setLocationAttribute($value): void
    {
        // $value puede venir como ['lat'=>..., 'lng'=>...] o null
        $lat = data_get($value, 'lat');
        $lng = data_get($value, 'lng');

        $this->attributes['latitud']  = $lat !== null ? (float) $lat : null;
        $this->attributes['longitud'] = $lng !== null ? (float) $lng : null;
    }
}

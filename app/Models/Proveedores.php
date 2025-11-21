<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedores extends Model
{
   protected $fillable = [
        'nombre', 'contacto', 'servicio', 'responsable_id',
        'telefono_resp', 'correo_resp'
    ];

    public function evaluaciones()
    {
        return $this->hasMany(\App\Models\EvaluacionProveedor::class);
    }
    public function responsable()
    {
        return $this->belongsTo(Responsable::class);
    }
    protected static function booted()
    {
        static::saved(function ($proveedor) {
            if ($proveedor->responsable_id) {

                \App\Models\EvaluacionProveedor::firstOrCreate(
                    [
                        'proveedor_id' => $proveedor->id,
                        'responsable_id' => $proveedor->responsable_id,
                    ],
                    [
                        'fecha' => now(),
                    ]
                );
            }
        });
    }
}

<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'clave_portal',
        'token_publico',
    ];

    protected static function booted()
    {
        static::creating(function ($r) {
            if (empty($r->token_publico)) {
                $r->token_publico = Str::uuid();
            }
        });
    }

    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionProveedor::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications'; // Asegurar el nombre de la tabla

    protected $fillable = [
        'name',
        'description',
        'version',
        'status',
        'url',
        'publico',
        'manuals',
        'videos',
    ];

    protected $casts = [
        'manuals' => 'array',
        'videos' => 'array',
        'publico' => 'boolean',
    ];
}

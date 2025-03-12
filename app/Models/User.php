<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Nombre del usuario
        'email', // Correo electrónico
        'password', // Contraseña
        'tipoDocumento', // Tipo de Documento (PER_TIPDOC_COD)
        'numeroDocumento', // Número de Documento (PER_DOCUMENTO)
        'nombres', // Nombres (PER_NOMBRE)
        'apellidos', // Apellidos (PER_APELLIDOS)
        'codigo', // Código del Funcionario (USUCADV_CODIGO)
        'perfil_id', // ID del Perfil
        'proceso_id', // ID del Proceso
        'activo', // Activo/Inactivo
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function username(): string
    {
        return 'username';
    }
    /* public function canAccessPanel(Panel $panel): bool
     {
         return $this->hasRole(['Superadmin','admin']);
     }*/
}

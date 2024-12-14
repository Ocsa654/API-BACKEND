<?php
## app\Models\Usuario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellido',
        'correo_electronico',
        'contraseña',
        'fecha_nacimiento',
        'url_imagenPerfil',
    ];

    

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];
}


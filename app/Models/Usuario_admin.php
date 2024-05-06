<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario_admin extends Model {
  protected $table = 'Usuario_admin';

  protected $fillable = [
    'codigo_trabajador',
    'apellido1',
    'apellido2',
    'nombres',
    'sexo',
    'fecha_nacimiento',
    'email_admin',
    'telefono_casa',
    'telefono_trabajo',
    'telefono_movil',
    'direccion1',
    'cargo'
  ];

  protected $dates = [
    'fecha_nacimiento',
  ];
}

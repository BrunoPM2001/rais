<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Usuario_adminController extends Controller {

  public function getAll() {
    $usuarios = DB::table('Usuario AS a')
      ->join('Usuario_admin AS b', 'b.id', '=', 'a.tabla_id')
      ->select(
        'a.id',
        'a.username',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.telefono_movil',
        'b.cargo',
        'b.created_at',
        'a.estado'
      )
      ->where('a.tabla', '=', 'Usuario_admin')
      ->get();

    return ['data' => $usuarios];
  }

  public function getOne($id) {
    $usuario = DB::table('Usuario AS a')
      ->join('Usuario_admin AS b', 'b.id', '=', 'a.tabla_id')
      ->select(
        'a.id',
        'a.tabla_id',
        'a.username',
        'b.codigo_trabajador',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.sexo',
        'b.fecha_nacimiento',
        'b.email',
        'b.telefono_casa',
        'b.telefono_trabajo',
        'b.telefono_movil',
        'b.direccion1',
        'b.cargo',
        'a.estado'
      )
      ->where('a.tabla', '=', 'Usuario_admin')
      ->where('a.id', '=', $id)
      ->first();

    return $usuario;
  }
}

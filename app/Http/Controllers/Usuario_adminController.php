<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use App\Models\Usuario;
use Illuminate\Http\Request;
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
      ->where('tabla', '=', 'Usuario_admin')
      ->get();

    return ['data' => $usuarios];
  }

  public function getOne($id) {
    $usuario = Usuario::with('user_admin')
      ->where('id', '=', $id)->get();

    return $usuario[0];
  }

  public function main() {
    //  Lista de facultades
    $facultad = new Facultad();
    $facultades = $facultad->listar();

    //  Vista de usuarios
    return view('admin.admin.usuarios_admin', [
      'facultades' => $facultades
    ]);
  }
}

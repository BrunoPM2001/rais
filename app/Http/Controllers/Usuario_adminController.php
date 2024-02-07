<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use App\Models\Usuario;
use Illuminate\Http\Request;

class Usuario_adminController extends Controller {

  public function getAll() {
    $usuarios = Usuario::with('user_admin')->get(['*']);
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

<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class Usuario_adminController extends Controller {

  public function getAll() {
    $usuarios = Usuario::with('user_admin')->get(['*']);
    return $usuarios;
  }

  public function main() {
    return view('admin.usuarios_admin');
  }
}

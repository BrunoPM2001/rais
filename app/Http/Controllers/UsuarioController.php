<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Usuario_admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller {


  public function getAll() {
    $usuarios = Usuario::all();
    return $usuarios;
  }

  public function create(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_admin', 'Usuario_evaluador'];

    $tipoUsuario = $request->input('tipo');
    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return response()->json([
        'result' => 'Error',
        'message' => 'No existe ese tipo de usuario'
      ]);
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          //  Validar la data
          $request->validate([
            'username' => 'required|string|unique:Usuario,username|max:255',
            'facultad_id' => 'required|exists:Facultad,id',
            'codigo_trabajador' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'sexo' => 'required|string|max:1',
            'fecha_nacimiento' => 'nullable|date',
            'email_admin' => 'nullable|string|max:255',
            'telefono_casa' => 'nullable|string|max:255',
            'telefono_trabajo' => 'nullable|string|max:255',
            'telefono_movil' => 'nullable|string|max:255',
            'direccion1' => 'nullable|string|max:255',
            'cargo' => 'nullable|string|max:255',
          ]);

          //  Insertar en las BDS
          $user1 = Usuario_admin::create([
            'facultad_id' => $request->facultad_id,
            'codigo_trabajador' => $request->codigo_trabajador,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2,
            'nombres' => $request->nombres,
            'sexo' => $request->sexo,
            'fecha_nacimiento' => date('Y-m-d', strtotime($request->fecha_nacimiento)),
            'email' => $request->email_admin,
            'telefono_casa' => $request->telefono_casa,
            'telefono_trabajo' => $request->telefono_trabajo,
            'telefono_movil' => $request->telefono_movil,
            'direccion1' => $request->direccion1,
            'cargo' => $request->cargo
          ]);

          Usuario::create([
            'username' => $request->username,
            'password' => bcrypt($request->apellido1[0] . $request->apellido2),
            'tabla' => $tipoUsuario,
            'tabla_id' => $user1['id']
          ]);

          //  Respuesta
          return response()->json([
            'result' => 'Success',
            'message' => 'Usuario creado'
          ]);
          break;
        case $tiposUsuarios[1]:
          //  USUARIOS EVALUADORES

          break;
        default:
          return response()->json([
            'result' => 'Error',
            'message' => 'No existe ese tipo de usuario'
          ]);
          break;
      }
    }
  }

  public function login(Request $request) {
    $user = $request->input('username_mail');
    $pass = $request->input('password');

    if (Auth::attempt(['username' => $user, 'password' => $pass]) || Auth::attempt(['email' => $user, 'password' => $pass])) {
      return redirect()->route('view_lineas');
    } else {
      return redirect()->route('login')->withErrors(['message' => 'Credenciales incorrectas']);
    }
  }
}

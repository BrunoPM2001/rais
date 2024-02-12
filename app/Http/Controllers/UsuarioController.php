<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Usuario_admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller {


  public function getAll() {
    $usuarios = Usuario::all();
    return $usuarios;
  }

  public function create(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_admin', 'Usuario_investigador'];

    $tipoUsuario = $request->input('tipo');
    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return redirect()->route('view_usuariosAdmin')->withErrors(['message' => 'Error al crear usuario']);
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
          return redirect()->route('view_usuariosAdmin');
          break;
        case $tiposUsuarios[1]:
          //  USUARIOS INVESTIGADORES
          $request->validate([
            'id' => 'required|exists:Usuario_investigador,id',
            'email' => 'required|string|unique:Usuario,email|max:255',
            'password' => 'required|string|max:255',
          ]);

          $result = DB::table('Usuario')
            ->where('tabla_id', '=', $request->id)
            ->where('tabla', '=', $tipoUsuario)
            ->count();

          if ($result > 0) {
            return redirect()->route('view_usuariosInvestigadores')->withErrors(['message' => 'El investigador ya tiene cuenta']);
          } else {
            Usuario::create([
              'email' => $request->email,
              'password' => bcrypt($request->password),
              'tabla' => $tipoUsuario,
              'tabla_id' => $request->id
            ]);
            return redirect()->route('view_usuariosInvestigadores');
          }
          break;
        default:
          return redirect()->route('view_usuariosAdmin')->withErrors(['message' => 'Error al crear usuario']);
          break;
      }
    }
  }

  public function update(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_admin', 'Usuario_investigador'];

    $id = $request->input('id');
    $tabla_id = $request->input('tabla_id');
    $tipoUsuario = $request->input('tipo');
    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return redirect()->route('view_usuariosAdmin')->withErrors(['message' => 'Error al actualizar usuario']);
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          //  Validar la data
          $request->validate([
            'username' => 'required|string|unique:Usuario,username,' . $id . ',id|max:255',
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

          //  Encontrar ambas filas (Usuario_admin y Usuario) para actualizar
          $usuario_admin = Usuario_admin::findOrFail($tabla_id);
          $usuario_admin->update([
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

          $usuario = Usuario::findOrFail($id);
          $usuario->update([
            'username' => $request->username,
          ]);

          //  Respuesta
          return ['result' => 'Success'];
          break;
        case $tiposUsuarios[1]:
          //  USUARIOS INVESTIGADORES
          //  Validar la data
          $request->validate([
            'email' => 'required|string|unique:Usuario,email,' . $id . ',id|max:255',
            'estado' => 'required|bool',
            'password' => 'nullable|string|max:255',
          ]);

          $usuario = Usuario::findOrFail($id);
          $usuario->email = $request->input('email');
          $usuario->estado = $request->input('estado');
          if ($request->input('password') != null) {
            $usuario->password = bcrypt($request->input('password'));
          }
          $usuario->save();

          //  Respuesta
          return ['result' => 'Success'];
          break;
        default:
          return redirect()->route('view_usuariosAdmin')->withErrors(['message' => 'Error al crear usuario']);
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

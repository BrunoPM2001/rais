<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Usuario_admin;
use App\Models\Usuario_investigador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsuarioController extends Controller {

  public function create(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_admin', 'Usuario_investigador'];

    $tipoUsuario = $request->input('tipo');
    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return ['message' => 'error', 'detail' => 'Error al crear usuario'];
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          //  Validar la data
          $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:Usuario,username|max:255',
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

          if ($validator->fails()) {
            return ['message' => 'error', 'detail' => 'Data inválida'];
          }

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
          return ['message' => 'success', 'detail' => 'Usuario creado con éxito'];
          break;
        case $tiposUsuarios[1]:
          //  USUARIOS INVESTIGADORES
          $validator = Validator::make($request->all(), [
            'investigador_id' => 'required|exists:Usuario_investigador,id',
            'email' => 'required|string|unique:Usuario,email|max:255',
            'password' => 'required|string|max:255',
          ]);

          if ($validator->fails()) {
            return ['message' => 'error', 'detail' => 'Data inválida'];
          }

          $result = DB::table('Usuario')
            ->where('tabla_id', '=', $request->investigador_id)
            ->where('tabla', '=', $tipoUsuario)
            ->count();

          if ($result > 0) {
            return ['message' => 'error', 'detail' => 'El investigador ya tiene cuenta'];
          } else {
            Usuario::create([
              'email' => $request->email,
              'password' => bcrypt($request->password),
              'tabla' => $tipoUsuario,
              'tabla_id' => $request->investigador_id
            ]);
            return ['message' => 'success', 'detail' => 'Usuario creado con éxito'];
          }
          break;
        default:
          return ['message' => 'error', 'detail' => 'Error al crear usuario'];
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
      return ['message' => 'error', 'detail' => 'Error al actualizar usuario'];
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          //  Validar la data
          $request->validate([
            'username' => 'required|string|unique:Usuario,username,' . $id . ',id|max:255',
            'codigo_trabajador' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'sexo' => 'required|string|max:1',
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
            'codigo_trabajador' => $request->codigo_trabajador,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2,
            'nombres' => $request->nombres,
            'sexo' => $request->sexo,
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
          return ['message' => 'success', 'detail' => 'Usuario actualizado con éxito'];
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
          return ['message' => 'success', 'detail' => 'Investigador actualizado con éxito'];
          break;
        default:
          return ['message' => 'error', 'detail' => 'Error al actualizar usuario'];
          break;
      }
    }
  }

  public function resetPassword(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_admin', 'Usuario_investigador'];

    $idUsuario = $request->input('id');
    $user = Usuario::findOrFail($idUsuario);
    $tipoUsuario = $user->tabla;

    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return ['message' => 'error', 'detail' => 'Error al reestablecer contraseña'];
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          $usuario_admin = Usuario_admin::find($user->tabla_id);
          $user->update([
            'password' => bcrypt($usuario_admin->apellido1[0] . $usuario_admin->apellido2),
          ]);
          return ['message' => 'success', 'detail' => 'Contraseña reestablecida correctamente'];
          break;
        case $tiposUsuarios[1]:
          $usuario_invest = Usuario_investigador::find($user->tabla_id);
          $user->update([
            'password' => bcrypt($usuario_invest->doc_numero),
          ]);
          return ['message' => 'success', 'detail' => 'Contraseña reestablecida correctamente'];
          break;
        default:
          return ['message' => 'error', 'detail' => 'Error al reestablecer contraseña'];
          break;
      }
    }
  }

  public function delete(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_admin', 'Usuario_investigador'];

    $idUsuario = $request->input('idUser');
    $tipoUsuario = $request->input('tipo');
    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return ['message' => 'error', 'detail' => 'Error al eliminar usuario'];
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          $user = Usuario::find($idUsuario);
          Usuario_admin::find($user->tabla_id)->delete();
          $user->delete();
          return ['message' => 'success', 'detail' => 'Usuario eliminado correctamente'];
          break;
        case $tiposUsuarios[1]:
          $user = Usuario::find($idUsuario)->delete();
          return ['message' => 'success', 'detail' => 'Usuario eliminado correctamente'];
          break;
        default:
          return ['message' => 'error', 'detail' => 'Error al eliminar usuario'];
          break;
      }
    }
  }

  public function createTemporal(Request $request) {
    //  TIPOS DE USUARIOS
    $tiposUsuarios = ['Usuario_investigador'];

    $idUsuario = $request->input('id');
    $user = Usuario::findOrFail($idUsuario);
    $tipoUsuario = $user->tabla;

    if (!in_array($tipoUsuario, $tiposUsuarios)) {
      return ['message' => 'error', 'detail' => 'Error al reestablecer contraseña'];
    } else {
      switch ($tipoUsuario) {
        case $tiposUsuarios[0]:
          $username = "temporal1";
          $pass = Str::random(8);
          Usuario::create([
            'email' => $username,
            'password' => bcrypt($pass),
            'tabla' => $tipoUsuario,
            'tabla_id' => $user->tabla_id
          ]);
          return ['message' => 'info', 'detail' => 'Usuario temporal creado - Usuario: ' . $username . ' | Contraseña: ' . $pass];
          break;
        default:
          return ['message' => 'error', 'detail' => 'Error al reestablecer contraseña'];
          break;
      }
    }
  }
}

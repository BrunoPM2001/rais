<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller {

  public function login(Request $request) {
    $user = $request->input('username_mail');
    $pass = $request->input('password');

    if (Auth::attempt(['username' => $user, 'password' => $pass]) || Auth::attempt(['email' => $user, 'password' => $pass])) {
      //  Datos básicos
      $table = DB::table('Usuario')
        ->select(
          'id',
          'tabla',
          'tabla_id',
          'estado'
        )
        ->where('username', '=', $user)
        ->orWhere('email', '=', $user)
        ->first();

      if ($table->tabla == "Usuario_admin") {
        //  Nombres
        $usuario = DB::table('Usuario_admin')
          ->select([
            DB::raw("CONCAT(nombres, ' ', apellido1, ' ', apellido2) AS nombre")
          ])
          ->where('id', '=', $table->tabla_id)
          ->first();
        //  Token
        $jwt = JWT::encode([
          'id' => $table->table_id,
          'tabla' => $table->tabla,
          'exp' => time() + 7200
        ], env('JWT_SECRET'), 'HS256');
      } else if ($table->tabla == "Usuario_investigador") {
        //  Nombres
        $usuario = DB::table('Usuario_investigador')
          ->select([
            DB::raw("CONCAT(nombres, ' ', apellido1, ' ', apellido2) AS nombre")
          ])
          ->where('id', '=', $table->tabla_id)
          ->first();
        $jwt = JWT::encode([
          'id' => $table->id,
          'tabla' => $table->tabla,
          'investigador_id' => $table->tabla_id,
          'exp' => time() + 7200
        ], env('JWT_SECRET'), 'HS256');
      }
      return ['data' => [
        'usuario' => $usuario->nombre,
        'tabla' => $table->tabla,
        'token' => $jwt
      ]];
    } else {
      return ['data' => "Error"];
    }
  }
}

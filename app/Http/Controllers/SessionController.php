<?php

namespace App\Http\Controllers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller {

  public function login(Request $request) {
    $user = $request->input('username_mail');
    $pass = $request->input('password');

    // $res = Auth::attempt(['username' => $user, 'password' => $pass]) || Auth::attempt(['email' => $user, 'password' => $pass]);
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
        $jwt = JWT::encode([
          'id' => $table->id,
          'tabla' => $table->tabla,
          'exp' => time() + 3600
        ], env('JWT_SECRET'), 'HS256');
      } else if ($table->tabla == "Usuario_investigador") {
        $jwt = JWT::encode([
          'id' => $table->id,
          'tabla' => $table->tabla,
          'investigador_id' => $table->tabla_id,
          'exp' => time() + 3600
        ], env('JWT_SECRET'), 'HS256');
      }
      return ['data' => [
        'usuario' => $user,
        'tabla' => $table->tabla,
        'token' => $jwt
      ]];
    } else {
      return ['data' => "Error"];
    }
  }

  public function checkAuth(Request $request) {
    try {
      $token = $request->header('Authorization');
      $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
      return ['data' => $decoded];
    } catch (Exception $e) {
      return ['data' => 'Error en token'];
    }
  }

  public function checkRole($role, $token) {
    try {
      $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
      if ($role == $decoded->tabla) {
        return true;
      } else {
        return false;
      }
    } catch (Exception $e) {
      return false;
    }
  }
}

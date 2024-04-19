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

    $res = Auth::attempt(['username' => $user, 'password' => $pass]);
    if ($res) {
      //  Datos básicos
      $table = DB::table('Usuario')
        ->select(
          'tabla'
        )
        ->where('username', '=', $user)
        ->first();

      //  Token
      $jwt = JWT::encode([
        'usuario' => $user,
        'tabla' => $table->tabla,
        'exp' => time() + 60
      ], env('JWT_SECRET'), 'HS256');
      return ['data' => [
        'usuario' => $user,
        'tabla' => $table->tabla,
        'token' => $jwt
      ]];
    } else {
      return ['data' => $res];
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
}

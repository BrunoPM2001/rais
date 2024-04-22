<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Response;

class CheckRole {
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next, String $role): Response {
    try {
      $decoded = JWT::decode($request->header('Authorization'), new Key(env('JWT_SECRET'), 'HS256'));
      if ($role == $decoded->tabla) {
        return $next($request);
      } else {
        return response()->json(['error' => 'Unauthorized'], 401);
      }
    } catch (Exception $e) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }
}

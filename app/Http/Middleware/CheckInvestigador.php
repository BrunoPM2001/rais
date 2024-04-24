<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInvestigador {
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response {
    try {
      $decoded = JWT::decode($request->header('Authorization') ?? "", new Key(env('JWT_SECRET'), 'HS256'));
      if ($decoded->tabla == 'Usuario_investigador') {
        $request->attributes->add(['token_decoded' => $decoded]);
        return $next($request);
      } else {
        return response()->json(['error' => 'Unauthorized'], 401);
      }
    } catch (Exception $e) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }
}

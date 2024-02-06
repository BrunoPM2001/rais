<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckRole {
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next, String $tabla): Response {
    if (!Auth::check()) {
      return redirect()->route('login');
    } else {
      if (!Gate::allows($tabla)) {
        return abort(403, 'Permisos insuficientes para acceder a este recurso');
      } else {
        return $next($request);
      }
    }
  }
}

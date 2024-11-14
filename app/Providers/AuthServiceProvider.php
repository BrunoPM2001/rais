<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
  /**
   * The model to policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    //
  ];

  /**
   * Register any authentication / authorization services.
   */
  public function boot(): void {
    // Roles
    Gate::define('Administrador', function (Usuario $usuario) {
      return $usuario->tabla == "Usuario_admin";
    });

    Gate::define('Evaluador', function (Usuario $usuario) {
      return $usuario->tabla == "Usuario_evaluador";
    });

    Gate::define('Facultad', function (Usuario $usuario) {
      return $usuario->tabla == "Usuario_facultad";
    });

    //  ...
  }
}

<?php

use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\Evaluacion_facultadController;
use App\Http\Controllers\Linea_investigacionController;
use App\Http\Controllers\Usuario_adminController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
  return view('welcome');
});

Route::get('/login', function () {
  return view('login');
})->name('login');

//  ADMIN VIEWS
Route::prefix('admin')->middleware('checkRole:Administrador')->group(function () {
  //  FACULTAD
  Route::prefix('facultad')->group(function () {
    Route::get('convocatorias', [Linea_investigacionController::class, 'main'])->name('view_facultad_convocatorias');
    // Route::get('asignacionEvaluadores', [DependenciaController::class, 'main'])->name('view_dependencias');
    // Route::get('proyectosEvaluados', [Usuario_adminController::class, 'main'])->name('view_usuariosAdmin');
  });
  //  ADMIN
  Route::prefix('admin')->group(function () {
    Route::get('lineas', [Linea_investigacionController::class, 'main'])->name('view_lineas');
    Route::get('dependencias', [DependenciaController::class, 'main'])->name('view_dependencias');
    Route::get('usuariosAdmin', [Usuario_adminController::class, 'main'])->name('view_usuariosAdmin');
  });
});

//  API
Route::prefix('api')->group(function () {
  //  ADMIN
  Route::prefix('admin')->group(function () {
    //  Facultad
    Route::prefix('facultad')->group(function () {
      Route::get('getConvocatorias', [Evaluacion_facultadController::class, 'getConvocatorias']);
      Route::get('getDetalleConvocatoria/{periodo}/{tipo_proyecto}', [Evaluacion_facultadController::class, 'getDetalleConvocatoria']);
    });
  });

  //  Dependencias
  Route::prefix('dependencias')->group(function () {
    Route::get('getAll', [DependenciaController::class, 'getAll']);
    Route::get('getOne/{id}', [DependenciaController::class, 'getOne']);
    Route::post('create', [DependenciaController::class, 'create'])->name('create_dependencia');
    Route::post('update', [DependenciaController::class, 'update']);
  });

  //  Lineas de investigación
  Route::prefix('lineasInvestigacion')->group(function () {
    Route::get('getAll', [Linea_investigacionController::class, 'getAll']);
    Route::get('getAllFacultad/{id}', [Linea_investigacionController::class, 'getAllOfFacultad']);
    Route::post('create', [Linea_investigacionController::class, 'create'])->name('create_linea');
  });

  //  Usuarios
  Route::prefix('usuarios')->group(function () {
    Route::get('getUsuarios', [UsuarioController::class, 'getAll']);
    Route::post('create', [UsuarioController::class, 'create'])->name('create_usuario');
    Route::post('update', [UsuarioController::class, 'update']);
    //  Administrador
    Route::get('getUsuariosAdmin', [Usuario_adminController::class, 'getAll']);
    Route::get('getOneAdmin/{id}', [Usuario_adminController::class, 'getOne']);
  });
});



//  Login
Route::post('/reqlogin', [UsuarioController::class, 'login'])->name('login_form');

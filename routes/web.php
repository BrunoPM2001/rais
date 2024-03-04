<?php

use App\Http\Controllers\Admin\Constancias\ReporteController;
use App\Http\Controllers\Admin\Facultad\AsignacionEvaluadorController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\Evaluacion_facultadController;
use App\Http\Controllers\Linea_investigacionController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\Publicacion_autorController;
use App\Http\Controllers\Usuario_adminController;
use App\Http\Controllers\Usuario_investigadorController;
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
    Route::get('convocatorias', [Evaluacion_facultadController::class, 'main'])->name('view_facultad_convocatorias');
    Route::get('asignacionEvaluadores', [AsignacionEvaluadorController::class, 'main'])->name('view_facultad_asignacionEvaluadores');
  });

  //  ADMIN
  Route::prefix('admin')->group(function () {
    Route::get('lineas', [Linea_investigacionController::class, 'main'])->name('view_lineas');
    Route::get('dependencias', [DependenciaController::class, 'main'])->name('view_dependencias');
    Route::get('usuariosAdmin', [Usuario_adminController::class, 'main'])->name('view_usuariosAdmin');
    Route::get('usuariosInvestigadores', [Usuario_investigadorController::class, 'main'])->name('view_usuariosInvestigadores');
  });
});


// +---------------------------------------------------------------------------------------------------------------------------------------------+

//  API
Route::prefix('api')->group(function () {
  //  ADMIN
  Route::prefix('admin')->group(function () {
    //  Constancias
    Route::prefix('constancias')->group(function () {
      Route::get('getConstanciaPuntajePublicaciones/{investigador_id}', [ReporteController::class, 'getConstanciaPuntajePublicaciones']);
      Route::get('getConstanciaPublicacionesCientificas/{investigador_id}', [ReporteController::class, 'getConstanciaPublicacionesCientificas']);
      Route::get('getConstanciaGrupoInvestigacion/{investigador_id}', [ReporteController::class, 'getConstanciaGrupoInvestigacion']);
    });

    //  Facultad
    Route::prefix('facultad')->group(function () {
      Route::get('getConvocatorias', [Evaluacion_facultadController::class, 'getConvocatorias']);
      Route::get('getDetalleConvocatoria/{periodo}/{tipo_proyecto}', [Evaluacion_facultadController::class, 'getDetalleConvocatoria']);
      Route::get('getEvaluadoresConvocatoria/{id}', [Evaluacion_facultadController::class, 'getEvaluadoresConvocatoria']);

      Route::get('getAllEvaluadores', [ProyectoController::class, 'getAllEvaluadores']);
      Route::get('searchEvaluadorBy/{input}', [AsignacionEvaluadorController::class, 'searchEvaluadorBy']);
      Route::get('getEvaluadoresProyecto/{id}', [AsignacionEvaluadorController::class, 'getEvaluadoresProyecto']);
      Route::get('getAllProyectosEvaluados/{periodo}/{tipo_proyecto}', [ProyectoController::class, 'getAllProyectosEvaluados']);
    });

    //  Usuarios
    Route::prefix('usuarios')->group(function () {
      Route::get('getUsuarios', [UsuarioController::class, 'getAll']);
      Route::post('create', [UsuarioController::class, 'create'])->name('create_usuario');
      Route::post('update', [UsuarioController::class, 'update']);
      //  Administrador
      Route::get('getUsuariosAdmin', [Usuario_adminController::class, 'getAll']);
      Route::get('getOneAdmin/{id}', [Usuario_adminController::class, 'getOne']);
      //  Investigador
      Route::get('getUsuariosInvestigadores', [Usuario_investigadorController::class, 'getAll']);
      Route::get('getOneInvestigador/{id}', [Usuario_investigadorController::class, 'getOne']);
      Route::get('searchInvestigadorBy/{input}', [Usuario_investigadorController::class, 'searchInvestigadorBy']);
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
  });
});

//  Login
Route::post('/reqlogin', [UsuarioController::class, 'login'])->name('login_form');

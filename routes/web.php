<?php

use App\Http\Controllers\Admin\Constancias\ReporteController;
use App\Http\Controllers\Admin\Facultad\AsignacionEvaluadorController;
use App\Http\Controllers\Admin\Admin\Linea_investigacionController;
use App\Http\Controllers\Admin\Admin\DependenciaController;
use App\Http\Controllers\Admin\Admin\Usuario_adminController;
use App\Http\Controllers\Admin\Admin\Usuario_investigadorController;
use App\Http\Controllers\Admin\Admin\UsuarioController;
use App\Http\Controllers\Admin\Estudios\ConvocatoriasController;
use App\Http\Controllers\Admin\Estudios\DeudaProyectosController;
use App\Http\Controllers\Admin\Estudios\DocentesController;
use App\Http\Controllers\Admin\Estudios\GruposController;
use App\Http\Controllers\Admin\Estudios\InformesTecnicosController;
use App\Http\Controllers\Admin\Estudios\InvestigadoresController;
use App\Http\Controllers\Admin\Estudios\MonitoreoController;
use App\Http\Controllers\Admin\Estudios\ProyectosFEXController;
use App\Http\Controllers\Admin\Estudios\ProyectosGrupoController;
use App\Http\Controllers\Admin\Estudios\PublicacionesController;
use App\Http\Controllers\Admin\Estudios\RevistasController;
use App\Http\Controllers\Admin\Facultad\ProyectosEvaluadosController;
use App\Http\Controllers\Admin\Reportes\ConsolidadoGeneralController;
use App\Http\Controllers\Admin\Reportes\DocenteController;
use App\Http\Controllers\Admin\Reportes\EstudioController;
use App\Http\Controllers\Admin\Reportes\GrupoController;
use App\Http\Controllers\Admin\Reportes\PresupuestoController;
use App\Http\Controllers\Admin\Reportes\ProyectoController as ReporteProyectoController;
use App\Http\Controllers\Evaluacion_facultadController;
use App\Http\Controllers\ProyectoController;
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
    Route::get('proyectosEvaluados', [ProyectosEvaluadosController::class, 'main'])->name('view_facultad_proyectosEvaluados');
  });

  //  ADMIN
  Route::prefix('admin')->group(function () {
    Route::get('lineas', [Linea_investigacionController::class, 'main'])->name('view_lineas');
    Route::get('dependencias', [DependenciaController::class, 'main'])->name('view_dependencias');
    Route::get('usuariosAdmin', [Usuario_adminController::class, 'main'])->name('view_usuariosAdmin');
    Route::get('usuariosInvestigadores', [Usuario_investigadorController::class, 'main'])->name('view_usuariosInvestigadores');
  });

  //  ESTUDIOS
  Route::prefix('estudios')->group(function () {
    Route::get('grupos', [GruposController::class, 'main'])->name('view_estudios_grupos');
  });
});


// +---------------------------------------------------------------------------------------------------------------------------------------------+

//  API
Route::prefix('api')->group(function () {
  //  ADMIN
  Route::prefix('admin')->group(function () {
    //  Estudios
    Route::prefix('estudios')->group(function () {
      //  Convocatorias
      Route::get('listarConvocatorias', [ConvocatoriasController::class, 'listarConvocatorias']);
      Route::get('getOneConvocatoria/{parent_id}', [ConvocatoriasController::class, 'getOneConvocatoria']);
      Route::get('listaEvaluaciones', [ConvocatoriasController::class, 'listaEvaluaciones']);
      Route::get('verCriteriosEvaluacion/{evaluacion_id}', [ConvocatoriasController::class, 'verCriteriosEvaluacion']);

      //  Grupos
      Route::prefix('grupos')->group(function () {
        Route::get('listadoGrupos', [GruposController::class, 'listadoGrupos']);
        Route::get('listadoSolicitudes', [GruposController::class, 'listadoSolicitudes']);
        Route::get('detalle/{grupo_id}', [GruposController::class, 'detalle']);
        Route::get('miembros/{grupo_id}/{estado}', [GruposController::class, 'miembros']);
        Route::get('docs/{grupo_id}', [GruposController::class, 'docs']);
        Route::get('lineas/{grupo_id}', [GruposController::class, 'lineas']);
        Route::get('proyectos/{grupo_id}', [GruposController::class, 'proyectos']);
        Route::get('publicaciones/{grupo_id}', [GruposController::class, 'publicaciones']);
        Route::get('laboratorios/{grupo_id}', [GruposController::class, 'laboratorios']);
      });

      //  Proyectos grupo
      Route::prefix('proyectosGrupo')->group(function () {
        Route::get('listado/{periodo}', [ProyectosGrupoController::class, 'listado']);
        Route::get('detalle/{proyecto_id}', [ProyectosGrupoController::class, 'detalle']);
        Route::get('miembros/{proyecto_id}', [ProyectosGrupoController::class, 'miembros']);
        Route::get('cartas/{proyecto_id}', [ProyectosGrupoController::class, 'cartas']);
        Route::get('descripcion/{proyecto_id}', [ProyectosGrupoController::class, 'descripcion']);
        Route::get('actividades/{proyecto_id}', [ProyectosGrupoController::class, 'actividades']);
        Route::get('presupuesto/{proyecto_id}', [ProyectosGrupoController::class, 'presupuesto']);
        Route::get('responsable/{proyecto_id}', [ProyectosGrupoController::class, 'responsable']);
      });

      //  Proyectos FEX
      Route::prefix('proyectosFEX')->group(function () {
        Route::get('listado', [ProyectosFEXController::class, 'listado']);
      });

      //  Informe técnico
      Route::prefix('informesTecnicos')->group(function () {
        Route::get('proyectosListado/{periodo}', [InformesTecnicosController::class, 'proyectosListado']);
        Route::get('informes/{proyecto_id}', [InformesTecnicosController::class, 'informes']);
      });

      //  Monitoreo
      Route::prefix('monitoreo')->group(function () {
        Route::get('listadoProyectos/{periodo}/{tipo_proyecto}/{estado_meta}', [MonitoreoController::class, 'listadoProyectos']);
        Route::get('detalleProyecto/{proyecto_id}', [MonitoreoController::class, 'detalleProyecto']);
        Route::get('metasCumplidas/{proyecto_id}', [MonitoreoController::class, 'metasCumplidas']);
        Route::get('publicaciones/{proyecto_id}', [MonitoreoController::class, 'publicaciones']);
        Route::get('listadoPeriodos', [MonitoreoController::class, 'listadoPeriodos']);
        Route::get('listadoTipoProyectos/{meta_periodo_id}', [MonitoreoController::class, 'listadoTipoProyectos']);
        Route::get('listadoPublicaciones/{meta_tipo_proyecto_id}', [MonitoreoController::class, 'listadoPublicaciones']);
      });

      //  Deudas proyecto
      Route::prefix('deudaProyecto')->group(function () {
        Route::get('listadoProyectos/{periodo}/{tipo_proyecto}/{deuda}', [DeudaProyectosController::class, 'listadoProyectos']);
        Route::get('listadoIntegrantes/{proyecto_id}', [DeudaProyectosController::class, 'listadoIntegrantes']);
        Route::get('listadoProyectosNoDeuda/{periodo}/{tipo_proyecto}', [DeudaProyectosController::class, 'listadoProyectosNoDeuda']);
      });

      //  Gestión de publicaciones
      Route::prefix('publicaciones')->group(function () {
        Route::get('listado', [PublicacionesController::class, 'listado']);
        Route::get('listadoInvestigador/{investigador_id}', [PublicacionesController::class, 'listadoInvestigador']);
      });

      Route::prefix('sincronizarPub')->group(function () {
        // Route::get('listado/{investigador_id}');
      });

      //  Revistas
      Route::prefix('revistas')->group(function () {
        Route::get('listado', [RevistasController::class, 'listado']);
        Route::get('listadoDBindex', [RevistasController::class, 'listadoDBindex']);
        Route::get('listadoDBwos', [RevistasController::class, 'listadoDBwos']);
      });

      //  Laboratorio
      Route::prefix('laboratorios')->group(function () {
        Route::get('listado', [RevistasController::class, 'listado']);
      });

      //  Gestión de investigadores
      Route::prefix('investigadores')->group(function () {
        Route::get('listado', [InvestigadoresController::class, 'listado']);
      });

      //  Gestión de docentes investigadores
      Route::prefix('docentes')->group(function () {
        Route::get('listadoSolicitudes', [DocentesController::class, 'listadoSolicitudes']);
        Route::get('listadoConstancias', [DocentesController::class, 'listadoConstancias']);
      });
    });

    //  Reportes
    Route::prefix('reportes')->group(function () {
      Route::get('estudio/{tipo}/{periodo}/{facultad}', [EstudioController::class, 'reporte']);
      Route::get('grupo/{estado}/{facultad}/{miembros}', [GrupoController::class, 'reporte']);
      Route::get('proyecto/{facultad}/{tipo}/{periodo}', [ReporteProyectoController::class, 'reporte']);
      Route::get('docente/{investigador_id}', [DocenteController::class, 'reporte']);
      Route::get('consolidadoGeneral/{periodo}', [ConsolidadoGeneralController::class, 'reporte']);
      Route::get('presupuesto/{facultad_id}/{periodo}', [PresupuestoController::class, 'reporte']);
    });

    //  Constancias
    Route::prefix('constancias')->group(function () {
      Route::get('getConstanciaPuntajePublicaciones/{investigador_id}', [ReporteController::class, 'getConstanciaPuntajePublicaciones']);
      Route::get('getConstanciaPublicacionesCientificas/{investigador_id}', [ReporteController::class, 'getConstanciaPublicacionesCientificas']);
      Route::get('getConstanciaGrupoInvestigacion/{investigador_id}', [ReporteController::class, 'getConstanciaGrupoInvestigacion']);
    });


    //  Admin
    Route::prefix('admin')->group(function () {
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
        Route::get('getOne/{id}', [DependenciaController::class, 'getOne']);
        Route::get('getAll', [DependenciaController::class, 'getAll']);
        Route::post('create', [DependenciaController::class, 'create'])->name('create_dependencia');
        Route::post('update', [DependenciaController::class, 'update']);
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

      //  Lineas de investigación
      Route::prefix('lineasInvestigacion')->group(function () {
        Route::get('getAllFacultad/{facultad_id}', [Linea_investigacionController::class, 'getAllOfFacultad']);
        Route::get('getAll/{facultad_id}', [Linea_investigacionController::class, 'getAll']);
        Route::post('create', [Linea_investigacionController::class, 'create'])->name('create_linea');
      });
    });
  });
});

//  Login
Route::post('/reqlogin', [UsuarioController::class, 'login'])->name('login_form');

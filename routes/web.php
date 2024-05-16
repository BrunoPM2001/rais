<?php

use App\Http\Controllers\Admin\Constancias\ReporteController;
use App\Http\Controllers\Admin\Facultad\AsignacionEvaluadorController;
use App\Http\Controllers\Admin\Admin\Linea_investigacionController;
use App\Http\Controllers\Admin\Admin\DependenciaController;
use App\Http\Controllers\Admin\Admin\Usuario_adminController;
use App\Http\Controllers\Admin\Admin\Usuario_investigadorController;
use App\Http\Controllers\Admin\Admin\UsuarioController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Estudios\ConvocatoriasController;
use App\Http\Controllers\Admin\Estudios\DeudaProyectosController;
use App\Http\Controllers\Admin\Estudios\DocentesController;
use App\Http\Controllers\Admin\Estudios\GruposController;
use App\Http\Controllers\Admin\Estudios\InformesTecnicosController;
use App\Http\Controllers\Admin\Estudios\InvestigadoresController;
use App\Http\Controllers\Admin\Estudios\LaboratoriosController;
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
use App\Http\Controllers\SessionController;
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

//  Auth
// Route::post('login', [SessionController::class, 'login']);
Route::get('checkAuth', [SessionController::class, 'checkAuth']);


Route::prefix('api')->group(function () {
  //  ADMIN
  Route::prefix('admin')->group(function () {
    //  Estudios
    Route::prefix('estudios')->group(function () {
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
        Route::get('listadoProyectosNoDeuda', [DeudaProyectosController::class, 'listadoProyectosNoDeuda']);
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
        Route::get('listado', [LaboratoriosController::class, 'listado']);
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
  });
});

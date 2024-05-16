<?php

use App\Http\Controllers\Admin\Admin\DependenciaController;
use App\Http\Controllers\Admin\Admin\Linea_investigacionController;
use App\Http\Controllers\Admin\Admin\Usuario_adminController;
use App\Http\Controllers\Admin\Admin\Usuario_investigadorController;
use App\Http\Controllers\Admin\Admin\UsuarioController;
use App\Http\Controllers\Admin\Constancias\ReporteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Estudios\ConvocatoriasController;
use App\Http\Controllers\Admin\Facultad\AsignacionEvaluadorController;
use App\Http\Controllers\Admin\Facultad\ConvocatoriasController as FacultadConvocatoriasController;
use App\Http\Controllers\Admin\Reportes\ProyectoController;
use App\Http\Controllers\Investigador\Actividades\AsesoriaTesisPosController;
use App\Http\Controllers\Investigador\Actividades\AsesoriaTesisPreController;
use App\Http\Controllers\Investigador\Actividades\ComiteEditorialController;
use App\Http\Controllers\Investigador\Actividades\EquipamientoCientificoController;
use App\Http\Controllers\Investigador\Actividades\EventosController;
use App\Http\Controllers\Investigador\Actividades\GrupoEstudioController;
use App\Http\Controllers\Investigador\Actividades\ProyectoConFinanciamientoController;
use App\Http\Controllers\Investigador\Actividades\ProyectoFEXController;
use App\Http\Controllers\Investigador\Actividades\ProyectoMultidisciplinarioController;
use App\Http\Controllers\Investigador\Actividades\ProyectoSinFinanciamientoController;
use App\Http\Controllers\Investigador\Actividades\PublicacionLibrosUniController;
use App\Http\Controllers\Investigador\Actividades\TalleresController;
use App\Http\Controllers\Investigador\DashboardController as InvestigadorDashboardController;
use App\Http\Controllers\Investigador\Publicaciones\ArticulosController;
use App\Http\Controllers\Investigador\Publicaciones\CapitulosLibrosController;
use App\Http\Controllers\Investigador\Publicaciones\EventoController;
use App\Http\Controllers\Investigador\Grupo\GrupoController as InvestigadorGrupoController;
use App\Http\Controllers\Investigador\Publicaciones\LibrosController;
use App\Http\Controllers\Investigador\Publicaciones\PropiedadIntelectualController;
use App\Http\Controllers\Investigador\Publicaciones\TesisAsesoriaController;
use App\Http\Controllers\Investigador\Publicaciones\TesisPropiasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [SessionController::class, 'login']);

//  Admin
Route::prefix('admin')->middleware('checkRole:Usuario_admin')->group(function () {

  //  Main dashboard
  Route::prefix('dashboard')->group(function () {
    Route::get('getData', [DashboardController::class, 'getData']);
    Route::get('metricas', [DashboardController::class, 'metricas']);
    Route::get('tipoPublicaciones', [DashboardController::class, 'tipoPublicaciones']);
    Route::get('proyectosHistoricoData', [DashboardController::class, 'proyectosHistoricoData']);
    Route::get('proyectos/{periodo}', [DashboardController::class, 'proyectos']);
  });

  //  Estudios
  Route::prefix('estudios')->group(function () {
    //  Gestión de convocatorias
    Route::prefix('convocatorias')->group(function () {
      Route::get('listarConvocatorias', [ConvocatoriasController::class, 'listarConvocatorias']);
      Route::get('getOneConvocatoria/{parent_id}', [ConvocatoriasController::class, 'getOneConvocatoria']);
      Route::get('listaEvaluaciones', [ConvocatoriasController::class, 'listaEvaluaciones']);
      Route::get('verCriteriosEvaluacion/{evaluacion_id}', [ConvocatoriasController::class, 'verCriteriosEvaluacion']);
    });
  });

  //  Constancias
  Route::prefix('constancias')->group(function () {
    Route::get('getConstanciaPuntajePublicaciones/{investigador_id}', [ReporteController::class, 'getConstanciaPuntajePublicaciones']);
    Route::get('getConstanciaPublicacionesCientificas/{investigador_id}', [ReporteController::class, 'getConstanciaPublicacionesCientificas']);
    Route::get('getConstanciaGrupoInvestigacion/{investigador_id}', [ReporteController::class, 'getConstanciaGrupoInvestigacion']);
  });

  //  Facultad
  Route::prefix('facultad')->group(function () {
    //  Convocatorias
    Route::prefix('convocatorias')->group(function () {
      Route::get('getConvocatorias', [FacultadConvocatoriasController::class, 'getConvocatorias']);
      Route::get('getDetalleConvocatoria/{periodo}/{tipo_proyecto}', [FacultadConvocatoriasController::class, 'getDetalleConvocatoria']);
      Route::get('getEvaluadoresConvocatoria/{id}', [FacultadConvocatoriasController::class, 'getEvaluadoresConvocatoria']);
    });
  });

  //  Admin
  Route::prefix('admin')->group(function () {

    //  Lineas de investigación
    Route::prefix('lineasInvestigacion')->group(function () {
      Route::get('getAllFacultad/{facultad_id}', [Linea_investigacionController::class, 'getAllOfFacultad']);
      Route::get('getAll/{facultad_id}', [Linea_investigacionController::class, 'getAll']);
      Route::post('create', [Linea_investigacionController::class, 'create']);
    });

    //  Usuarios
    Route::prefix('usuarios')->group(function () {
      Route::post('create', [UsuarioController::class, 'create']);
      Route::put('update', [UsuarioController::class, 'update']);
      Route::put('resetPass', [UsuarioController::class, 'resetPassword']);
      Route::delete('delete', [UsuarioController::class, 'delete']);

      //  Administrador
      Route::get('getUsuariosAdmin', [Usuario_adminController::class, 'getAll']);
      Route::get('getOneAdmin/{id}', [Usuario_adminController::class, 'getOne']);

      //  Investigador
      Route::get('getUsuariosInvestigadores', [Usuario_investigadorController::class, 'getAll']);
      Route::get('getOneInvestigador/{id}', [Usuario_investigadorController::class, 'getOne']);
      Route::get('searchInvestigadorBy/{input}', [Usuario_investigadorController::class, 'searchInvestigadorBy']);
    });
  });
});

Route::prefix('investigador')->middleware('checkInvestigador:Usuario_investigador')->group(function () {

  //  Main dashboard
  Route::prefix('dashboard')->group(function () {
    Route::get('metricas', [InvestigadorDashboardController::class, 'metricas']);
    Route::get('tipoPublicaciones', [InvestigadorDashboardController::class, 'tipoPublicaciones']);
    Route::get('tipoProyectos', [InvestigadorDashboardController::class, 'tipoProyectos']);
  });

  //  Actividades
  Route::prefix('actividades')->group(function () {
    //  Proyectos con financiamiento
    Route::prefix('conFinanciamiento')->group(function () {
      Route::get('listado', [ProyectoConFinanciamientoController::class, 'listado']);
    });
    //  Proyectos sin financiamiento
    Route::prefix('sinFinanciamiento')->group(function () {
      Route::get('listado', [ProyectoSinFinanciamientoController::class, 'listado']);
    });
    //  Proyectos FEX
    Route::prefix('fex')->group(function () {
      Route::get('listado', [ProyectoFEXController::class, 'listado']);
    });
    //  Proyectos multidisciplinario
    Route::prefix('multi')->group(function () {
      Route::get('listado', [ProyectoMultidisciplinarioController::class, 'listado']);
    });
    //  Concurso para publicación de libros universitarios
    Route::prefix('pubLibroUni')->group(function () {
      Route::get('listado', [PublicacionLibrosUniController::class, 'listado']);
    });
    //  Asesoria pregrado
    Route::prefix('asesoriaPre')->group(function () {
      Route::get('listado', [AsesoriaTesisPreController::class, 'listado']);
    });
    //  Asesoria posgrado
    Route::prefix('asesoriaPos')->group(function () {
      Route::get('listado', [AsesoriaTesisPosController::class, 'listado']);
    });
    //  Talleres
    Route::prefix('talleres')->group(function () {
      Route::get('listado', [TalleresController::class, 'listado']);
    });
    //  Eventos
    Route::prefix('eventos')->group(function () {
      Route::get('listado', [EventosController::class, 'listado']);
    });
    //  Equipamiento científico
    Route::prefix('eci')->group(function () {
      Route::get('listado', [EquipamientoCientificoController::class, 'listado']);
    });
    //  Comité editorial
    Route::prefix('comiteEdi')->group(function () {
      Route::get('listado', [ComiteEditorialController::class, 'listado']);
    });
    //  Grupos de estudio
    Route::prefix('gruposEstudio')->group(function () {
      Route::get('listado', [GrupoEstudioController::class, 'listado']);
    });
  });

  //  Publicaciones
  Route::prefix('publicaciones')->group(function () {

    //  Artículos en revistas de investigación
    Route::prefix('articulos')->group(function () {
      Route::get('listado', [ArticulosController::class, 'listado']);
    });

    //  Libros
    Route::prefix('libros')->group(function () {
      Route::get('listado', [LibrosController::class, 'listado']);
    });

    //  Capítulos de libros
    Route::prefix('capitulos')->group(function () {
      Route::get('listado', [CapitulosLibrosController::class, 'listado']);
    });

    //  Participación en eventos
    Route::prefix('eventos')->group(function () {
      Route::get('listado', [EventoController::class, 'listado']);
    });

    //  Tesis propias
    Route::prefix('tesisPropias')->group(function () {
      Route::get('listado', [TesisPropiasController::class, 'listado']);
    });

    //  Tesis propias
    Route::prefix('tesisAsesoria')->group(function () {
      Route::get('listado', [TesisAsesoriaController::class, 'listado']);
    });

    //  Propiedad intelectual
    Route::prefix('propiedadInt')->group(function () {
      Route::get('listado', [PropiedadIntelectualController::class, 'listado']);
    });

    Route::get('listadoRevistasIndexadas', [ArticulosController::class, 'listadoRevistasIndexadas']);
  });

  //  Grupo
  Route::prefix('grupo')->group(function () {
    Route::get('listadoGrupos', [InvestigadorGrupoController::class, 'listadoGrupos']);
    Route::get('listadoSolicitudes', [InvestigadorGrupoController::class, 'listadoSolicitudes']);
  });
});

<?php

use App\Http\Controllers\Admin\Admin\Linea_investigacionController;
use App\Http\Controllers\Admin\Admin\Usuario_adminController;
use App\Http\Controllers\Admin\Admin\Usuario_investigadorController;
use App\Http\Controllers\Admin\Admin\UsuarioController;
use App\Http\Controllers\Admin\Constancias\ReporteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Estudios\ConvocatoriasController;
use App\Http\Controllers\Admin\Estudios\DeudaProyectosController;
use App\Http\Controllers\Admin\Estudios\DocentesController;
use App\Http\Controllers\Admin\Estudios\GestionSUMController;
use App\Http\Controllers\Admin\Estudios\GruposController;
use App\Http\Controllers\Admin\Estudios\InformesTecnicosController;
use App\Http\Controllers\Admin\Estudios\InvestigadoresController;
use App\Http\Controllers\Admin\Estudios\LaboratoriosController;
use App\Http\Controllers\Admin\Estudios\MonitoreoController;
use App\Http\Controllers\Admin\Estudios\ProyectosFEXController;
use App\Http\Controllers\Admin\Estudios\ProyectosGrupoController;
use App\Http\Controllers\Admin\Estudios\PublicacionesController;
use App\Http\Controllers\Admin\Estudios\RevistasController;
use App\Http\Controllers\Admin\Facultad\ConvocatoriasController as FacultadConvocatoriasController;
use App\Http\Controllers\Admin\Reportes\ConsolidadoGeneralController;
use App\Http\Controllers\Admin\Reportes\DocenteController;
use App\Http\Controllers\Admin\Reportes\EstudioController;
use App\Http\Controllers\Admin\Reportes\GrupoController;
use App\Http\Controllers\Admin\Reportes\PresupuestoController;
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
      Route::post('createConvocatoria', [ConvocatoriasController::class, 'createConvocatoria']);
      Route::put('updateConvocatoria', [ConvocatoriasController::class, 'updateConvocatoria']);
      Route::delete('deleteConvocatoria', [ConvocatoriasController::class, 'deleteConvocatoria']);
      Route::get('listarConvocatorias', [ConvocatoriasController::class, 'listarConvocatorias']);
      Route::get('getOneConvocatoria/{parent_id}', [ConvocatoriasController::class, 'getOneConvocatoria']);
      Route::get('listaEvaluaciones', [ConvocatoriasController::class, 'listaEvaluaciones']);
      Route::get('verCriteriosEvaluacion/{evaluacion_id}', [ConvocatoriasController::class, 'verCriteriosEvaluacion']);
    });

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
      //  Miembros
      Route::get('searchDocenteRrhh', [GruposController::class, 'searchDocenteRrhh']);
      Route::get('searchEstudiante', [GruposController::class, 'searchEstudiante']);
      Route::get('searchEgresado', [GruposController::class, 'searchEgresado']);
      Route::get('incluirMiembroData', [GruposController::class, 'incluirMiembroData']);
      Route::post('agregarMiembro', [GruposController::class, 'agregarMiembro']);
      Route::put('excluirMiembro', [GruposController::class, 'excluirMiembro']);
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

    //  Gestión de SUM
    Route::prefix('sum')->group(function () {
      Route::get('listadoLocal', [GestionSUMController::class, 'listadoLocal']);
      Route::get('listadoSum', [GestionSUMController::class, 'listadoSum']);
    });
  });

  //  Reportes
  Route::prefix('reportes')->group(function () {
    Route::get('estudio/{tipo}/{periodo}/{facultad}', [EstudioController::class, 'reporte']);
    Route::get('grupo/{estado}/{facultad}/{miembros}', [GrupoController::class, 'reporte']);
    Route::get('proyecto/{facultad}/{tipo}/{periodo}', [ProyectoController::class, 'reporte']);
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
      Route::put('createTemporal', [UsuarioController::class, 'createTemporal']);

      //  Administrador
      Route::get('getUsuariosAdmin', [Usuario_adminController::class, 'getAll']);
      Route::get('getOneAdmin/{id}', [Usuario_adminController::class, 'getOne']);

      //  Investigador
      Route::get('getUsuariosInvestigadores', [Usuario_investigadorController::class, 'getAll']);
      Route::get('getOneInvestigador/{id}', [Usuario_investigadorController::class, 'getOne']);
      Route::get('searchInvestigadorBy', [Usuario_investigadorController::class, 'searchInvestigadorBy']);
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

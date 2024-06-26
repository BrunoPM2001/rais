<?php

use App\Http\Controllers\Admin\Admin\Linea_investigacionController;
use App\Http\Controllers\Admin\Admin\Usuario_adminController;
use App\Http\Controllers\Admin\Admin\Usuario_investigadorController;
use App\Http\Controllers\Admin\Admin\UsuarioController;
use App\Http\Controllers\Admin\Constancias\ReporteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Economia\GestionComprobantesController;
use App\Http\Controllers\Admin\Economia\GestionTransferenciasController;
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
use App\Http\Controllers\Investigador\Actividades\ProyectoDetalleController;
use App\Http\Controllers\Investigador\Actividades\ProyectoFEXController;
use App\Http\Controllers\Investigador\Actividades\ProyectoMultidisciplinarioController;
use App\Http\Controllers\Investigador\Actividades\ProyectoSinFinanciamientoController;
use App\Http\Controllers\Investigador\Actividades\PublicacionLibrosUniController;
use App\Http\Controllers\Investigador\Actividades\TalleresController;
use App\Http\Controllers\Investigador\Convocatorias\ProCTIController;
use App\Http\Controllers\Investigador\DashboardController as InvestigadorDashboardController;
use App\Http\Controllers\Investigador\Publicaciones\ArticulosController;
use App\Http\Controllers\Investigador\Publicaciones\CapitulosLibrosController;
use App\Http\Controllers\Investigador\Publicaciones\EventoController;
use App\Http\Controllers\Investigador\Grupo\GrupoController as InvestigadorGrupoController;
use App\Http\Controllers\Investigador\Informes\Informe_economicoController;
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

      Route::put('updateDetalle', [GruposController::class, 'updateDetalle']);
      Route::put('aprobarSolicitud', [GruposController::class, 'aprobarSolicitud']);
      Route::put('disolverGrupo', [GruposController::class, 'disolverGrupo']);

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
      Route::get('visualizarMiembro', [GruposController::class, 'visualizarMiembro']);
      Route::put('cambiarCondicion', [GruposController::class, 'cambiarCondicion']);
      Route::put('cambiarCargo', [GruposController::class, 'cambiarCargo']);
    });

    //  Proyectos grupo
    Route::prefix('proyectosGrupo')->group(function () {
      Route::get('listado/{periodo}', [ProyectosGrupoController::class, 'listado']);
      Route::get('detalle/{proyecto_id}', [ProyectosGrupoController::class, 'detalle']);

      Route::put('updateDetalle', [ProyectosGrupoController::class, 'updateDetalle']);

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
      Route::get('proyectosListado', [InformesTecnicosController::class, 'proyectosListado']);
      Route::get('informes/{proyecto_id}', [InformesTecnicosController::class, 'informes']);
      Route::get('getDataInforme', [InformesTecnicosController::class, 'getDataInforme']);
      Route::post('updateInforme', [InformesTecnicosController::class, 'updateInforme']);
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
      Route::get('getOne', [InvestigadoresController::class, 'getOne']);
      Route::post('create', [InvestigadoresController::class, 'create']);
      Route::put('update', [InvestigadoresController::class, 'update']);
      Route::get('getSelectsData', [InvestigadoresController::class, 'getSelectsData']);

      Route::get('licenciasTipo', [InvestigadoresController::class, 'licenciasTipo']);
      Route::get('getLicencias', [InvestigadoresController::class, 'getLicencias']);
      Route::post('addLicencia', [InvestigadoresController::class, 'addLicencia']);
      Route::put('updateLicencia', [InvestigadoresController::class, 'updateLicencia']);
      Route::delete('deleteLicencia', [InvestigadoresController::class, 'deleteLicencia']);

      //  Search
      Route::get('searchDocenteRrhh', [InvestigadoresController::class, 'searchDocenteRrhh']);
      Route::get('searchEstudiante', [InvestigadoresController::class, 'searchEstudiante']);
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

  //  Economía
  Route::prefix('economia')->group(function () {

    Route::prefix('comprobantes')->group(function () {
      Route::get('listadoProyectos', [GestionComprobantesController::class, 'listadoProyectos']);
      Route::get('detalleProyecto', [GestionComprobantesController::class, 'detalleProyecto']);
      Route::get('listadoComprobantes', [GestionComprobantesController::class, 'listadoComprobantes']);
      Route::get('listadoPartidasComprobante', [GestionComprobantesController::class, 'listadoPartidasComprobante']);
      Route::put('updateEstadoComprobante', [GestionComprobantesController::class, 'updateEstadoComprobante']);
      Route::get('listadoPartidasProyecto', [GestionComprobantesController::class, 'listadoPartidasProyecto']);
    });

    Route::prefix('transferencias')->group(function () {
      Route::get('listadoProyectos', [GestionTransferenciasController::class, 'listadoProyectos']);
      Route::get('getSolicitudData', [GestionTransferenciasController::class, 'getSolicitudData']);
      Route::get('movimientosTransferencia', [GestionTransferenciasController::class, 'movimientosTransferencia']);
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

Route::prefix('investigador')->middleware('checkRole:Usuario_investigador')->group(function () {

  //  Main dashboard
  Route::prefix('dashboard')->group(function () {
    Route::get('getData', [InvestigadorDashboardController::class, 'getData']);
    Route::get('metricas', [InvestigadorDashboardController::class, 'metricas']);
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

    Route::get('detalleProyecto', [ProyectoDetalleController::class, 'detalleProyecto']);
  });

  //  Publicaciones
  Route::prefix('publicaciones')->group(function () {

    //  Artículos en revistas de investigación
    Route::prefix('articulos')->group(function () {
      Route::get('listado', [ArticulosController::class, 'listado']);
      Route::post('registrarPaso1', [ArticulosController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [ArticulosController::class, 'datosPaso1']);
    });

    //  Libros
    Route::prefix('libros')->group(function () {
      Route::get('listado', [LibrosController::class, 'listado']);
      Route::post('registrarPaso1', [LibrosController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [LibrosController::class, 'datosPaso1']);
      Route::get('getPaises', [LibrosController::class, 'getPaises']);
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

    //  Paso 1
    Route::get('listadoRevistasIndexadas', [ArticulosController::class, 'listadoRevistasIndexadas']);
    //  Paso 2
    Route::get('proyectos_asociados', [ArticulosController::class, 'proyectos_asociados']);
    Route::get('proyectos_registrados', [ArticulosController::class, 'proyectos_registrados']);
    Route::post('agregarProyecto', [ArticulosController::class, 'agregarProyecto']);
    Route::delete('eliminarProyecto', [ArticulosController::class, 'eliminarProyecto']);
    //  Paso 3
    Route::get('listarAutores', [ArticulosController::class, 'listarAutores']);
    Route::get('searchDocenteRegistrado', [ArticulosController::class, 'searchDocenteRegistrado']);
    Route::get('searchEstudianteRegistrado', [ArticulosController::class, 'searchEstudianteRegistrado']);
    Route::get('searchExternoRegistrado', [ArticulosController::class, 'searchExternoRegistrado']);
    Route::post('agregarAutor', [ArticulosController::class, 'agregarAutor']);
    Route::put('editarAutor', [ArticulosController::class, 'editarAutor']);
    Route::delete('eliminarAutor', [ArticulosController::class, 'eliminarAutor']);
    //  Paso 4
    Route::post('enviarPublicacion', [ArticulosController::class, 'enviarPublicacion']);
  });

  //  Grupo
  Route::prefix('grupo')->group(function () {
    Route::get('listadoGrupos', [InvestigadorGrupoController::class, 'listadoGrupos']);
    Route::get('listadoSolicitudes', [InvestigadorGrupoController::class, 'listadoSolicitudes']);
    Route::get('detalle', [InvestigadorGrupoController::class, 'detalle']);
    Route::get('listarMiembros', [InvestigadorGrupoController::class, 'listarMiembros']);

    Route::get('searchEstudiante', [InvestigadorGrupoController::class, 'searchEstudiante']);
    Route::get('searchEgresado', [InvestigadorGrupoController::class, 'searchEgresado']);
    Route::get('incluirMiembroData', [InvestigadorGrupoController::class, 'incluirMiembroData']);
    Route::post('agregarMiembro', [InvestigadorGrupoController::class, 'agregarMiembro']);
    Route::put('excluirMiembro', [InvestigadorGrupoController::class, 'excluirMiembro']);
    Route::get('visualizarMiembro', [InvestigadorGrupoController::class, 'visualizarMiembro']);
  });

  //  Convocatorias
  Route::prefix('convocatorias')->group(function () {
    Route::get('verificar', [ProCTIController::class, 'verificar']);

    Route::get('datosPaso1', [ProCTIController::class, 'datosPaso1']);
    Route::get('getDataToPaso1', [ProCTIController::class, 'getDataToPaso1']);
    Route::post('registrarPaso1', [ProCTIController::class, 'registrarPaso1']);

    Route::get('getDataPaso2', [ProCTIController::class, 'getDataPaso2']);
    Route::post('registrarPaso2', [ProCTIController::class, 'registrarPaso2']);

    Route::get('listarIntegrantes', [ProCTIController::class, 'listarIntegrantes']);
    Route::get('searchEstudiante', [ProCTIController::class, 'searchEstudiante']);
    Route::get('verificarEstudiante', [ProCTIController::class, 'verificarEstudiante']);
    Route::post('agregarIntegrante', [ProCTIController::class, 'agregarIntegrante']);
    Route::post('agregarIntegranteExterno', [ProCTIController::class, 'agregarIntegranteExterno']);
    Route::delete('eliminarIntegrante', [ProCTIController::class, 'eliminarIntegrante']);

    Route::get('getDataPaso4', [ProCTIController::class, 'getDataPaso4']);
    Route::post('registrarPaso4', [ProCTIController::class, 'registrarPaso4']);

    Route::get('listarActividades', [ProCTIController::class, 'listarActividades']);
    Route::post('agregarActividad', [ProCTIController::class, 'agregarActividad']);
    Route::delete('eliminarActividad', [ProCTIController::class, 'eliminarActividad']);

    Route::get('listarPartidas', [ProCTIController::class, 'listarPartidas']);
    Route::get('listarTiposPartidas', [ProCTIController::class, 'listarTiposPartidas']);
    Route::post('agregarPartida', [ProCTIController::class, 'agregarPartida']);
    Route::delete('eliminarPartida', [ProCTIController::class, 'eliminarPartida']);

    Route::put('enviarProyecto', [ProCTIController::class, 'enviarProyecto']);
    Route::get('reportePDF', [ProCTIController::class, 'reportePDF']);

    //  Extras
    Route::get('getOcde', [ProCTIController::class, 'getOcde']);
    Route::get('getOds', [ProCTIController::class, 'getOds']);
  });

  //  Informes
  Route::prefix('informes')->group(function () {

    Route::prefix('informe_economico')->group(function () {
      Route::get('listadoProyectos', [Informe_economicoController::class, 'listadoProyectos']);
      Route::get('detalles', [Informe_economicoController::class, 'detalles']);
      Route::get('listarPartidas', [Informe_economicoController::class, 'listarPartidas']);
      Route::get('dataComprobante', [Informe_economicoController::class, 'dataComprobante']);
      Route::post('subirComprobante', [Informe_economicoController::class, 'subirComprobante']);
      Route::put('anularComprobante', [Informe_economicoController::class, 'anularComprobante']);
      Route::post('calcularNuevoPresupuesto', [Informe_economicoController::class, 'calcularNuevoPresupuesto']);
    });
  });
});

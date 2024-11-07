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
use App\Http\Controllers\Admin\Estudios\DocenteInvestigadorController;
use App\Http\Controllers\Admin\Estudios\GestionSUMController;
use App\Http\Controllers\Admin\Estudios\GruposController;
use App\Http\Controllers\Admin\Estudios\Informes_tecnicos\UtilInformeAdminController;
use App\Http\Controllers\Admin\Estudios\InformesTecnicosController;
use App\Http\Controllers\Admin\Estudios\InvestigadoresController;
use App\Http\Controllers\Admin\Estudios\LaboratoriosController;
use App\Http\Controllers\Admin\Estudios\MonitoreoController;
use App\Http\Controllers\Admin\Estudios\ProyectosFEXController;
use App\Http\Controllers\Admin\Estudios\ProyectosGrupoController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\PublicacionesUtilsController as PublicacionesPublicacionesUtilsController;
use App\Http\Controllers\Admin\Estudios\PublicacionesController;
use App\Http\Controllers\Admin\Estudios\RevistasController;
use App\Http\Controllers\Admin\Facultad\AsignacionEvaluadorController;
use App\Http\Controllers\Admin\Facultad\ConvocatoriasController as FacultadConvocatoriasController;
use App\Http\Controllers\Admin\Facultad\GestionEvaluadoresController;
use App\Http\Controllers\Admin\Facultad\ProyectosEvaluadosController;
use App\Http\Controllers\Admin\Reportes\ConsolidadoGeneralController;
use App\Http\Controllers\Admin\Reportes\DocenteController;
use App\Http\Controllers\Admin\Reportes\EstudioController;
use App\Http\Controllers\Admin\Reportes\GrupoController;
use App\Http\Controllers\Admin\Reportes\PresupuestoController;
use App\Http\Controllers\Admin\Reportes\ProyectoController;
use App\Http\Controllers\Evaluador\Evaluaciones\EvaluadorProyectosController;
use App\Http\Controllers\Investigador\Actividades\AsesoriaTesisPosController;
use App\Http\Controllers\Investigador\Actividades\AsesoriaTesisPreController;
use App\Http\Controllers\Investigador\Actividades\ComiteEditorialController;
use App\Http\Controllers\Investigador\Actividades\DeudaController;
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
use App\Http\Controllers\Investigador\Convocatorias\PinvposController;
use App\Http\Controllers\Investigador\Convocatorias\ProCTIController;
use App\Http\Controllers\Investigador\Convocatorias\PsinfinvController;
use App\Http\Controllers\Investigador\Convocatorias\PsinfipuController;
use App\Http\Controllers\Investigador\DashboardController as InvestigadorDashboardController;
use App\Http\Controllers\Investigador\Publicaciones\ArticulosController;
use App\Http\Controllers\Investigador\Publicaciones\CapitulosLibrosController;
use App\Http\Controllers\Investigador\Publicaciones\EventoController;
use App\Http\Controllers\Investigador\Grupo\GrupoController as InvestigadorGrupoController;
use App\Http\Controllers\Investigador\Informes\Informe_academicoController;
use App\Http\Controllers\Investigador\Informes\Informe_economicoController;
use App\Http\Controllers\Investigador\Informes\Informes_academicos\InformeUtilsController;
use App\Http\Controllers\Investigador\Perfil\CdiController;
use App\Http\Controllers\Investigador\Perfil\OrcidController;
use App\Http\Controllers\Investigador\Perfil\PerfilController;
use App\Http\Controllers\Investigador\Publicaciones\LibrosController;
use App\Http\Controllers\Investigador\Publicaciones\PropiedadIntelectualController;
use App\Http\Controllers\Investigador\Publicaciones\PublicacionesUtilsController;
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

      //  Evaluaciones
      Route::get('listaEvaluaciones', [ConvocatoriasController::class, 'listaEvaluaciones']);
      Route::get('listadoProyectosCopia', [ConvocatoriasController::class, 'listadoProyectosCopia']);
      Route::post('createEvaluacion', [ConvocatoriasController::class, 'createEvaluacion']);

      //  Criterios
      Route::get('detalleCriterios', [ConvocatoriasController::class, 'detalleCriterios']);
      Route::post('createCriterio', [ConvocatoriasController::class, 'createCriterio']);
      Route::put('editCriterio', [ConvocatoriasController::class, 'editCriterio']);
      Route::put('aprobarCriterios', [ConvocatoriasController::class, 'aprobarCriterios']);
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

      Route::get('docs', [GruposController::class, 'docs']);
      Route::get('editarMiembroData', [GruposController::class, 'editarMiembroData']);
      Route::delete('eliminarDoc', [GruposController::class, 'eliminarDoc']);

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
      Route::get('dataProyecto', [ProyectosGrupoController::class, 'dataProyecto']);

      Route::put('updateDetalle', [ProyectosGrupoController::class, 'updateDetalle']);

      Route::get('miembros', [ProyectosGrupoController::class, 'miembros']);
      Route::get('cartas', [ProyectosGrupoController::class, 'cartas']);
      Route::get('descripcion', [ProyectosGrupoController::class, 'descripcion']);
      Route::get('actividades', [ProyectosGrupoController::class, 'actividades']);
      Route::get('presupuesto', [ProyectosGrupoController::class, 'presupuesto']);
      Route::get('responsable', [ProyectosGrupoController::class, 'responsable']);

      Route::get('exportToWord', [ProyectosGrupoController::class, 'exportToWord']);
      Route::get('reporte', [ProyectosGrupoController::class, 'reporte']);
    });

    //  Proyectos FEX
    Route::prefix('proyectosFEX')->group(function () {
      Route::get('listado', [ProyectosFEXController::class, 'listado']);
      Route::get('lineasUnmsm', [ProyectosFEXController::class, 'lineasUnmsm']);
      Route::post('registrarPaso1', [ProyectosFEXController::class, 'registrarPaso1']);
      Route::post('registrarPaso2', [ProyectosFEXController::class, 'registrarPaso2']);

      Route::post('registrarPaso3', [ProyectosFEXController::class, 'registrarPaso3']);
      Route::put('updateDoc', [ProyectosFEXController::class, 'updateDoc']);
      Route::delete('deleteDoc', [ProyectosFEXController::class, 'deleteDoc']);

      Route::get('datosPaso1', [ProyectosFEXController::class, 'datosPaso1']);
      Route::get('datosPaso2', [ProyectosFEXController::class, 'datosPaso2']);
      Route::get('datosPaso3', [ProyectosFEXController::class, 'datosPaso3']);
    });

    //  Informe técnico
    Route::prefix('informesTecnicos')->group(function () {
      Route::get('proyectosListado', [InformesTecnicosController::class, 'proyectosListado']);
      Route::get('informes/{proyecto_id}', [InformesTecnicosController::class, 'informes']);
      Route::get('getDataInforme', [InformesTecnicosController::class, 'getDataInforme']);
      Route::post('updateInforme', [InformesTecnicosController::class, 'updateInforme']);
      Route::post('loadActividad', [InformesTecnicosController::class, 'loadActividad']);
      Route::get('verAuditoria', [InformesTecnicosController::class, 'verAuditoria']);

      Route::get('reporte', [UtilInformeAdminController::class, 'reporte']);
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
      Route::get('listadoProyectos', [DeudaProyectosController::class, 'listadoProyectos']);
      Route::get('listadoIntegrantes', [DeudaProyectosController::class, 'listadoIntegrantes']);
      Route::get('listadoProyectosNoDeuda', [DeudaProyectosController::class, 'listadoProyectosNoDeuda']);
      Route::get('listadoDeudaAcademica', [DeudaProyectosController::class, 'listadoDeudaAcademica']);
      Route::post('asignarDeuda', [DeudaProyectosController::class, 'asignarDeuda']);
      Route::get('proyectoDeuda', [DeudaProyectosController::class, 'proyectoDeuda']);
      Route::post('subsanarDeuda', [DeudaProyectosController::class, 'subsanarDeuda']);
      Route::get('getTipoDeuda', [DeudaProyectosController::class, 'getTipoDeuda']);
    });

    //  Gestión de publicaciones
    Route::prefix('publicaciones')->group(function () {
      Route::get('listado', [PublicacionesController::class, 'listado']);
      Route::get('reporte', [PublicacionesController::class, 'reporte']);
      Route::get('verAuditoria', [PublicacionesController::class, 'verAuditoria']);

      Route::get('detalle', [PublicacionesController::class, 'detalle']);
      Route::post('updateDetalle', [PublicacionesController::class, 'updateDetalle']);
      Route::get('getTabs', [PublicacionesController::class, 'getTabs']);
      Route::get('infoNuevo', [PublicacionesController::class, 'infoNuevo']);

      //  Paso 1
      Route::post('paso1', [PublicacionesController::class, 'paso1']);
      Route::post('agregarRevista', [PublicacionesPublicacionesUtilsController::class, 'agregarRevista']);
      Route::post('agregarWos', [PublicacionesPublicacionesUtilsController::class, 'agregarWos']);
      Route::post('agregarRevistaPublicacion', [PublicacionesPublicacionesUtilsController::class, 'agregarRevistaPublicacion']);
      Route::get('getPaises', [PublicacionesPublicacionesUtilsController::class, 'getPaises']);
      Route::get('searchRevista', [PublicacionesPublicacionesUtilsController::class, 'searchRevista']);

      //  Paso 2
      Route::post('agregarProyecto', [PublicacionesPublicacionesUtilsController::class, 'agregarProyecto']);
      Route::get('proyectos_registrados', [PublicacionesPublicacionesUtilsController::class, 'proyectos_registrados']);
      Route::delete('eliminarProyecto', [PublicacionesPublicacionesUtilsController::class, 'eliminarProyecto']);

      //  Paso 3
      Route::get('searchDocenteRegistrado', [PublicacionesPublicacionesUtilsController::class, 'searchDocenteRegistrado']);
      Route::get('searchEstudianteRegistrado', [PublicacionesPublicacionesUtilsController::class, 'searchEstudianteRegistrado']);
      Route::get('searchExternoRegistrado', [PublicacionesPublicacionesUtilsController::class, 'searchExternoRegistrado']);

      Route::post('agregarAutor', [PublicacionesPublicacionesUtilsController::class, 'agregarAutor']);
      Route::put('editarAutor', [PublicacionesPublicacionesUtilsController::class, 'editarAutor']);
      Route::delete('eliminarAutor', [PublicacionesPublicacionesUtilsController::class, 'eliminarAutor']);
      Route::put('recalcularPuntaje', [PublicacionesPublicacionesUtilsController::class, 'recalcularPuntaje']);
      Route::put('reOrdenar', [PublicacionesPublicacionesUtilsController::class, 'reOrdenar']);

      Route::get('searchDocentePermanente', [PublicacionesPublicacionesUtilsController::class, 'searchDocentePermanente']);
      Route::put('asociarInvestigador', [PublicacionesPublicacionesUtilsController::class, 'asociarInvestigador']);
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
      Route::get('listado', [DocenteInvestigadorController::class, 'listado']);
      Route::get('constancias', [DocenteInvestigadorController::class, 'constancias']);

      Route::get('evaluarData', [DocenteInvestigadorController::class, 'evaluarData']);
      Route::get('opcionesSubCategorias', [DocenteInvestigadorController::class, 'opcionesSubCategorias']);
      Route::post('aprobarActividad', [DocenteInvestigadorController::class, 'aprobarActividad']);
      //  Cambios de estado admin
      Route::put('evaluar', [DocenteInvestigadorController::class, 'evaluar']);
      Route::put('tramite', [DocenteInvestigadorController::class, 'tramite']);
      Route::post('subirCDI', [DocenteInvestigadorController::class, 'subirCDI']);

      //  Observaciones
      Route::post('observar', [DocenteInvestigadorController::class, 'observar']);
      Route::get('observaciones', [DocenteInvestigadorController::class, 'observaciones']);

      Route::get('fichaEvaluacion', [DocenteInvestigadorController::class, 'fichaEvaluacion']);
      Route::get('constanciaCDI', [DocenteInvestigadorController::class, 'constanciaCDI']);
      Route::get('constanciaCDIFirmada', [DocenteInvestigadorController::class, 'constanciaCDIFirmada']);
      Route::post('enviarCorreo', [DocenteInvestigadorController::class, 'enviarCorreo']);
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
      Route::get('recalcularMontos', [GestionComprobantesController::class, 'recalcularMontos']);
      Route::get('verAuditoria', [GestionComprobantesController::class, 'verAuditoria']);
    });

    Route::prefix('transferencias')->group(function () {
      Route::get('listadoProyectos', [GestionTransferenciasController::class, 'listadoProyectos']);
      Route::get('getSolicitudData', [GestionTransferenciasController::class, 'getSolicitudData']);
      Route::get('movimientosTransferencia', [GestionTransferenciasController::class, 'movimientosTransferencia']);
      Route::post('calificar', [GestionTransferenciasController::class, 'calificar']);
      Route::get('reporte', [GestionTransferenciasController::class, 'reporte']);
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

    //  Gestión de evaluadores
    Route::prefix('gestionEvaluadores')->group(function () {
      Route::get('listado', [GestionEvaluadoresController::class, 'listado']);
      Route::get('searchInvestigador', [GestionEvaluadoresController::class, 'searchInvestigador']);
      Route::post('crearEvaluador', [GestionEvaluadoresController::class, 'crearEvaluador']);
    });

    //  Evaluadores de proyectos
    Route::prefix('evaluadores')->group(function () {
      Route::get('listado', [AsignacionEvaluadorController::class, 'listado']);
      Route::get('evaluadoresProyecto', [AsignacionEvaluadorController::class, 'evaluadoresProyecto']);
      Route::get('searchEvaluadorBy', [AsignacionEvaluadorController::class, 'searchEvaluadorBy']);
      Route::put('updateEvaluadores', [AsignacionEvaluadorController::class, 'updateEvaluadores']);
    });

    //  Proyectos evaluados
    Route::prefix('evaluaciones')->group(function () {
      Route::get('opciones', [ProyectosEvaluadosController::class, 'opciones']);
      Route::get('listado', [ProyectosEvaluadosController::class, 'listado']);
      Route::get('verFicha', [ProyectosEvaluadosController::class, 'verFicha']);
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
  //  Perfil
  Route::prefix('perfil')->group(function () {
    Route::get('getData', [PerfilController::class, 'getData']);
    Route::put('updateData', [PerfilController::class, 'updateData']);
    Route::get('cdiEstado', [CdiController::class, 'cdiEstado']);
    Route::post('solicitarCDI', [CdiController::class, 'solicitarCDI']);

    Route::get('actividadesExtra', [CdiController::class, 'actividadesExtra']);
    Route::post('addActividad', [CdiController::class, 'addActividad']);
    Route::delete('deleteActividad', [CdiController::class, 'deleteActividad']);

    Route::get('actividadesExtraObs', [CdiController::class, 'actividadesExtraObs']);
    Route::post('addActividadObs', [CdiController::class, 'addActividadObs']);

    Route::post('actualizarSolicitud', [CdiController::class, 'actualizarSolicitud']);

    Route::get('observaciones', [CdiController::class, 'observaciones']);
  });

  //  Main dashboard
  Route::prefix('dashboard')->group(function () {
    Route::get('getData', [InvestigadorDashboardController::class, 'getData']);
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
      Route::get('lineasUnmsm', [ProyectoFEXController::class, 'lineasUnmsm']);
      Route::post('registrarPaso1', [ProyectoFEXController::class, 'registrarPaso1']);
      Route::post('registrarPaso2', [ProyectoFEXController::class, 'registrarPaso2']);

      Route::post('registrarPaso3', [ProyectoFEXController::class, 'registrarPaso3']);
      Route::put('updateDoc', [ProyectoFEXController::class, 'updateDoc']);
      Route::delete('deleteDoc', [ProyectoFEXController::class, 'deleteDoc']);

      Route::get('searchDocente', [ProyectoFEXController::class, 'searchDocente']);
      Route::post('agregarDocente', [ProyectoFEXController::class, 'agregarDocente']);
      Route::get('searchEstudiante', [ProyectoFEXController::class, 'searchEstudiante']);
      Route::post('agregarEstudiante', [ProyectoFEXController::class, 'agregarEstudiante']);
      Route::get('searchExterno', [ProyectoFEXController::class, 'searchExterno']);
      Route::post('agregarExterno', [ProyectoFEXController::class, 'agregarExterno']);
      Route::delete('eliminarMiembro', [ProyectoFEXController::class, 'eliminarMiembro']);

      Route::post('enviar', [ProyectoFEXController::class, 'enviar']);

      Route::get('datosPaso1', [ProyectoFEXController::class, 'datosPaso1']);
      Route::get('datosPaso2', [ProyectoFEXController::class, 'datosPaso2']);
      Route::get('datosPaso3', [ProyectoFEXController::class, 'datosPaso3']);
      Route::get('datosPaso4', [ProyectoFEXController::class, 'datosPaso4']);
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
    //  Deudas
    Route::prefix('deudas')->group(function () {
      Route::get('listado', [DeudaController::class, 'listado']);
    });

    Route::get('detalleProyecto', [ProyectoDetalleController::class, 'detalleProyecto']);
    Route::get('reportePresupuesto', [ProyectoDetalleController::class, 'reportePresupuesto']);
    Route::get('reporteConFin', [ProyectoDetalleController::class, 'reporteConFin']);
    Route::get('reporteSinFin', [ProyectoDetalleController::class, 'reporteSinFin']);
  });

  //  Publicaciones
  Route::prefix('publicaciones')->group(function () {

    //  Artículos en revistas de investigación
    Route::prefix('articulos')->group(function () {
      Route::get('listado', [ArticulosController::class, 'listado']);
      Route::post('registrarPaso1', [ArticulosController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [ArticulosController::class, 'datosPaso1']);
      Route::get('validarPublicacion', [ArticulosController::class, 'validarPublicacion']);
    });

    //  Libros
    Route::prefix('libros')->group(function () {
      Route::get('listado', [LibrosController::class, 'listado']);
      Route::post('registrarPaso1', [LibrosController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [LibrosController::class, 'datosPaso1']);

      Route::get('searchTitulo', [LibrosController::class, 'searchTitulo']);
      Route::get('searchIsbn', [LibrosController::class, 'searchIsbn']);
    });

    //  Capítulos de libros
    Route::prefix('capitulos')->group(function () {
      Route::get('listado', [CapitulosLibrosController::class, 'listado']);
      Route::post('registrarPaso1', [CapitulosLibrosController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [CapitulosLibrosController::class, 'datosPaso1']);

      Route::get('searchTitulo', [CapitulosLibrosController::class, 'searchTitulo']);
    });

    //  Participación en eventos
    Route::prefix('eventos')->group(function () {
      Route::get('listado', [EventoController::class, 'listado']);
      Route::post('registrarPaso1', [EventoController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [EventoController::class, 'datosPaso1']);

      Route::get('searchTitulo', [EventoController::class, 'searchTitulo']);
    });

    //  Tesis propias
    Route::prefix('tesisPropias')->group(function () {
      Route::get('listado', [TesisPropiasController::class, 'listado']);
      Route::post('registrarPaso1', [TesisPropiasController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [TesisPropiasController::class, 'datosPaso1']);
    });

    //  Tesis propias
    Route::prefix('tesisAsesoria')->group(function () {
      Route::get('listado', [TesisAsesoriaController::class, 'listado']);
      Route::post('registrarPaso1', [TesisAsesoriaController::class, 'registrarPaso1']);
      Route::get('datosPaso1', [TesisAsesoriaController::class, 'datosPaso1']);
    });

    //  Propiedad intelectual
    Route::prefix('propiedadInt')->group(function () {
      Route::get('listado', [PropiedadIntelectualController::class, 'listado']);
      Route::get('verificar1', [PropiedadIntelectualController::class, 'verificar1']);
      Route::post('registrar1', [PropiedadIntelectualController::class, 'registrar1']);

      Route::get('verificar2', [PropiedadIntelectualController::class, 'verificar2']);
      Route::post('addTitular', [PropiedadIntelectualController::class, 'addTitular']);
      Route::delete('deleteTitular', [PropiedadIntelectualController::class, 'deleteTitular']);

      Route::get('verificar3', [PropiedadIntelectualController::class, 'verificar3']);
      Route::post('addAutor', [PropiedadIntelectualController::class, 'addAutor']);
      Route::delete('deleteAutor', [PropiedadIntelectualController::class, 'deleteAutor']);

      Route::get('verificar4', [PropiedadIntelectualController::class, 'verificar4']);
      Route::post('registrar4', [PropiedadIntelectualController::class, 'registrar4']);
    });

    //  Utils
    Route::prefix('utils')->group(function () {
      Route::get('observacion', [PublicacionesUtilsController::class, 'observacion']);

      //  ELiminar
      Route::delete('eliminarPublicacion', [PublicacionesUtilsController::class, 'eliminarPublicacion']);

      //  Solicitar inclusión
      Route::get('listadoTitulos', [PublicacionesUtilsController::class, 'listadoTitulos']);
      Route::get('listadoDois', [PublicacionesUtilsController::class, 'listadoDois']);
      Route::get('listadoRevistas', [PublicacionesUtilsController::class, 'listadoRevistas']);
      Route::get('infoPublicacion', [PublicacionesUtilsController::class, 'infoPublicacion']);
      //  Data
      Route::get('listadoRevistasIndexadas', [PublicacionesUtilsController::class, 'listadoRevistasIndexadas']);
      Route::get('getPaises', [PublicacionesUtilsController::class, 'getPaises']);
      //  Paso 2
      Route::get('proyectos_asociados', [PublicacionesUtilsController::class, 'proyectos_asociados']);
      Route::get('proyectos_registrados', [PublicacionesUtilsController::class, 'proyectos_registrados']);
      Route::post('agregarProyecto', [PublicacionesUtilsController::class, 'agregarProyecto']);
      Route::delete('eliminarProyecto', [PublicacionesUtilsController::class, 'eliminarProyecto']);
      //  Paso 3
      Route::get('listarAutores', [PublicacionesUtilsController::class, 'listarAutores']);
      Route::get('searchDocenteRegistrado', [PublicacionesUtilsController::class, 'searchDocenteRegistrado']);
      Route::get('searchEstudianteRegistrado', [PublicacionesUtilsController::class, 'searchEstudianteRegistrado']);
      Route::get('searchExternoRegistrado', [PublicacionesUtilsController::class, 'searchExternoRegistrado']);
      Route::post('agregarAutor', [PublicacionesUtilsController::class, 'agregarAutor']);
      Route::put('editarAutor', [PublicacionesUtilsController::class, 'editarAutor']);
      Route::delete('eliminarAutor', [PublicacionesUtilsController::class, 'eliminarAutor']);
      //  Paso 4
      Route::post('enviarPublicacion', [PublicacionesUtilsController::class, 'enviarPublicacion']);
      Route::get('reporte', [PublicacionesUtilsController::class, 'reporte']);
    });
  });

  //  Grupo
  Route::prefix('grupo')->group(function () {
    //  Solicitar
    Route::prefix('solicitar')->group(function () {
      Route::get('verificar1', [InvestigadorGrupoController::class, 'verificar1']);
      Route::get('verificar2', [InvestigadorGrupoController::class, 'verificar2']);
      Route::get('verificar3', [InvestigadorGrupoController::class, 'verificar3']);
      Route::get('verificar4', [InvestigadorGrupoController::class, 'verificar4']);
      Route::get('verificar5', [InvestigadorGrupoController::class, 'verificar5']);
      Route::get('verificar6', [InvestigadorGrupoController::class, 'verificar6']);
      Route::get('verificar7', [InvestigadorGrupoController::class, 'verificar7']);
      Route::get('verificar8', [InvestigadorGrupoController::class, 'verificar8']);
      Route::get('verificar9', [InvestigadorGrupoController::class, 'verificar9']);

      Route::get('reporteSolicitud', [InvestigadorGrupoController::class, 'reporteSolicitud']);

      Route::post('registrar1', [InvestigadorGrupoController::class, 'registrar1']);
      Route::post('registrar2', [InvestigadorGrupoController::class, 'registrar2']);
      Route::post('registrar4', [InvestigadorGrupoController::class, 'registrar4']);
      Route::post('registrar7', [InvestigadorGrupoController::class, 'registrar7']);
      Route::post('registrar8', [InvestigadorGrupoController::class, 'registrar8']);
      Route::post('registrar9', [InvestigadorGrupoController::class, 'registrar9']);

      Route::get('searchDocenteRrhh', [InvestigadorGrupoController::class, 'searchDocenteRrhh']);
      Route::get('searchEstudiante', [InvestigadorGrupoController::class, 'searchEstudiante']);
      Route::get('searchEgresado', [InvestigadorGrupoController::class, 'searchEgresado']);

      Route::get('incluirMiembroData', [InvestigadorGrupoController::class, 'incluirMiembroData']);
      Route::post('agregarMiembro', [InvestigadorGrupoController::class, 'agregarMiembro']);
      Route::delete('eliminarMiembro', [InvestigadorGrupoController::class, 'eliminarMiembro']);
      Route::get('getPaises', [InvestigadorGrupoController::class, 'getPaises']);

      Route::post('agregarLinea', [InvestigadorGrupoController::class, 'agregarLinea']);
      Route::delete('eliminarLinea', [InvestigadorGrupoController::class, 'eliminarLinea']);

      Route::get('searchLaboratorio', [InvestigadorGrupoController::class, 'searchLaboratorio']);
      Route::post('agregarLaboratorio', [InvestigadorGrupoController::class, 'agregarLaboratorio']);
      Route::delete('eliminarLaboratorio', [InvestigadorGrupoController::class, 'eliminarLaboratorio']);
    });

    //  Grupos
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

    Route::get('reporteGrupo', [InvestigadorGrupoController::class, 'reporteGrupo']);
  });

  //  Convocatorias
  Route::prefix('convocatorias')->group(function () {

    Route::prefix('pinvpos')->group(function () {
      Route::get('verificar', [PinvposController::class, 'verificar']);
      Route::get('verificar2', [PinvposController::class, 'verificar2']);
      Route::get('verificar3', [PinvposController::class, 'verificar3']);
      Route::get('verificar4', [PinvposController::class, 'verificar4']);
      Route::get('verificar5', [PinvposController::class, 'verificar5']);

      Route::post('registrar1', [PinvposController::class, 'registrar1']);
      Route::post('registrar3', [PinvposController::class, 'registrar3']);
      Route::post('registrar5', [PinvposController::class, 'registrar5']);
      Route::get('reporte', [PinvposController::class, 'reporte']);
      Route::post('enviar', [PinvposController::class, 'enviar']);

      Route::post('agregarActividad', [PinvposController::class, 'agregarActividad']);
      Route::put('actualizarActividad', [PinvposController::class, 'actualizarActividad']);
      Route::delete('eliminarActividad', [PinvposController::class, 'eliminarActividad']);

      Route::post('agregarPartida', [PinvposController::class, 'agregarPartida']);
      Route::put('actualizarPartida', [PinvposController::class, 'actualizarPartida']);
      Route::delete('eliminarPartida', [PinvposController::class, 'eliminarPartida']);
    });

    Route::prefix('psinfinv')->group(function () {
      Route::get('verificar', [PsinfinvController::class, 'verificar']);

      Route::get('verificar1', [PsinfinvController::class, 'verificar1']);
      Route::post('registrar1', [PsinfinvController::class, 'registrar1']);

      Route::get('verificar2', [PsinfinvController::class, 'verificar2']);
      Route::post('registrar2', [PsinfinvController::class, 'registrar2']);

      Route::get('verificar3', [PsinfinvController::class, 'verificar3']);

      Route::get('verificar4', [PsinfinvController::class, 'verificar4']);
      Route::get('listadoGrupoDocente', [PsinfinvController::class, 'listadoGrupoDocente']);
      Route::get('listadoGrupoExterno', [PsinfinvController::class, 'listadoGrupoExterno']);
      Route::get('listadoGrupoEstudiante', [PsinfinvController::class, 'listadoGrupoEstudiante']);
      Route::post('agregarIntegrante', [PsinfinvController::class, 'agregarIntegrante']);
      Route::delete('eliminarIntegrante', [PsinfinvController::class, 'eliminarIntegrante']);

      Route::get('verificar5', [PsinfinvController::class, 'verificar5']);
      Route::post('addActividad', [PsinfinvController::class, 'addActividad']);
      Route::delete('eliminarActividad', [PsinfinvController::class, 'eliminarActividad']);
      Route::put('editActividad', [PsinfinvController::class, 'editActividad']);

      Route::get('verificar6', [PsinfinvController::class, 'verificar6']);
      Route::get('reporte', [PsinfinvController::class, 'reporte']);
      Route::post('enviar', [PsinfinvController::class, 'enviar']);
    });

    Route::prefix('psinfipu')->group(function () {
      Route::get('listado', [PsinfipuController::class, 'listado']);

      Route::get('verificar', [PsinfipuController::class, 'verificar']);

      Route::get('verificar1', [PsinfipuController::class, 'verificar1']);
      Route::post('registrar1', [PsinfipuController::class, 'registrar1']);

      Route::get('verificar2', [PsinfipuController::class, 'verificar2']);
      Route::post('registrar2', [PsinfipuController::class, 'registrar2']);
      Route::delete('eliminarArchivo', [PsinfipuController::class, 'eliminarArchivo']);

      Route::get('verificar3', [PsinfipuController::class, 'verificar3']);

      Route::get('verificar4', [PsinfipuController::class, 'verificar4']);
      Route::get('listadoGrupoMiembro', [PsinfipuController::class, 'listadoGrupoMiembro']);
      Route::post('agregarIntegrante', [PsinfipuController::class, 'agregarIntegrante']);
      Route::delete('eliminarIntegrante', [PsinfipuController::class, 'eliminarIntegrante']);

      Route::get('verificar5', [PsinfipuController::class, 'verificar5']);
      Route::post('addActividad', [PsinfipuController::class, 'addActividad']);
      Route::delete('eliminarActividad', [PsinfipuController::class, 'eliminarActividad']);
      Route::put('editActividad', [PsinfipuController::class, 'editActividad']);

      Route::get('verificar6', [PsinfipuController::class, 'verificar6']);
      Route::get('reporte', [PsinfipuController::class, 'reporte']);
      Route::post('enviar', [PsinfipuController::class, 'enviar']);
    });

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
      Route::get('movimientosTransferencia', [Informe_economicoController::class, 'movimientosTransferencia']);
      Route::get('partidasTransferencias', [Informe_economicoController::class, 'partidasTransferencias']);
      Route::post('addTransferenciaTemporal', [Informe_economicoController::class, 'addTransferenciaTemporal']);
      Route::post('solicitarTransferencia', [Informe_economicoController::class, 'solicitarTransferencia']);

      Route::get('reportePresupuesto', [Informe_economicoController::class, 'reportePresupuesto']);
      Route::get('detalleGasto', [Informe_economicoController::class, 'detalleGasto']);

      Route::post('enviarInforme', [Informe_economicoController::class, 'enviarInforme']);
      Route::get('reporteInforme', [Informe_economicoController::class, 'reporteInforme']);
    });

    Route::prefix('informe_academico')->group(function () {
      Route::get('listadoPendientes', [Informe_academicoController::class, 'listadoPendientes']);
      Route::get('listadoAceptados', [Informe_academicoController::class, 'listadoAceptados']);
      Route::get('verInforme', [Informe_academicoController::class, 'verInforme']);

      Route::get('getData', [InformeUtilsController::class, 'getData']);
      Route::post('sendData', [InformeUtilsController::class, 'sendData']);
      Route::put('presentar', [InformeUtilsController::class, 'presentar']);
      Route::post('loadActividad', [InformeUtilsController::class, 'loadActividad']);

      Route::get('reporte', [InformeUtilsController::class, 'reporte']);
    });
  });

  //  Orcid
  Route::prefix('orcid')->group(function () {
    Route::post('obtenerTokens', [OrcidController::class, 'obtenerTokens']);
  });
});

Route::prefix('evaluador')->middleware('checkRole:Usuario_evaluador')->group(function () {
  //  Evaluaciones
  Route::prefix('evaluaciones')->group(function () {
    Route::get('listado', [EvaluadorProyectosController::class, 'listado']);
    Route::get('criteriosEvaluacion', [EvaluadorProyectosController::class, 'criteriosEvaluacion']);
    Route::put('updateItem', [EvaluadorProyectosController::class, 'updateItem']);
    Route::put('preFinalizarEvaluacion', [EvaluadorProyectosController::class, 'preFinalizarEvaluacion']);
    Route::put('finalizarEvaluacion', [EvaluadorProyectosController::class, 'finalizarEvaluacion']);
    Route::get('fichaEvaluacion', [EvaluadorProyectosController::class, 'fichaEvaluacion']);
    Route::post('cargarFicha', [EvaluadorProyectosController::class, 'cargarFicha']);
    Route::get('visualizarProyecto', [EvaluadorProyectosController::class, 'visualizarProyecto']);
    Route::get('reportePasado', [ProyectosGrupoController::class, 'reporte']);
  });
});

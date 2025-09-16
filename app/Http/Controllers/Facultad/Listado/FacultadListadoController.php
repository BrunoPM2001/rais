<?php

namespace App\Http\Controllers\Facultad\Listado;

use App\Exports\Admin\FromDataExport;
use App\Exports\Facultad\DeudasExport;
use App\Exports\Facultad\PublicacionesExport;
use App\Exports\Facultad\DocenteInvestigadorExport;
use App\Exports\Facultad\GrupoIntegrantesExport;
use App\Exports\Facultad\InvestigadoresExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FacultadListadoController extends Controller {

  public function facultadId(Request $request) {
    $usuarioFacultadId = $request->attributes->get('token_decoded')->id;

    $facultadId = DB::table('Usuario_facultad')
      ->select('facultad_id')
      ->where('id', $usuarioFacultadId)
      ->first();

    return $facultadId ? $facultadId->facultad_id : null;
  }

  public function index(Request $request) {

    $facultadId = $this->facultadId($request);
    //  Métricas 

    $totalGrupos = $this->totalGrupos($facultadId);
    $totalConcon      = $this->total(['CON-CON'], [], $facultadId, 'PROYECTO_H');
    $totalPconfigi    = $this->total(['PCONFIGI'], [], $facultadId, 'PROYECTO');
    $totalSinsin      = $this->total(['SIN-SIN'], [], $facultadId, 'PROYECTO_H');
    $totalPsinfinv    = $this->total(['PSINFINV'], [], $facultadId, 'PROYECTO');
    $totalPsinfipu    = $this->total(['PSINFIPU'], [], $facultadId, 'PROYECTO');
    $totalSincon      = $this->total(['SIN-CON'], [], $facultadId, 'PROYECTO_H');
    $totalFex         = $this->total(['FEX'], [], $facultadId, 'PROYECTO');
    $totalPublicacion = $this->total(['PUBLICACION'], [], $facultadId, 'PROYECTO_H');
    $totalTaller      = $this->total(['TALLER'], [], $facultadId, 'PROYECTO_H');

    $totalPconfiginv   = $this->total(['PCONFIGI-INV'], [], $facultadId, 'PROYECTO');
    $totalPinvpos     = $this->total(['PINVPOS'], [], $facultadId, 'PROYECTO');
    $totalTesis       = $this->total(['Tesis'], [], $facultadId, 'PROYECTO_H');
    $totalPtpgrado    = $this->total(['PTPGRADO'], [], $facultadId, 'PROYECTO');
    $totalPtpdocto    = $this->total(['PTPDOCTO'], [], $facultadId, 'PROYECTO');
    $totalPtpmaest    = $this->total(['PTPMAEST'], [], $facultadId, 'PROYECTO');
    $totalECI         = $this->total(['ECI'], [], $facultadId, 'PROYECTO');
    $totalPevento     = $this->total(['PEVENTO'], [], $facultadId, 'PROYECTO');
    $totalGrupo       = $this->total(['Grupo'], [], $facultadId, 'PROYECTO_H');
    $totalMulti       = $this->total(['MULTI'], [], $facultadId, 'PROYECTO_H');
    $totalPmulti      = $this->total(['PMULTI'], [], $facultadId, 'PROYECTO');

    $totalConFinanciamiento = $totalConcon + $totalPconfigi + $totalPconfiginv;
    $totalSinFinanciamiento = $totalSinsin + $totalPsinfinv + $totalPsinfipu;
    $totalFinanciamientoExterno = $totalFex + $totalSincon;
    $totalAsesoriaTesis = $totalTesis + $totalPtpgrado + $totalPtpdocto + $totalPtpmaest;
    $totalTalleres = $totalTaller + $totalPevento;
    $totalEvento  = $totalPevento;
    $totalPublicaciones = $totalPublicacion;
    $totalGrupoEstudio = $totalGrupo;
    $totalMultidisciplinarios = $totalMulti + $totalPmulti;
    $totalEquipamiento = $totalECI;

    $totalDeudores = $this->totalDeudores($facultadId);

    $publicaciones = $this->totalPublicaciones($facultadId);

    $totalProyectos = $this->totalProyecto($facultadId);

    $totalProyectosHistoricos = $this->totalProyectosHistoricos($facultadId);


    return [
      'metricas' => [
        'grupos' => $totalGrupos,
        'totalConFinanciamiento' => $totalConFinanciamiento,
        'totalSinFinanciamiento' => $totalSinFinanciamiento,
        'totalFinanciamientoExterno' => $totalFinanciamientoExterno,
        'totalAsesoriaTesis' => $totalAsesoriaTesis,
        'totalTalleres' => $totalTalleres,
        'totalEvento' => $totalEvento,
        'totalPublicaciones' => $totalPublicaciones,
        'totalGrupoEstudio' => $totalGrupoEstudio,
        'totalMultidisciplinarios' => $totalMultidisciplinarios,
        'totalEquipamiento' => $totalEquipamiento,
        'totalDeudores' => $totalDeudores,
        'facultad' => $facultadId

      ],
      'publicaciones' => $publicaciones,
      'proyectos' => $totalProyectos,
      'proyectos_historicos' => $totalProyectosHistoricos
    ];
  }

  public function searchInvestigadorFacultad(Request $request) {
    $facultadId = $this->facultadId($request);

    $docentes = DB::table('Usuario_investigador AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.investigador_id', '=', 'a.id')
      ->select(
        DB::raw("
                    CONCAT(
                        TRIM(a.codigo), ' | ', 
                        a.doc_numero, ' | ', 
                        a.apellido1, ' ', a.apellido2, ', ', a.nombres, ' | ', 
                        COALESCE(a.tipo, CONCAT(a.tipo_investigador, ' - ', a.tipo_investigador_estado))
                    ) AS value
                "),
        'a.id AS investigador_id',
        'a.id',
        'a.codigo',
        'a.doc_numero',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.tipo',
        'a.tipo_investigador',
        'a.tipo_investigador_estado',
        DB::raw("COUNT(b.id) AS publicaciones")
      )
      ->where('a.facultad_id', $facultadId)
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.id')
      ->limit(10)
      ->get();

    return $docentes;
  }

  public function datosPublicaciones(Request $request) {

    $facultadId = $this->facultadId($request);

    $periodo = DB::table('Publicacion AS p')
      ->join('Publicacion_autor AS pa', 'pa.publicacion_id', '=', 'p.id')
      ->join('Usuario_investigador AS i', 'i.id', '=', 'pa.investigador_id')
      ->selectRaw('YEAR(p.fecha_publicacion) AS periodo')
      ->where('i.facultad_id', $facultadId)
      ->distinct() // Evita años repetidos
      ->orderBy('periodo', 'desc')
      ->get();

    $tipoPublicacion = DB::table('Publicacion AS p')
      ->join('Publicacion_categoria AS pcat', 'pcat.id', '=', 'p.categoria_id')
      ->join('Publicacion_autor AS pa', 'pa.publicacion_id', '=', 'p.id')
      ->join('Usuario_investigador AS i', 'i.id', '=', 'pa.investigador_id')
      ->select('pcat.tipo')
      ->distinct() // Evita tipos de publicación repetidos
      ->get();

    $estado = DB::table('Publicacion AS p')
      ->join('Publicacion_autor AS pa', 'pa.publicacion_id', '=', 'p.id')
      ->join('Usuario_investigador AS i', 'i.id', '=', 'pa.investigador_id')
      ->select('p.estado')
      ->where('i.facultad_id', $facultadId)
      ->distinct() // Evita estados repetidos
      ->get();



    return [
      'periodo' => $periodo,
      'tipoPublicacion' => $tipoPublicacion,
      'estado' => $estado
    ];
  }

  public function total(array $tipo = [], array $group = [], $facultad = null, $tipoTabla = null) {
    // Determina la tabla y los nombres de campos en función del valor de $tipoTabla
    if ($tipoTabla === 'PROYECTO_H') {
      $tabla = 'Proyecto_H as t1';
      $tipoCampo = 't1.tipo';
      $estadoCampo = 't1.status';
    } else {
      $tabla = 'Proyecto as t1';
      $tipoCampo = 't1.tipo_proyecto';
      $estadoCampo = 't1.estado';
    }

    // Construye la consulta base
    $total = DB::table($tabla)
      ->selectRaw('COUNT(*) as total, ' . $tipoCampo . ' as tipo, YEAR(t1.fecha_inicio) as year')
      ->leftJoin('Instituto as t2', 't2.id', '=', 't1.instituto_id')
      ->leftJoin('Facultad as t3', 't3.id', '=', 't2.facultad_id')
      ->where('t1.excluido', 0)
      ->where($estadoCampo, 1);

    // Filtrar por tipo si hay valores en el array $tipo
    if (!empty($tipo)) {
      $total->whereIn($tipoCampo, $tipo);
    }

    // Filtrar por facultad si $facultad no es nulo
    if ($facultad) {
      $total->where('t1.facultad_id', $facultad);
    }

    // Agrupación dinámica
    if (!empty($group)) {
      $total->groupBy($group);
    } else {
      $total->groupBy($tipoCampo);
    }

    // Ejecutar la consulta
    return $total->count();
  }

  public function totalGrupos($facultadId = null) {
    $grupos = DB::table('Grupo')
      ->where('estado', '=', 4)
      ->where('facultad_id', $facultadId)
      ->count();

    return $grupos;
  }

  public function totalPublicaciones($facultadId = null) {

    $tipoPublicacion = DB::table('Publicacion AS p')
      ->leftJoin('Publicacion_categoria AS pcat', 'pcat.id', '=', 'p.categoria_id')
      ->select('p.tipo_publicacion')
      ->whereRaw('YEAR(p.fecha_inscripcion) > 2019')
      ->whereNotNull('p.tipo_publicacion')
      ->groupBy('p.tipo_publicacion')
      ->get();

    // Construimos los conteos condicionales para cada tipo
    $countExp = [];
    foreach ($tipoPublicacion as $tipo) {
      $countExp[] = DB::raw('COUNT(IF(p.tipo_publicacion = "' . $tipo->tipo_publicacion . '", 1, NULL)) AS `' . $tipo->tipo_publicacion . '`');
    }

    // Consulta principal
    $publicaciones = DB::table('Publicacion AS p')
      ->leftJoin('Publicacion_categoria AS pcat', 'pcat.id', '=', 'p.categoria_id')
      ->leftJoin('Publicacion_autor AS pautor', 'pautor.publicacion_id', '=', 'p.id')
      ->leftJoin('Usuario_investigador AS i', 'i.id', '=', 'pautor.investigador_id')
      ->select(
        DB::raw('YEAR(p.fecha_inscripcion) AS periodo'),
        ...$countExp // Aquí pasamos los conteos generados
      )
      ->whereRaw('YEAR(p.fecha_inscripcion) > 2019')
      ->where('p.validado', 1)
      ->where('i.facultad_id', $facultadId)
      ->groupByRaw('YEAR(p.fecha_inscripcion)')
      ->get();

    return ['tipos' => $tipoPublicacion, 'cuenta' => $publicaciones];
  }

  public function totalProyecto($facultadId = null) {
    $proyectos = DB::table('Proyecto')
      ->select(
        'tipo_proyecto AS title',
        DB::raw('COUNT(*) AS value')
      )
      ->where('periodo', '=', 2025)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->where('facultad_id', $facultadId)
      ->groupBy('tipo_proyecto')
      ->get();

    return $proyectos;
  }

  public function totalProyectosHistoricos($facultadId = null) {

    //  Proyectos históricos
    $countExp = [];
    $tipoProyecto = DB::table('Proyecto')
      ->select(
        'tipo_proyecto'
      )
      ->where('periodo', '>', 2016)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->groupBy('tipo_proyecto')
      ->get();
    foreach ($tipoProyecto as $tipo) {
      $countExp[] = DB::raw('COUNT(IF(tipo_proyecto = "' . $tipo->tipo_proyecto . '", 1, NULL)) AS "' . $tipo->tipo_proyecto . '"');
    }
    $cuenta =  DB::table('Proyecto')
      ->select(
        'periodo',
        ...$countExp,
      )
      ->where('periodo', '>', 2016)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->where('facultad_id', $facultadId)
      ->groupBy('periodo')
      ->orderBy('periodo')
      ->get();

    return [
      'tipos' => $tipoProyecto,
      'cuenta' => $cuenta
    ];
  }

  public function totalDeudores($facultadId = null) {
    $deudores = DB::table('view_deudores as t1')
      ->join('Usuario_investigador as t2', 't1.investigador_id', '=', 't2.id')
      ->where('facultad_id', $facultadId)
      ->whereIn('t1.tipo', [1, 2, 3])
      ->count();

    return $deudores;
  }

  public function ListadoInvestigadores(Request $request) {
    $fechaInicio = date('Y') - 7;
    $fechaFin = date('Y') - 1;
    $facultadId = $this->facultadId($request);

    $investigadores = DB::table('Usuario_investigador as t1')
      ->select([
        't1.id',
        't1.apellido1 as apellido_paterno',
        't1.apellido2 as apellido_materno',
        't1.nombres',
        't1.doc_tipo as tipo_documento',
        't1.doc_numero',
        't1.codigo',
        't1.tipo',
        't1.fecha_nac as fecha_nacimiento',
        't1.sexo',
        't1.renacyt',
        't1.renacyt_nivel',
        't1.codigo_orcid',
        DB::raw('CONCAT(t1.apellido1," ",t1.apellido2, " ", t1.nombres ) as docente'),
        DB::raw('IF(YEAR(t1.fecha_nac) > 0, (YEAR(CURDATE()) - YEAR(t1.fecha_nac)), NULL) as edad'),
        DB::raw('COALESCE(SUM(pub.puntaje), 0) + COALESCE(SUM(pat.puntaje), 0) as puntaje_total'),
        't2.nombre as facultad'
      ])
      ->leftJoin('Facultad as t2', 't1.facultad_id', '=', 't2.id')

      // Subconsulta para calcular el puntaje de publicaciones
      ->leftJoinSub(
        DB::table('Publicacion_autor as pautor')
          ->select('pautor.investigador_id', DB::raw('SUM(pautor.puntaje) as puntaje'))
          ->join('Publicacion as pb', 'pautor.publicacion_id', '=', 'pb.id')
          ->where('pb.validado', 1)
          ->whereBetween(DB::raw('YEAR(pb.fecha_publicacion)'), [$fechaInicio, $fechaFin])
          ->groupBy('pautor.investigador_id'),
        'pub',
        'pub.investigador_id',
        '=',
        't1.id'
      )

      // Subconsulta para calcular el puntaje de patentes
      ->leftJoinSub(
        DB::table('Patente_autor as pautor')
          ->select('pautor.investigador_id', DB::raw('SUM(pautor.puntaje) as puntaje'))
          ->join('Patente as pt', 'pautor.patente_id', '=', 'pt.id')
          ->whereBetween(DB::raw('YEAR(pt.created_at)'), [$fechaInicio, $fechaFin])
          ->groupBy('pautor.investigador_id'),
        'pat',
        'pat.investigador_id',
        '=',
        't1.id'
      )

      ->where('t1.facultad_id', $facultadId)
      ->where('t1.tipo', 'not like', 'Sin categoria%')
      ->where('t1.tipo', 'not like', '%externo%')
      ->where('t1.doc_numero', '!=', '')

      // Agrupación para evitar un único resultado
      ->groupBy('t1.id', 't2.nombre')

      // Ordenar por apellido y nombres
      ->orderBy('t1.apellido1')
      ->orderBy('t1.apellido2')
      ->orderBy('t1.nombres')

      ->get();

    return $investigadores;
  }

  public function excelInvestigadores(Request $request) {
    $facultadId = $this->facultadId($request);
    $export = new InvestigadoresExport($facultadId);

    return Excel::download($export, 'investigadores.xlsx');
  }

  public function pdfInvestigadores(Request $request) {
    $fechaInicio = date('Y') - 7;
    $fechaFin = date('Y') - 1;
    $facultadId = $this->facultadId($request);

    $publicacion_query = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'a.publicacion_id', '=', 'b.id')
      ->select([
        'a.investigador_id',
        DB::raw("COUNT(b.id) AS publicaciones"),
        DB::raw("SUM(a.puntaje) AS puntaje")
      ])
      ->where('b.validado', 1)
      ->whereBetween(DB::raw('YEAR(b.fecha_publicacion)'), [$fechaInicio, $fechaFin])
      ->groupBy('a.investigador_id');

    $patente_query = DB::table('Patente_autor AS a')
      ->join('Patente AS b', 'a.patente_id', '=', 'b.id')
      ->select([
        'a.investigador_id',
        DB::raw("COUNT(b.id) AS publicaciones"),
        DB::raw('SUM(a.puntaje) AS puntaje')
      ])
      ->whereBetween(DB::raw('YEAR(b.created_at)'), [$fechaInicio, $fechaFin])
      ->groupBy('a.investigador_id');

    $investigadores = DB::table('Usuario_investigador AS a')
      ->leftJoinSub($publicacion_query, 'pub', 'pub.investigador_id', '=', 'a.id')
      ->leftJoinSub($patente_query, 'pat', 'pat.investigador_id', '=', 'a.id')
      ->select([
        'a.codigo',
        DB::raw("CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS nombres"),
        DB::raw("COALESCE(SUM(pub.publicaciones), 0) + COALESCE(SUM(pat.publicaciones), 0)  AS publicaciones"),
        DB::raw('COALESCE(SUM(pub.puntaje), 0) + COALESCE(SUM(pat.puntaje), 0) as puntaje'),
      ])
      ->where('a.facultad_id', '=', $facultadId)
      ->where('a.tipo', '=', 'DOCENTE PERMANENTE')
      ->groupBy('a.id')
      ->get();

    $facultad = DB::table('Facultad AS a')
      ->join('Area AS b', 'b.id', '=', 'a.area_id')
      ->select([
        'b.nombre AS area',
        'a.nombre AS facultad',
      ])
      ->where('a.id', '=', $facultadId)
      ->first();

    $pdf = Pdf::loadView('facultad.listado.investigador', ['lista' => $investigadores, 'facultad' => $facultad]);
    return $pdf->stream();
  }

  public function DocenteInvestigador(Request $request) {
    $facultadId = $this->facultadId($request);

    $docenteInvestigador = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'a.investigador_id', '=', 'b.id')
      ->select([
        'a.estado',
        'a.tipo_eval',
        DB::raw("DATE(a.fecha_constancia) AS fecha_constancia"),
        DB::raw("DATE(a.fecha_fin) AS fecha_fin"),
        'a.tipo_docente',
        'a.orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_tipo',
        'b.doc_numero',
        'b.telefono_movil',
        'b.email3',
      ])
      ->where('b.facultad_id', '=', $facultadId)
      ->whereRaw('a.created_at = (SELECT MAX(_t1.created_at) FROM Eval_docente_investigador as _t1 WHERE _t1.investigador_id = a.investigador_id)')
      ->orderBy('b.apellido1')
      ->orderBy('b.apellido2')
      ->orderBy('b.nombres')
      ->get();

    return $docenteInvestigador;
  }

  public function excelDocentes(Request $request) {
    $facultadId = $this->facultadId($request);
    $export = new DocenteInvestigadorExport($facultadId);

    return Excel::download($export, 'docentes.xlsx');
  }

  public function ListadoProyectos(Request $request) {
    $facultadId = $this->facultadId($request);

    $proyectos_nuevos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('condicion', '=', 'Responsable');
      })
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        'a.periodo',
        'a.resolucion_rectoral',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inscripcion',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluacion'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecución'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluído'
        ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.facultad_id', '=', $facultadId);

    $proyectos = DB::table('Proyecto_H AS a')
      ->leftJoin('Proyecto_integrante_H AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('condicion', '=', 'Responsable');
      })
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.tipo AS tipo_proyecto',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.periodo',
        'a.resolucion AS resolucion_rectoral',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        DB::raw("DATE(a.fecha_inscripcion) AS fecha_inscripcion"),
        DB::raw("CASE(a.status)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluacion'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecución'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluído'
        ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.facultad_id', '=', $facultadId)
      ->union($proyectos_nuevos)
      ->get();

    return $proyectos;
  }

  public function ListadoProyectosGI(Request $request) {
    $facultadId = $this->facultadId($request);

    $proyectos = DB::table('view_proyecto_grupo')
      ->select([
        '*',
        DB::raw("CASE(estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 3 THEN 'En evaluacion'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'Sustentado'
            WHEN 9 THEN 'En ejecución'
            WHEN 10 THEN 'Ejecutado'
            WHEN 11 THEN 'Concluído'
          ELSE 'Sin estado' END AS estado"),

      ])
      ->where('proyecto_facultad_id', $facultadId)
      ->where('estado', '!=', -1)
      ->get();

    return $proyectos;
  }

  public function ListadoProyectosFEX(Request $request) {

    $facultadId = $this->facultadId($request);

    // Subquery for the 'responsable' field
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    // Subquery for 'Proyecto_descripcion' fields based on 'codigo' value
    $projectDescriptions = function ($code) {
      return DB::table('Proyecto_descripcion')
        ->select('proyecto_id', 'detalle')
        ->where('codigo', '=', $code);
    };

    // Main query with simplified joins
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($projectDescriptions('moneda_tipo'), 'moneda', 'moneda.proyecto_id', '=', 'a.id')
      ->leftJoinSub($projectDescriptions('participacion_ummsm'), 'p_unmsm', 'p_unmsm.proyecto_id', '=', 'a.id')
      ->leftJoinSub($projectDescriptions('fuente_financiadora'), 'fuente', 'fuente.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'res.responsable',
        'b.nombre AS facultad',
        'moneda.detalle AS moneda',
        'a.aporte_no_unmsm',
        'a.aporte_unmsm',
        'a.financiamiento_fuente_externa',
        'a.monto_asignado',
        'p_unmsm.detalle AS participacion_unmsm',
        'fuente.detalle AS fuente_fin',
        'a.periodo',
        DB::raw('DATE(a.created_at) AS registrado'),
        DB::raw('DATE(a.updated_at) AS actualizado'),
        DB::raw("CASE(a.estado)
                WHEN -1 THEN 'Eliminado'
                WHEN 0 THEN 'No aprobado'
                WHEN 1 THEN 'Aprobado'
                WHEN 3 THEN 'En evaluacion'
                WHEN 5 THEN 'Enviado'
                WHEN 6 THEN 'En proceso'
                WHEN 7 THEN 'Anulado'
                WHEN 8 THEN 'Sustentado'
                WHEN 9 THEN 'En ejecución'
                WHEN 10 THEN 'Ejecutado'
                WHEN 11 THEN 'Concluído'
              ELSE 'Sin estado' END AS estado")
      )
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->where('a.facultad_id', $facultadId)
      ->where('a.estado', '!=', -1)
      ->get();

    return $proyectos;
  }

  public function ListadoGrupos(Request $request) {
    $facultadId = $this->facultadId($request);

    $grupos = DB::table('Grupo as t1')
      ->select([
        't1.id',
        't1.tipo',
        't1.grupo_nombre',
        DB::raw('UPPER(t1.grupo_nombre_corto) AS grupo_nombre_corto'),
        DB::raw('UPPER(t1.grupo_categoria) AS grupo_categoria'),
        't1.observaciones',
        't1.resolucion_rectoral',
        DB::raw("CASE(t1.estado)
                    WHEN -2 THEN 'Disuelto'
                    WHEN -1 THEN 'Eliminado'
                    WHEN 1 THEN 'Reconocido'
                    WHEN 2 THEN 'Observado'
                    WHEN 4 THEN 'Registrado'
                    WHEN 5 THEN 'Enviado'
                    WHEN 6 THEN 'En proceso'
                    WHEN 12 THEN 'Reg. Observado'
                ELSE 'Sin estado' END AS estado"),
        't1.created_at',
        't1.updated_at',
        DB::raw('t1.id AS idx'),
        DB::raw("
            (
                SELECT CONCAT(_t2.apellido1, ' ', _t2.apellido2, ' ', _t2.nombres)
                FROM Grupo_integrante AS _t1
                JOIN Usuario_investigador AS _t2 ON _t1.investigador_id = _t2.id
                WHERE _t1.grupo_id = t1.id 
                  AND _t1.cargo IN ('Coordinador')
                LIMIT 1
            ) AS coordinador
        "),
        DB::raw("
            (
                SELECT COUNT(*)
                FROM Grupo_integrante AS _t1
                WHERE _t1.grupo_id = t1.id
            ) AS total_integrantes
        "),
        't2.nombre as facultad',
        't2.area_id'
      ])
      ->join('Facultad as t2', 't1.facultad_id', '=', 't2.id')
      ->where('t2.id', $facultadId)
      ->orderByDesc('t1.created_at')
      ->get();

    return $grupos;
  }

  public function pdfGrupo(Request $request) {
    $grupo = DB::table('Grupo')
      ->select([
        'grupo_nombre',
        'grupo_nombre_corto',
        'telefono',
        'anexo',
        'oficina',
        'direccion',
        'web',
        'email',
        'presentacion',
        'objetivos',
        'servicios',
        'infraestructura_ambientes',
        DB::raw("CASE 
            WHEN infraestructura_sgestion IS NULL THEN 'No'
            ELSE 'Sí'
          END AS anexo"),
        DB::raw("CASE (estado)
          WHEN -2 THEN 'Disuelto'
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 4 THEN 'Registrado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado")
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $integrantes = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'b.doc_numero',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        DB::raw("CASE 
          WHEN a.cargo IS NOT NULL THEN CONCAT(a.condicion, '(', a.cargo, ')')
          ELSE a.condicion
          END AS condicion"),
        'b.tipo',
        'c.nombre AS facultad'
      ])
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.nombre',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $laboratorios = DB::table('Grupo_infraestructura AS a')
      ->join('Laboratorio AS b', 'b.id', '=', 'a.laboratorio_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.laboratorio',
        'b.responsable',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.grupo.reporte_grupo', [
      'grupo' => $grupo,
      'integrantes' => $integrantes,
      'lineas' => $lineas,
      'laboratorios' => $laboratorios,
    ]);

    return $pdf->stream();
  }

  public function ListadoPublicaciones(Request $request) {
    $facultadId = $this->facultadId($request);
    $publicaciones = DB::table('Publicacion AS t1')
      ->select([
        't1.*',
        't1.id AS idx',
        DB::raw("YEAR(t1.fecha_publicacion) AS year_publicacion"),
        DB::raw("CASE 
          WHEN t1.tipo_publicacion = 'evento' THEN 'R. Evento Cientifico'
          WHEN t1.tipo_publicacion = 'articulo' THEN 'Artículo de Revista'
          WHEN t1.tipo_publicacion = 'capitulo' THEN 'Capítulo de Libro'
          WHEN t1.tipo_publicacion = 'libro' THEN 'Libro'
          WHEN t1.tipo_publicacion = 'tesis' THEN 'Tesis'
          ELSE t1.tipo_publicacion 
        END AS xtipo_publicacion"),
        DB::raw("CASE(t1.estado)                     
          WHEN 1 THEN 'Registrado'
          WHEN 2 THEN 'Observado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'No Registrado'
          WHEN 9 THEN 'Reg. Duplicado'
        ELSE 'Sin estado' END AS estado"),
        't2.investigador_id'
      ])
      ->leftJoin('Publicacion_autor AS t2', 't1.id', '=', 't2.publicacion_id')
      ->leftJoin('Usuario_investigador AS t3', 't3.id', '=', 't2.investigador_id')
      ->where('t3.facultad_id', $facultadId)
      ->where('t1.estado', '!=', '-1')
      ->whereNotIn('t1.id', function ($subquery) {
        $subquery->select('t4.id')
          ->from('Publicacion AS t4')
          ->where('t4.tipo_publicacion', 'tesis-asesoria')
          ->where('t4.source', 'cybertesis')
          ->where('t4.estado', '!=', 1);
      })
      ->groupBy('t1.id')
      ->orderByDesc('t1.fecha_inscripcion')
      ->get();

    return $publicaciones;
  }

  public function ListadoInformes(Request $request) {
    $facultadId = $this->facultadId($request);

    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $proyectos_nuevos = DB::table('Proyecto AS a')
      ->leftJoin('Informe_tecnico AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw('COUNT(b.id) AS cantidad_informes'),
        'res.responsable',
        'a.periodo',
        'a.fecha_inscripcion'
      )
      ->where('a.estado', '>', 0)
      ->where('a.facultad_id', '=', $facultadId)
      ->groupBy('a.id');

    $proyectos = DB::table('Proyecto_H AS a')
      ->leftJoin('Informe_tecnico_H AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->leftJoin('Proyecto_integrante_H AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.condicion', '=', 'Responsable');
      })
      ->leftJoin('Usuario_investigador AS e', 'e.id', '=', 'd.investigador_id')
      ->select(
        'a.id',
        'a.tipo AS tipo_proyecto',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        DB::raw('COUNT(b.id) AS cantidad_informes'),
        DB::raw("CONCAT(e.apellido1, ' ', e.apellido2, ', ', e.nombres) AS responsable"),
        'a.periodo',
        DB::raw("DATE(a.fecha_inscripcion) AS fecha_inscripcion"),
      )
      ->where('a.status', '>', 0)
      ->where('a.facultad_id', '=', $facultadId)
      ->groupBy('a.id')
      ->union($proyectos_nuevos)
      ->get();

    return $proyectos;
  }

  public function ListadoDeudas(Request $request) {
    $facultadId = $this->facultadId($request);

    $deudas = DB::table('view_deudores as t1')
      ->select(
        't2.id',
        DB::raw('CONCAT(t1.apellido1, " ", t1.apellido2, ", ", t1.nombres) as nombre_completo'),
        't1.categoria',
        't1.coddoc',
        't1.ptipo',
        't1.pcodigo',
        't1.condicion',
        't1.detalle',
        't1.periodo'

      )  // Selecciona todos los campos de la tabla
      ->join('Usuario_investigador as t2', 't1.investigador_id', '=', 't2.id')  // Usa el método 'join' en lugar de 'innerJoin'
      ->where('t2.facultad_id', $facultadId)  // Filtra por facultad
      ->get();  // Ejecuta la consulta y obtiene los resultados

    return $deudas;
  }

  public function pdfDeudas(Request $request) {
    $facultadId = $this->facultadId($request);

    $deudas = DB::table('view_deudores as t1')
      ->join('Usuario_investigador as t2', 't1.investigador_id', '=', 't2.id')
      ->select(
        't2.id',
        't1.coddoc',
        DB::raw('CONCAT(t1.apellido1, " ", t1.apellido2, ", ", t1.nombres) as nombres'),
        't1.ptipo',
        't1.pcodigo',
        't1.condicion',
        't1.categoria',
        't1.periodo'
      )
      ->where('t2.facultad_id', $facultadId)
      ->get();

    $facultad = DB::table('Facultad AS a')
      ->join('Area AS b', 'b.id', '=', 'a.area_id')
      ->select([
        'b.nombre AS area',
        'a.nombre AS facultad',
      ])
      ->where('a.id', '=', $facultadId)
      ->first();

    $pdf = Pdf::loadView('facultad.listado.deudores', ['lista' => $deudas, 'facultad' => $facultad]);
    $pdf->setPaper('A4', 'landscape');
    return $pdf->stream();
  }

  public function excel(Request $request) {

    $data = $request->all();

    $export = new FromDataExport($data);

    return Excel::download($export, 'proyectos.xlsx');
  }
}

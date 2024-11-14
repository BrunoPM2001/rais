<?php

namespace App\Http\Controllers\Facultad\Listado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
      ->where('periodo', '=', 2024)
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

  public function DocenteInvestigador(Request $request) {
    $facultadId = $this->facultadId($request);
    $docenteInvestigador = DB::table('Eval_docente_investigador as t1')
      ->select([
        '*',
        DB::raw('IF(t1.estado="APROBADO", DATE_FORMAT(t1.fecha_constancia, "%Y-%m-%d"), NULL) as fecha_constancia_const'),
        DB::raw('IF(t1.estado="APROBADO", DATE_FORMAT(t1.fecha_fin, "%Y-%m-%d"), NULL) as fecha_fin_const'),
        DB::raw('(
                CASE
                    WHEN t1.estado = "PENDIENTE" THEN "Pendiente"
                    WHEN t1.estado = "APROBADO" AND TIMESTAMPDIFF(YEAR, t1.fecha_constancia, NOW()) >= 2 THEN "No vigente"
                    WHEN t1.estado = "APROBADO" AND TIMESTAMPDIFF(YEAR, t1.fecha_constancia, NOW()) < 2 THEN "Vigente"
                    WHEN t1.estado = "ANULADO" THEN "Anulado"
                    WHEN t1.estado = "ENVIADO" THEN "Enviado"
                    WHEN t1.estado = "PROCESO" THEN "Observado"
                    WHEN t1.estado = "TRAMITE" THEN "En trámite"
                END
            ) as status_const')
      ])
      ->join('Usuario_investigador as t2', 't1.investigador_id', '=', 't2.id')
      ->leftJoin('Facultad as t3', 't2.facultad_id', '=', 't3.id')
      ->addSelect([
        't2.tipo',
        DB::raw('(SELECT orcid FROM token_investigador_orcid WHERE investigador_id = t1.investigador_id ORDER BY id DESC LIMIT 1) as orcid'),
        't2.apellido1',
        't2.apellido2',
        't2.nombres',
        't2.doc_tipo',
        't2.telefono_movil',
        't2.email3',
      ])
      ->where('t2.facultad_id', $facultadId)
      ->whereRaw('t1.created_at = (SELECT MAX(_t1.created_at) FROM eval_docente_investigador as _t1 WHERE _t1.investigador_id = t1.investigador_id)')
      ->orderBy('t2.apellido1')
      ->orderBy('t2.apellido2')
      ->orderBy('t2.nombres')
      ->get();

    return $docenteInvestigador;
  }

  public function ListadoProyectos(Request $request) {
    $facultadId = $this->facultadId($request);

    $proyectos = DB::table('view_facultad_proyecto')
      ->select([
        'proyecto_id',
        'codigo',
        'tipo',
        'periodo',
        'xtitulo',
        'responsable',
        'facultad',
        'nro_informe_p',
        DB::raw("CASE(deuda)
                WHEN 0 THEN 'Sin deuda'
                WHEN 1 THEN 'Deuda Académica'
                WHEN 2 THEN 'Deuda Económica'
                WHEN 3 THEN 'Deuda Académica y Económica'
              ELSE 'Subsanado' END AS deuda"),
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
        'fecha_inscripcion'

      ])
      ->where('facultad_id', $facultadId)
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

  public function ListadoPublicaciones(Request $request) {
    $facultadId = $this->facultadId($request);
    $publicaciones = DB::table('Publicacion AS t1')
      ->select([
        't1.*',
        't1.id AS idx',
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

    $informes = DB::table('view_informe_proyectos as t1')
      ->select(
        '*',
        DB::raw("CASE(estado)
            WHEN 0 THEN 'En proceso'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Presentado'
            WHEN 3 THEN 'Observado'
            ELSE 'No tiene informe'
          END AS estado")
      )
      ->join('Proyecto as t2', 't1.proyecto_id', '=', 't2.id', 'left')
      ->where('t1.facultad_id', $facultadId)
      ->orderBy('t1.fecha_inscripcion', 'desc')
      ->orderBy('t1.facultad_id', 'desc')
      ->get();

    return $informes;
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
}

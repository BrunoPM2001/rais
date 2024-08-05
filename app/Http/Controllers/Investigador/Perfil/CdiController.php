<?php

namespace App\Http\Controllers\Investigador\Perfil;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CdiController extends S3Controller {

  /**
   *  Estados  del cdi para el cliente
   *  0: No está registrado en RRHH
   *  1: No cumple con los prerrequisitos (Art. 7)
   *  2: Cumple con los prerrequisitos (No tiene constancia vigente ni ha enviado una solicitud)
   *  3: Tiene solicitud en curso
   *  4: Constancia emitida 
   */
  public function cdiEstado(Request $request) {

    $constancia = $this->constanciaCdi($request);
    if ($constancia) {
      return [
        'estado' => 4,
        'fecha_fin' => $constancia->fecha_fin,
        'url' => $constancia->url,
      ];
    }

    $solicitud = $this->estadoSolicitud($request);
    if ($solicitud["solicitud"] == 1) {
      return [
        'estado' => 3,
        'solicitud' => $solicitud
      ];
    }

    $rrhh = $this->rrhhCdi($request);
    if (!$rrhh) {
      return ['estado' => 0];
    }


    $preReq = $this->preCdi($request);
    if ($preReq == 0) {
      return ['estado' => 1];
    }

    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $req1 = DB::table('Usuario_investigador')
      ->select([
        'renacyt',
        'renacyt_nivel'
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $req2 = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.grupo_id')
          ->where('b.tipo', '=', 'grupo');
      })
      ->select([
        'b.grupo_nombre',
        'b.grupo_nombre_corto',
        'a.condicion',
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();


    //  Proyectos (a)
    $req3A = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'A. Proyectos de investigación con financiamiento Institucional' AS categoria"),
        DB::raw("'a' AS sub_categoria"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'b.condicion AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_proyecto', '!=', 'PFEX')
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    //  Proyectos fex (b)
    $req3B = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'B. Proyectos de investigación con financiamiento externo' AS categoria"),
        DB::raw("'a' AS sub_categoria"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'b.condicion AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    //  Asesorías (c)
    $req3C = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'C. Asesoría de trabajo de investigación o tesis' AS categoria"),
        DB::raw("'c' AS sub_categoria"),
        'a.tipo_publicacion AS tipo_proyecto',
        'a.codigo_registro AS codigo_proyecto',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'b.categoria AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_publicacion', '=', 'tesis-asesoria')
      ->where('b.categoria', '=', 'Asesor')
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->get();

    $req4 = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_index AS c', 'c.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_db_indexada AS d', 'd.id', '=', 'c.publicacion_db_indexada_id')
      ->select([
        'a.titulo',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'a.codigo_registro',
        DB::raw("GROUP_CONCAT(d.nombre SEPARATOR ', ') AS indexada"),
        'b.filiacion'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->groupBy('a.id')
      ->get();

    $req5 = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->select([
        'c.titulo',
        'c.periodo',
        'c.tipo_proyecto',
        'b.id AS id_deuda',
        'b.detalle'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNull('b.fecha_sub')
      ->get();

    $req6 = DB::table('Eval_declaracion_jurada AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'DECLARACION_JURADA');
      })
      ->select([
        DB::raw("CONCAT('/minio/declaracion-jurada/', b.key) AS url"),
        'a.fecha_inicio',
        'a.fecha_fin'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    $actividades_extra = $this->actividadesExtra($request);

    //  Result
    return [
      'estado' => 2,
      'rrhh' => $rrhh,
      'req1' => $req1,
      'req2' => $req2,
      'req3' => $req3A->merge($req3B)->merge($req3C),
      'req4' => $req4,
      'req5' => $req5,
      'req6' => $req6,
      'actividades_extra' => $actividades_extra
    ];
  }

  /**
   *  Inserción del archivo de la DJ
   */
  public function presentarDJ(Request $request) {
    $date = Carbon::now();
    $date_end = Carbon::now()->addMonths(3);
    $nameFile = "Constancia-dj_" . $date->format('Ymd-His') . "-" . Str::random(8) . ".pdf";

    $id = DB::table('Eval_declaracion_jurada')
      ->insertGetId([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'fecha_inicio' => $date,
        'fecha_fin' => $date_end,
        'created_at' => $date,
        'updated_at' => $date,
      ]);

    DB::table('File')
      ->insert([
        'tabla_id' => $id,
        'tabla' => 'Eval_docente_investigador',
        'bucket' => 'declaracion-jurada',
        'key' => $nameFile,
        'estado' => 20,
        'recurso' => 'DECLARACION_JURADA',
        'created_at' => $date,
        'updated_at' => $date,
      ]);

    $req = DB::table('Repo_rrhh AS a')
      ->join('Usuario_investigador AS b', 'b.codigo', '=', 'a.ser_cod_ant')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.ser_cod',
        'a.ser_ape_pat',
        'a.ser_ape_mat',
        'a.ser_nom',
        'c.nombre AS facultad',
        'a.des_dep_cesantes',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END AS categoria"),
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.ser_cat_act, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.ser_cat_act, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.ser_cat_act, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
      ])
      ->where('b.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    function obtenerFechaActual() {
      $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
      ];

      $fecha = new DateTime();
      $dia = $fecha->format('j');
      $mes = $meses[(int)$fecha->format('n')];
      $año = $fecha->format('Y');

      return "$dia de $mes de $año";
    }

    $fecha = obtenerFechaActual();

    $pdf = Pdf::loadView('investigador.perfil.dj', ['data' => $req, 'fecha' => $fecha]);
    $file = $pdf->output();

    $this->loadFile($file, 'declaracion-jurada', $nameFile);

    return [
      'fecha_inicio' => $date,
      'fecha_fin' => $date_end
    ];
  }

  /**
   *  Ver el estado de una solicitud en curso
   */
  public function estadoSolicitud(Request $request) {
    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $solicitud = DB::table('Eval_docente_investigador')
      ->select([
        'id',
        'd1',
        'd2',
        'd3',
        'd4',
        'd6',
        DB::raw("CASE 
            WHEN estado = 'ENVIADO' THEN 'Enviado'
            WHEN estado = 'TRAMITE' THEN 'En trámite'
            WHEN estado = 'CONSTANCIA' THEN 'Constancia'
            WHEN estado = 'NO_APROBADO' THEN 'No aprobado'
            WHEN estado = 'PROCESO' THEN 'Observado'
            WHEN estado = 'PENDIENTE' THEN 'Pendiente'
            ELSE ''
        END AS estado"),
        'estado_tecnico',
        'estado_real',
        'created_at'
      ])
      ->where('investigador_id',  '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where(function ($query) {
        $query->where('tipo_eval', '=', 'Solicitud')
          ->orWhere(function ($query) {
            $query->where('tipo_eval', '=', 'Constancia')
              ->where('estado', '=', 'PENDIENTE');
          });
      })
      ->orderByDesc('created_at')
      ->first();

    if (!$solicitud) {
      return [
        'solicitud' => 0
      ];
    }

    //  Observaciones
    $obs = $this->observaciones($solicitud->id);

    $d2 = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.grupo_id')
          ->where('b.tipo', '=', 'grupo');
      })
      ->select([
        'b.grupo_nombre',
        'b.grupo_nombre_corto',
        'a.condicion',
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.id', '=', $solicitud->d2)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $d3 = json_decode($solicitud->d3, true);

    $d3Extra = DB::table('Actividad_investigador AS a')
      ->join('Eval_docente_actividad AS b', 'b.id', '=', 'a.eval_docente_actividad_id')
      ->select([
        'a.id',
        'b.tipo AS categoria',
        'a.periodo'
      ])
      ->where('b.eval_docente_investigador_id', '=', $solicitud->id)
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    $d3Complete = [];
    foreach ($d3 as $element) {
      $d3Complete[] = $element;
    }

    foreach ($d3Extra as $element) {
      $d3Complete[] = [
        'id' => $element->id,
        'categoria' => $element->categoria,
        'periodo' => $element->periodo,
      ];
    }

    // Inicializar un array para verificar la presencia de los años
    $yearsFound = array_fill_keys($lastTwoYears, false);

    // Recorrer el array y marcar los años encontrados
    foreach ($d3Complete as $element) {
      if (in_array((int)$element["periodo"], $lastTwoYears)) {
        $yearsFound[(int)$element["periodo"]] = true;
      }
    }

    // Verificar si se encontraron los dos años
    $allYearsFound = !in_array(false, $yearsFound);

    $d4 = json_decode($solicitud->d4, true);
    $filiacion = 0;
    foreach ($d4 as $item) {
      if ($item["filiacion"] == 1) {
        $filiacion++;
      }
    }

    $d5 = $this->deudasInvestigador($request);

    $d6 = json_decode($solicitud->d6, true);
    $d6_valid = $d6["fecha_fin"] > Carbon::now() ? true : false;

    $req6 = DB::table('Eval_declaracion_jurada AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'DECLARACION_JURADA');
      })
      ->select([
        DB::raw("CONCAT('/minio/declaracion-jurada/', b.key) AS url"),
        'a.fecha_inicio',
        'a.fecha_fin'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->orderByDesc('b.created_at')
      ->limit(1)
      ->get();

    if ($solicitud->estado == 'Observado') {
      $req1 = DB::table('Usuario_investigador')
        ->select([
          'renacyt',
          'renacyt_nivel'
        ])
        ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      $req2 = DB::table('Grupo_integrante AS a')
        ->join('Grupo AS b', function (JoinClause $join) {
          $join->on('b.id', '=', 'a.grupo_id')
            ->where('b.tipo', '=', 'grupo');
        })
        ->select([
          'b.grupo_nombre',
          'b.grupo_nombre_corto',
          'a.condicion',
        ])
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->whereNot('a.condicion', 'LIKE', 'Ex%')
        ->first();


      //  Proyectos (a)
      $req3A = DB::table('Proyecto AS a')
        ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
        ->select([
          'a.id',
          DB::raw("'A. Proyectos de investigación con financiamiento Institucional' AS categoria"),
          DB::raw("'a' AS sub_categoria"),
          'a.tipo_proyecto',
          'a.codigo_proyecto',
          'a.periodo',
          'b.condicion AS name'
        ])
        ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.estado', '=', 1)
        ->where('a.tipo_proyecto', '!=', 'PFEX')
        ->whereIn('a.periodo', $lastTwoYears)
        ->get();

      //  Proyectos fex (b)
      $req3B = DB::table('Proyecto AS a')
        ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
        ->select([
          'a.id',
          DB::raw("'B. Proyectos de investigación con financiamiento externo' AS categoria"),
          DB::raw("'a' AS sub_categoria"),
          'a.tipo_proyecto',
          'a.codigo_proyecto',
          'a.periodo',
          'b.condicion AS name'
        ])
        ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.estado', '=', 1)
        ->where('a.tipo_proyecto', '=', 'PFEX')
        ->whereIn('a.periodo', $lastTwoYears)
        ->get();

      //  Asesorías (c)
      $req3C = DB::table('Publicacion AS a')
        ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
        ->select([
          'a.id',
          DB::raw("'C. Asesoría de trabajo de investigación o tesis' AS categoria"),
          DB::raw("'c' AS sub_categoria"),
          'a.tipo_publicacion AS tipo_proyecto',
          'a.codigo_registro AS codigo_proyecto',
          DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
          'b.categoria AS name'
        ])
        ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.estado', '=', 1)
        ->where('a.tipo_publicacion', '=', 'tesis-asesoria')
        ->where('b.categoria', '=', 'Asesor')
        ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
        ->get();

      $req4 = DB::table('Publicacion AS a')
        ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
        ->leftJoin('Publicacion_index AS c', 'c.publicacion_id', '=', 'a.id')
        ->leftJoin('Publicacion_db_indexada AS d', 'd.id', '=', 'c.publicacion_db_indexada_id')
        ->select([
          'a.titulo',
          DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
          'a.codigo_registro',
          DB::raw("GROUP_CONCAT(d.nombre SEPARATOR ', ') AS indexada"),
          'b.filiacion'
        ])
        ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.estado', '=', 1)
        ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
        ->groupBy('a.id')
        ->get();

      $req6 = DB::table('Eval_declaracion_jurada AS a')
        ->join('File AS b', function (JoinClause $join) {
          $join->on('b.tabla_id', '=', 'a.id')
            ->where('b.tabla', '=', 'Eval_docente_investigador')
            ->where('b.recurso', '=', 'DECLARACION_JURADA');
        })
        ->select([
          DB::raw("CONCAT('/minio/declaracion-jurada/', b.key) AS url"),
          'a.fecha_inicio',
          'a.fecha_fin'
        ])
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->get();

      $rrhh = $this->rrhhCdi($request);

      return [
        'obs' => $obs,
        'antiguo' => $rrhh->antiguedad,
        'solicitud' => 1,
        'id' => $solicitud->id,
        'estado' => $solicitud->estado,
        'fecha' => $solicitud->created_at,
        'd1' => [
          'cumple' => $solicitud->d1 != "" ? true : false,
          'valor' => $req1->renacyt,
        ],
        'd2' => [
          'cumple' => $d2 != "" ? true : false,
          'valor' => $req2,
        ],
        'd3' => [
          'cumple' => $allYearsFound,
          'lista' => $req3A->merge($req3B)->merge($req3C)
        ],
        'd4' => [
          'cumple' => $filiacion,
          'lista' => $req4
        ],
        'd5' => [
          'cumple' => sizeof($d5) == 0 ? true : false,
          'lista' => $d5
        ],
        'd6' => [
          'cumple' => $d6_valid,
          'lista' => $req6
        ]
      ];
    } else {
      return [
        'obs' => $obs,
        'solicitud' => 1,
        'id' => $solicitud->id,
        'estado' => $solicitud->estado,
        'fecha' => $solicitud->created_at,
        'd1' => [
          'cumple' => $solicitud->d1 != "" ? true : false,
          'valor' => $solicitud->d1,
        ],
        'd2' => [
          'cumple' => $d2 != "" ? true : false,
          'valor' => $d2,
        ],
        'd3' => [
          'cumple' => $allYearsFound,
          'lista' => $d3Complete
        ],
        'd4' => [
          'cumple' => $filiacion,
          'lista' => $d4
        ],
        'd5' => [
          'cumple' => sizeof($d5) == 0 ? true : false,
          'lista' => $d5
        ],
        'd6' => [
          'cumple' => $d6_valid,
          'lista' => $req6
        ]
      ];
    }
  }

  /**
   *  Data de RRHH y antiguedad para permitir agregar ciertas actividades
   */
  public function rrhhCdi(Request $request) {
    $year1 = Carbon::now()->year;
    $year2 = $year1 - 1;
    $year3 = $year1 - 2;

    $rrhh = DB::table('Repo_rrhh AS a')
      ->join('Usuario_investigador AS b', 'b.codigo', '=', 'a.ser_cod_ant')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.ser_cod AS doc_numero',
        DB::raw("CONCAT(a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS nombres"),
        'b.cti_vitae',
        'b.renacyt',
        'b.renacyt_nivel',
        'b.codigo_orcid',
        'b.google_scholar',
        'c.nombre AS facultad',
        'c.id AS facultad_id',
        'a.ser_sexo',
        'a.des_dep_cesantes',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(a.ser_cat_act, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END AS docente_categoria"),
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.ser_cat_act, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.ser_cat_act, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.ser_cat_act, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        DB::raw("SUBSTRING_INDEX(a.ser_cat_act, '-', -1) AS horas"),
        DB::raw("CASE
          WHEN YEAR(ser_fech_in_unmsm) IN ($year1, $year2, $year3) THEN 1
          ELSE 0
        END AS antiguedad")
      ])
      ->where('b.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    return $rrhh;
  }

  /**
   *  Solicitud del CDI con la data actual
   */
  public function solicitarCDI(Request $request) {
    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $d6 = $this->presentarDJ($request);

    $req1 = DB::table('Usuario_investigador')
      ->select([
        'renacyt',
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $d1 = $req1->renacyt ?? "";

    $req2 = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.grupo_id')
          ->where('b.tipo', '=', 'grupo');
      })
      ->select([
        'b.id',
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $d2 = $req2->id ?? "";

    //  Proyectos (a)
    $req3A = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'A. Proyectos de investigación con financiamiento Institucional' AS categoria"),
        DB::raw("'a' AS sub_categoria"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'b.condicion AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_proyecto', '!=', 'PFEX')
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    //  Proyectos fex (b)
    $req3B = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'B. Proyectos de investigación con financiamiento externo' AS categoria"),
        DB::raw("'a' AS sub_categoria"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'b.condicion AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    //  Asesorías (c)
    $req3C = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'C. Asesoría de trabajo de investigación o tesis' AS categoria"),
        DB::raw("'c' AS sub_categoria"),
        'a.tipo_publicacion AS tipo_proyecto',
        'a.codigo_registro AS codigo_proyecto',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'b.categoria AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_publicacion', '=', 'tesis-asesoria')
      ->where('b.categoria', '=', 'Asesor')
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->get();

    $d3 = $req3A->merge($req3B)->merge($req3C);

    $req4 = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_index AS c', 'c.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_db_indexada AS d', 'd.id', '=', 'c.publicacion_db_indexada_id')
      ->select([
        'a.id AS id',
        'a.titulo',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'a.codigo_registro',
        DB::raw("GROUP_CONCAT(d.nombre SEPARATOR ', ') AS indexada"),
        'b.filiacion'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->groupBy('a.id')
      ->get();

    $d4 = $req4;

    $rrhh = $this->rrhhCdi($request);

    $eval_id = DB::table('Eval_docente_investigador')
      ->insertGetId([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'facultad_id' => $rrhh->facultad_id,
        'tipo_eval' => 'Solicitud',
        'nombres' => $rrhh->nombres,
        'doc_numero' => $rrhh->doc_numero,
        'sexo' => $rrhh->ser_sexo,
        'tipo_docente' => 'DOCENTE PERMANENTE',
        'docente_categoria' => $rrhh->docente_categoria,
        'clase' => $rrhh->clase,
        'horas' => $rrhh->horas,
        'orcid' => $rrhh->codigo_orcid,
        'google_scholar' => $rrhh->google_scholar,
        'cti_vitae' => $rrhh->cti_vitae,
        'renacyt' => $rrhh->renacyt,
        'renacyt_nivel' => $rrhh->renacyt_nivel,
        'd1' => $d1,
        'd2' => $d2,
        'd3' => $d3,
        'd4' => $d4,
        'd6' => json_encode($d6),
        'estado' => 'ENVIADO',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    DB::table('Eval_docente_actividad')
      ->whereNull('eval_docente_investigador_id')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->update([
        'eval_docente_investigador_id' => $eval_id
      ]);

    return ['message' => 'success', 'detail' => 'Solicitud enviada con éxito'];
  }

  /**
   *  Verificar los prerrequisitos para solicitar la constancia:
   *  Orcid asociado al rais, no tener en blanco el CTI Vitae ni
   *  el Google Scholar
   */
  public function preCdi(Request $request) {
    $cuenta = DB::table('Usuario_investigador AS a')
      ->join('Token_investigador_orcid AS b', 'b.investigador_id', '=', 'a.id')
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.cti_vitae', '!=', '')
      ->where('a.google_scholar', '!=', '')
      ->count();

    return $cuenta;
  }

  /**
   *  Verificar si el investigador tiene  una constancia vigente
   */
  public function constanciaCdi(Request $request) {
    $constancia = DB::table('Eval_docente_investigador AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.tabla_id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'CONSTANCIA_FIRMADA');
      })
      ->select([
        'a.fecha_fin',
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.tipo_eval', '=', 'Constancia')
      ->where('a.fecha_fin', '>', Carbon::now())
      ->first();

    return $constancia;
  }

  /**
   *  Ver las deudas que tiene un investigador
   */
  public function deudasInvestigador(Request $request) {
    $deudas = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->select([
        'c.titulo',
        'c.periodo',
        'c.tipo_proyecto',
        'b.id AS id_deuda',
        'b.detalle'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNull('b.fecha_sub')
      ->get();

    return $deudas;
  }

  /**
   *  Actividades extras:
   *  - Listado: Lista de las actividades con el id de evaluación en null, junto a los archivos
   *  - Añadir: Carga de archivo y data en las tablas de actividades y files
   *  - Eliminar: Eliminar registros de la BD
   * 
   */
  public function actividadesExtra(Request $request) {
    $actividades = DB::table('Eval_docente_actividad AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_actividad');
      })
      ->select([
        'a.id',
        'a.tipo',
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNull('a.eval_docente_investigador_id')
      ->get();

    return $actividades;
  }

  public function addActividad(Request $request) {
    if ($request->hasFile('file')) {

      $now = Carbon::now();
      $name = "token-" . $now->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();

      $id = DB::table('Eval_docente_actividad')
        ->insertGetId([
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'tipo' => $request->input('tipo'),
          'categoria_id' => $request->input('categoria_id'),
          'estado' => 1,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Eval_docente_actividad',
          'tabla_id' => $id,
          'recurso' => 'EVAL_ACTIVIDAD',
          'bucket' => 'eval-docente-actividad',
          'key' => $name,
          'estado' => 20,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

      $this->uploadFile($request->file('file'), "eval-docente-actividad", $name);

      return ['message' => 'success', 'detail' => 'Actividad cargada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar el archivo'];
    }
  }

  public function deleteActividad(Request $request) {
    DB::table('Eval_docente_actividad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    DB::table('File')
      ->where('tabla_id', '=', $request->query('id'))
      ->where('tabla', '=', 'Eval_docente_actividad')
      ->delete();
  }

  /**
   *  Actividades al estar observada la solicitud
   *  - Listado: Lista de las actividades con el id de evaluación que se recibe en query, junto a los archivos
   *  - Añadir: Carga de archivo y data en las tablas de actividades y files
   */

  public function actividadesExtraObs(Request $request) {
    $actividades = DB::table('Eval_docente_actividad AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_actividad');
      })
      ->select([
        'a.id',
        'a.tipo',
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.eval_docente_investigador_id', '=', $request->query('id'))
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    return $actividades;
  }

  public function addActividadObs(Request $request) {
    if ($request->hasFile('file')) {

      $now = Carbon::now();
      $name = "token-" . $now->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();

      $id = DB::table('Eval_docente_actividad')
        ->insertGetId([
          'eval_docente_investigador_id' => $request->input('id'),
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'tipo' => $request->input('tipo'),
          'categoria_id' => $request->input('categoria_id'),
          'estado' => 1,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Eval_docente_actividad',
          'tabla_id' => $id,
          'recurso' => 'EVAL_ACTIVIDAD',
          'bucket' => 'eval-docente-actividad',
          'key' => $name,
          'estado' => 20,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

      $this->uploadFile($request->file('file'), "eval-docente-actividad", $name);

      return ['message' => 'success', 'detail' => 'Actividad cargada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar el archivo'];
    }
  }

  public function actualizarSolicitud(Request $request) {
    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $req1 = DB::table('Usuario_investigador')
      ->select([
        'renacyt',
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $d1 = $req1->renacyt ?? "";

    $req2 = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.grupo_id')
          ->where('b.tipo', '=', 'grupo');
      })
      ->select([
        'b.id',
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $d2 = $req2->id ?? "";

    //  Proyectos (a)
    $req3A = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'A. Proyectos de investigación con financiamiento Institucional' AS categoria"),
        DB::raw("'a' AS sub_categoria"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'b.condicion AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_proyecto', '!=', 'PFEX')
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    //  Proyectos fex (b)
    $req3B = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'B. Proyectos de investigación con financiamiento externo' AS categoria"),
        DB::raw("'a' AS sub_categoria"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'b.condicion AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    //  Asesorías (c)
    $req3C = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->select([
        'a.id',
        DB::raw("'C. Asesoría de trabajo de investigación o tesis' AS categoria"),
        DB::raw("'c' AS sub_categoria"),
        'a.tipo_publicacion AS tipo_proyecto',
        'a.codigo_registro AS codigo_proyecto',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'b.categoria AS name'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->where('a.tipo_publicacion', '=', 'tesis-asesoria')
      ->where('b.categoria', '=', 'Asesor')
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->get();

    $d3 = $req3A->merge($req3B)->merge($req3C);

    $req4 = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_index AS c', 'c.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_db_indexada AS d', 'd.id', '=', 'c.publicacion_db_indexada_id')
      ->select([
        'a.id AS id',
        'a.titulo',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'a.codigo_registro',
        DB::raw("GROUP_CONCAT(d.nombre SEPARATOR ', ') AS indexada"),
        'b.filiacion'
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.estado', '=', 1)
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->groupBy('a.id')
      ->get();

    $d4 = $req4;

    $rrhh = $this->rrhhCdi($request);

    DB::table('Eval_docente_investigador')
      ->where('id', '=', $request->input('id'))
      ->update([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'facultad_id' => $rrhh->facultad_id,
        'tipo_eval' => 'Solicitud',
        'nombres' => $rrhh->nombres,
        'doc_numero' => $rrhh->doc_numero,
        'sexo' => $rrhh->ser_sexo,
        'tipo_docente' => 'DOCENTE PERMANENTE',
        'docente_categoria' => $rrhh->docente_categoria,
        'clase' => $rrhh->clase,
        'horas' => $rrhh->horas,
        'orcid' => $rrhh->codigo_orcid,
        'google_scholar' => $rrhh->google_scholar,
        'cti_vitae' => $rrhh->cti_vitae,
        'renacyt' => $rrhh->renacyt,
        'renacyt_nivel' => $rrhh->renacyt_nivel,
        'd1' => $d1,
        'd2' => $d2,
        'd3' => $d3,
        'd4' => $d4,
        'estado' => 'ENVIADO',
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'La solicitud se ha actualizado correctamente'];
  }

  /**
   *  Observaciones de la solicitud
   */
  public function observaciones($id) {
    $observaciones = DB::table('Eval_docente_investigador_obs')
      ->select([
        'observacion',
        'created_at'
      ])
      ->where('eval_investigador_id', '=', $id)
      ->get();

    return $observaciones;
  }
}

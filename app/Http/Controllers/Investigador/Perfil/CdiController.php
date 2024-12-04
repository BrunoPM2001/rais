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

  public function dataSolicitar(Request $request, $investigador_id) {
    //  Últimos dos años
    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $req1 = DB::table('Usuario_investigador')
      ->select([
        'renacyt',
        'renacyt_nivel'
      ])
      ->where('id', '=', $investigador_id)
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
      ->where('a.investigador_id', '=', $investigador_id)
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
      ->where('b.investigador_id', '=', $investigador_id)
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
      ->where('b.investigador_id', '=', $investigador_id)
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
      ->where('b.investigador_id', '=', $investigador_id)
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
        DB::raw("CASE(b.filiacion)
              WHEN 1 THEN 'Sí'
              WHEN 0 THEN 'No'
              ELSE '-'
            END AS filiacion"),
        DB::raw("CASE(b.filiacion_unica)
              WHEN 1 THEN 'Sí'
              WHEN 0 THEN 'No'
              ELSE '-'
            END AS filiacion_unica"),
      ])
      ->where('b.investigador_id', '=', $investigador_id)
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
      ->where('a.investigador_id', '=', $investigador_id)
      ->whereNull('b.fecha_sub')
      ->get();

    $actividades_extra = $this->actividadesExtra($request);
    $rrhh = $this->rrhhCdi($request);
    //  Result
    return [
      'req1' => $req1,
      'req2' => $req2,
      'req3' => $req3A->merge($req3B)->merge($req3C),
      'req4' => $req4,
      'req5' => $req5,
      'actividades_extra' => $actividades_extra,
      'rrhh' => $rrhh
    ];
  }

  public function dataSolicitud(Request $request, $solicitud_id) {
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
        'estado',
        'created_at'
      ])
      ->where('id',  '=', $solicitud_id)
      ->first();

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
      if ($item["filiacion"] == "1") {
        $filiacion++;
      }
    }

    $d5 = $this->deudasInvestigador($request);

    $d6 = json_decode($solicitud->d6, true);
    $d6_valid = $d6["fecha_fin"] > Carbon::now() ? true : false;

    $obs = $this->observaciones($solicitud_id);
    $rrhh = $this->rrhhCdi($request);

    return [
      'id' => $solicitud_id,
      'obs' => $obs,
      'antiguo' => $rrhh->antiguedad,
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
        'cumple' => $filiacion > 0 ? true : false,
        'lista' => $d4,
      ],
      'd5' => [
        'cumple' => sizeof($d5) == 0 ? true : false,
        'lista' => $d5
      ],
      'd6' => [
        'cumple' => $d6_valid,
        'lista' => $d6
      ]
    ];
  }

  public function verificarEstado3($investigador_id) {
    return DB::table('Eval_docente_investigador')
      ->select([
        'id',
      ])
      ->where('investigador_id', '=', $investigador_id)
      ->whereNotIn('estado', ['Vigente', 'No vigente', 'Anulado', 'No aprobado'])
      ->first();
  }

  public function verificarEstado4($investigador_id) {
    return DB::table('Eval_docente_investigador AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.tabla_id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'CONSTANCIA_FIRMADA');
      })
      ->select([
        'a.id',
        DB::raw("DATE(a.fecha_constancia) AS fecha_constancia"),
        DB::raw("DATE(a.fecha_fin) AS fecha_fin"),
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('a.tipo_eval', '=', 'Constancia')
      ->where('a.estado', '=', 'Vigente')
      ->orderByDesc('a.id')
      ->first();
  }

  public function verificarEstado6($investigador_id, $constancia_id) {
    return DB::table('Eval_docente_investigador')
      ->select([
        'id'
      ])
      ->where('investigador_id', '=', $investigador_id)
      ->where('id', '>', $constancia_id)
      ->first();
  }

  public function cdiEstado(Request $request) {
    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;
    $ultimaConstanciaVigente = $this->verificarEstado4($investigador_id);

    if ($ultimaConstanciaVigente) {
      $fecha1 = Carbon::now()->addMonths(2);
      $fecha2 = Carbon::parse($ultimaConstanciaVigente->fecha_fin);

      //  Tiene constancia vigente
      if ($fecha1->greaterThan($fecha2)) {

        $solicitudNueva = $this->verificarEstado6($investigador_id, $ultimaConstanciaVigente->id);

        //  Sí ya hay una solicitud en curso
        if ($solicitudNueva) {

          $info = $this->dataSolicitud($request, $solicitudNueva->id);
          return [
            "estado" => 6,
            "constancia" => $ultimaConstanciaVigente,
            "solicitud" => $info
          ];
        } else {
          //  Enviar info para la solicitud y su constancia vigente
          $info = $this->dataSolicitar($request, $investigador_id);
          return [
            "estado" => 5,
            "constancia" => $ultimaConstanciaVigente,
            "solicitar" => $info
          ];
        }
      } else {
        //  Enviar constancia
        return [
          "estado" => 4,
          "constancia" => $ultimaConstanciaVigente
        ];
      }
    } else {
      $solicitud = $this->verificarEstado3($investigador_id);
      if ($solicitud) {
        $info = $this->dataSolicitud($request, $solicitud->id);
        return [
          "estado" => 3,
          "solicitud" => $info
        ];
      } elseif (!$this->rrhhCdi($request)) {
        //  No está registrado en RRHH
        return ['estado' => 0];
      } elseif (!$this->preCdi($request)) {
        //  No cumple los prerrequisitos
        return ['estado' => 1];
      } else {
        $info = $this->dataSolicitar($request, $investigador_id);
        return [
          "estado" => 2,
          "solicitar" => $info
        ];
      }
    }
  }

  /**
   *  Inserción del archivo de la DJ
   */
  public function presentarDJ(Request $request) {
    $date = Carbon::now()->format("Y-m-d");
    $date_end = Carbon::now()->addMonths(3)->format('Y-m-d');
    $nameFile = "Constancia-dj_" . $date . "-" . Str::random(8) . ".pdf";

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
        1 => 'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
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
      'fecha_fin' => $date_end,
      'url' => '/minio/declaracion-jurada/' . $nameFile
    ];
  }

  /**
   *  Data de RRHH y antiguedad para permitir agregar ciertas actividades
   */
  public function rrhhCdi(Request $request) {
    $year1 = Carbon::now()->year;
    $year2 = $year1 - 1;
    $year3 = $year1 - 2;

    $rrhh = DB::table('Repo_rrhh AS a')
      ->join('Usuario_investigador AS b', 'b.doc_numero', '=', 'a.ser_doc_id_act')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.ser_doc_id_act AS doc_numero',
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
        DB::raw("CASE(b.filiacion)
          WHEN 1 THEN 1
          WHEN 0 THEN 0
          ELSE '-'
        END AS filiacion"),
        DB::raw("CASE(b.filiacion_unica)
          WHEN 1 THEN 1
          WHEN 0 THEN 0
          ELSE '-'
        END AS filiacion_unica"),
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
        'd6' => json_encode($d6, JSON_UNESCAPED_UNICODE),
        'estado' => 'Enviado',
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
   *  el Google Scholar, el nivel de renacyt tiene que ser diferente
   *  a Carlos Monge y Maria R.
   */
  public function preCdi(Request $request) {
    return DB::table('Usuario_investigador AS a')
      ->join('Token_investigador_orcid AS b', 'b.investigador_id', '=', 'a.id')
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.cti_vitae', '!=', '')
      ->whereNot('a.renacyt_nivel', 'LIKE', '%worows%')
      ->whereNot('a.renacyt_nivel', 'LIKE', '%Monge%')
      ->where('a.google_scholar', '!=', '')
      ->count();
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
        DB::raw("CASE(b.filiacion)
          WHEN 1 THEN 1
          WHEN 0 THEN 0
          ELSE '-'
        END AS filiacion"),
        DB::raw("CASE(b.filiacion_unica)
          WHEN 1 THEN 1
          WHEN 0 THEN 0
          ELSE '-'
        END AS filiacion_unica"),
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
        'estado' => 'Enviado',
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

<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PmultiController extends S3Controller {
  public function listado(Request $request) {
    $listado = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id',
        'b.titulo',
        'b.step',
        DB::raw("CASE(b.estado)
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
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.condicion', '=', 'Responsable')
      ->where('b.tipo_proyecto', '=', 'PMULTI')
      ->where('b.periodo', '=', 2025)
      ->get();

    return $listado;
  }

  //  Verifica las condiciones para participar
  public function verificar(Request $request, $proyecto_id = null) {
    $errores = [];
    $detail = null;

    //  Ser titular de un grupo de investigación
    $req1 = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', function (JoinClause $join) {
        $join->on('b.investigador_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Titular');
      })
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req1 == 0 && $errores[] = "Necesita ser titular de un grupo de investigación";

    if ($proyecto_id != null) {
      $req2 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->where('a.proyecto_id', '=', $proyecto_id)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PMULTI')
        ->count();

      $req2 == 0 && $errores[] = "No figura como responsable del proyecto";
    }

    $req3 = DB::table('view_deudores AS vdeuda')
      ->where('vdeuda.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req3 != 0 && $errores[] = "Usted tiene registradas deudas pendientes que deben ser resueltas para participar en el concurso";


    if (!empty($errores)) {
      return ['estado' => false, 'errores' => $errores];
    } else {
      return ['estado' => true];
    }
  }

  public function verificar1(Request $request) {

    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    } else {
      if (isset($res1["id"])) {
        return ['go' => $res1["id"], 'step' => $res1["step"]];
      }
    }

    $datos = DB::table('Usuario_investigador AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS c', 'c.id', '=', 'b.area_id')
      ->join('Grupo_integrante AS d', function (JoinClause $join) {
        $join->on('d.investigador_id', '=', 'a.id')
          ->where('d.condicion', '=', 'Titular');
      })
      ->join('Grupo AS e', 'e.id', '=', 'd.grupo_id')
      ->select([
        DB::raw("CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS nombres"),
        'c.nombre AS area',
        'b.nombre AS facultad',
        'e.id AS grupo_id',
        'e.grupo_nombre'
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.grupo_id', '=', $datos->grupo_id)
      ->whereNull('a.concytec_codigo')
      ->get();

    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label"),
        'parent_id'
      ])
      ->get();

    $ods = DB::table('Ods')
      ->select([
        'id AS value',
        'descripcion AS label'
      ])
      ->get();

    if ($request->query('id')) {
      $proyecto = DB::table('Proyecto AS a')
        ->join('Proyecto_descripcion AS b', function (JoinClause $join) {
          $join->on('a.id', '=', 'b.proyecto_id')
            ->where('b.codigo', '=', 'objetivo_ods');
        })
        ->join('Proyecto_descripcion AS c', function (JoinClause $join) {
          $join->on('a.id', '=', 'c.proyecto_id')
            ->where('c.codigo', '=', 'area_tematica');
        })
        ->select([
          'a.linea_investigacion_id',
          'a.ocde_id',
          'a.titulo',
          'a.localizacion',
          'b.detalle AS objetivo_ods',
          'c.detalle AS area_tematica'
        ])
        ->where('a.id', '=', $request->query('id'))
        ->first();

      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'proyecto' => $proyecto,
        'ocde' => $ocde,
        'ods' => $ods,
      ];
    } else {
      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'ocde' => $ocde,
        'ods' => $ods,
      ];
    }
  }

  public function registrar1(Request $request) {
    $date = Carbon::now();
    if ($request->input('id')) {
      DB::table('Proyecto')
        ->where('id', '=', $request->input('id'))
        ->update([
          'titulo' => $request->input('titulo'),
          'linea_investigacion_id' => $request->input('linea')["value"],
          'ocde_id' => $request->input('ocde')["value"],
          'localizacion' => $request->input('localizacion')["value"],
          'step' => 2,
          'estado' => 6,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_descripcion')
        ->updateOrInsert([
          'proyecto_id' => $request->input('id'),
          'codigo' => 'area_tematica',
        ], [
          'detalle' => $request->input('area_tematica')["value"],
        ]);

      DB::table('Proyecto_descripcion')
        ->updateOrInsert([
          'proyecto_id' => $request->input('id'),
          'codigo' => 'objetivo_ods',
        ], [
          'detalle' => $request->input('ods')["value"],
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $request->input('id')];
    } else {
      $datos = DB::table('Usuario_investigador AS a')
        ->join('Grupo_integrante AS b', function (JoinClause $join) {
          $join->on('b.investigador_id', '=', 'a.id')
            ->where('b.condicion', '=', 'Titular');
        })
        ->select([
          'a.facultad_id',
          'b.grupo_id',
          'b.id'
        ])
        ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      $id = DB::table('Proyecto')
        ->insertGetId([
          'titulo' => $request->input('titulo'),
          'linea_investigacion_id' => $request->input('linea')["value"],
          'ocde_id' => $request->input('ocde')["value"],
          'facultad_id' => $datos->facultad_id,
          'grupo_id' => $datos->grupo_id,
          'localizacion' => $request->input('localizacion')["value"],
          'tipo_proyecto' => 'PMULTI',
          'step' => 2,
          'estado' => 6,
          'periodo' => Carbon::now()->year,
          'fecha_inscripcion' => $date,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_descripcion')
        ->insert([
          'proyecto_id' => $id,
          'codigo' => 'area_tematica',
          'detalle' => $request->input('area_tematica')["value"],
        ]);

      DB::table('Proyecto_descripcion')
        ->insert([
          'proyecto_id' => $id,
          'codigo' => 'objetivo_ods',
          'detalle' => $request->input('ods')["value"],
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'proyecto_integrante_tipo_id' => 56,
          'grupo_id' => $datos->grupo_id,
          'grupo_integrante_id' => $datos->id,
          'condicion' => 'Responsable',
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
    }
  }

  public function verificar2(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $data = DB::table('Usuario_investigador AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->join('Dependencia AS c', 'c.id', '=', 'a.dependencia_id')
      ->select([
        DB::raw("CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS nombres"),
        'a.doc_numero',
        'a.fecha_nac',
        'a.especialidad',
        'a.titulo_profesional',
        'a.grado',
        'a.tipo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        'a.codigo',
        'c.dependencia',
        'b.nombre AS facultad',
        'a.codigo_orcid',
        'a.scopus_id',
        'a.google_scholar',
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $carta = DB::table('Proyecto_doc')
      ->select([
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url"),
        'comentario AS url_fecha'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->where('categoria', '=', 'carta')
      ->where('nombre', '=', 'Carta de compromiso de confidencialidad')
      ->where('estado', '=', 1)
      ->first();

    return [
      'estado' => true,
      'data' => $data,
      'carta' => $carta
    ];
  }

  public function registrar2(Request $request) {

    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('categoria', '=', 'carta')
        ->where('nombre', '=', 'Carta de compromiso de confidencialidad')
        ->where('estado', '=', 1)
        ->update([
          'estado' => 0
        ]);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'archivo' => $name,
          'categoria' => 'carta',
          'nombre' => 'Carta de compromiso de confidencialidad',
          'estado' => 1,
          'comentario' => Carbon::now(),
          'tipo' => 28,
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados'];
    } else {
      $count = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('categoria', '=', 'carta')
        ->where('nombre', '=', 'Carta de compromiso de confidencialidad')
        ->where('estado', '=', 1)
        ->count();
      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'No hay una carta de compromiso cargada'];
      } else {
        return ['message' => 'success', 'detail' => 'Datos guardados'];
      }
    }
  }

  public function verificar3(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $carta = DB::table('Proyecto_doc')
      ->select([
        'archivo AS url',
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->where('categoria', '=', 'carta')
      ->where('nombre', '=', 'Carta de compromiso de confidencialidad')
      ->where('estado', '=', 1)
      ->first();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoin('File AS e', function (JoinClause $join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.bucket', '=', 'carta-compromiso')
          ->where('e.recurso', '=', 'CARTA_COMPROMISO')
          ->where('e.estado', '=', 20);
      })
      ->leftJoin('Grupo_integrante AS f', function (JoinClause $join) {
        $join->on('f.investigador_id', '=', 'b.id')
          ->whereNot('f.condicion', 'LIKE', 'Ex %');
      })
      ->leftJoin('Grupo AS g', 'g.id', '=', 'f.grupo_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'd.nombre AS facultad',
        'g.grupo_nombre_corto',
        DB::raw("COALESCE(CONCAT('/minio/carta-compromiso/', e.key), '/minio/proyecto-doc/" . $carta->url . "') AS url"),
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->groupBy('b.id')
      ->get();

    return ['estado' => true, 'integrantes' => $integrantes];
  }

  public function listadoCorresponsables(Request $request) {

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Eval_docente_investigador AS c', function (JoinClause $join) {
        $join->on('c.investigador_id', '=', 'b.id')
          ->where('c.estado', '=', 'Vigente');
      })
      ->leftJoin('view_deudores AS d', 'd.investigador_id', '=', 'b.id')
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
        DB::raw("COUNT(d.deuda_id) AS deudas"),
        'b.renacyt',
        'c.id AS cdi',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Titular')
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->facultad,
          'Deudas: ' . $item->deudas,
          $item->renacyt || $item->renacyt != "" ? 'Tiene renacyt' : 'No tiene renacyt',
          $item->cdi ? 'Tiene CDI' : 'No tiene CDI',
        ];
        $item->disabled = $item->deudas == 0 && $item->renacyt && $item->renacyt != "" && $item->cdi ? false : true;
        return $item;
      });

    return $listado;
  }

  public function listadoDocentes(Request $request) {

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Eval_docente_investigador AS c', function (JoinClause $join) {
        $join->on('c.investigador_id', '=', 'b.id')
          ->where('c.estado', '=', 'Vigente');
      })
      ->leftJoin('view_deudores AS d', 'd.investigador_id', '=', 'b.id')
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
        DB::raw("COUNT(d.deuda_id) AS deudas"),
        'b.renacyt',
        'c.id AS cdi',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Titular')
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->facultad,
          'Deudas: ' . $item->deudas,
          $item->renacyt || $item->renacyt != "" ? 'Tiene renacyt' : 'No tiene renacyt',
          $item->cdi ? 'Tiene CDI' : 'No tiene CDI',
        ];
        $item->disabled = $item->deudas == 0 && $item->renacyt && $item->renacyt != "" && $item->cdi ? false : true;
        return $item;
      });

    return $listado;
  }

  public function listadoTesistas(Request $request) {

    $grupos = DB::table('Proyecto_integrante AS a')
      ->select([
        'a.grupo_id'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->whereIn('a.proyecto_integrante_tipo_id', [56, 57, 58])
      ->pluck('a.grupo_id')
      ->values();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'b.tipo',
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Adherente')
      ->where('b.tipo', 'LIKE', 'Estudiante%')
      ->whereIn('a.grupo_id', $grupos)
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->tipo,
          $item->facultad,
        ];
        return $item;
      });

    return $listado;
  }

  public function listadoExterno(Request $request) {

    $grupos = DB::table('Proyecto_integrante AS a')
      ->select([
        'a.grupo_id'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->whereIn('a.proyecto_integrante_tipo_id', [56, 57, 58])
      ->pluck('a.grupo_id')
      ->values();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'b.tipo',
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Adherente')
      ->where('b.tipo', 'LIKE', 'Externo%')
      ->whereIn('a.grupo_id', $grupos)
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->tipo,
          $item->facultad,
        ];
        return $item;
      });

    return $listado;
  }

  public function agregarIntegrante(Request $request) {
    $count = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->input('id'))
      ->where('investigador_id', '=', $request->input('investigador_id'))
      ->count();

    if ($count == 0) {
      $integrante_id = DB::table('Proyecto_integrante')
        ->insertGetId([
          'proyecto_id' => $request->input('id'),
          'grupo_id' => $request->input('grupo_id'),
          'investigador_id' => $request->input('investigador_id'),
          'grupo_integrante_id' => $request->input('grupo_integrante_id'),
          'proyecto_integrante_tipo_id' => $request->input('proyecto_integrante_tipo_id'),
          'tipo_tesis' => $request->input('tipo'),
          'titulo_tesis' => $request->input('titulo'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      if ($request->hasFile('file')) {
        $date = Carbon::now();
        $name = $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
        $this->uploadFile($request->file('file'), "carta-compromiso", $name);

        DB::table('File')
          ->insert([
            'tabla_id' => $integrante_id,
            'tabla' => 'Proyecto_integrante',
            'bucket' => 'carta-compromiso',
            'key' => $name,
            'recurso' => 'CARTA_COMPROMISO',
            'estado' => 20,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
      }

      return ['message' => 'success', 'detail' => 'Integrante añadido'];
    } else {
      return ['message' => 'error', 'detail' => 'No puede añadir al mismo integrante 2 veces'];
    }
  }

  public function eliminarIntegrante(Request $request) {
    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Integrante eliminado'];
  }

  public function verificar4(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'estado_arte',
        'planteamiento_problema',
        'justificacion',
        'contribucion_impacto',
        'objetivos',
        'metodologia_trabajo',
        'referencias_bibliograficas',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $palabras_clave = DB::table('Proyecto')
      ->select([
        'palabras_clave'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $archivo1 = DB::table('Proyecto_doc')
      ->select([
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->where('tipo', '=', 27)
      ->where('categoria', '=', 'anexo')
      ->where('nombre', '=', 'Estado del arte')
      ->where('estado', '=', 1)
      ->first();

    $archivo2 = DB::table('Proyecto_doc')
      ->select([
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->where('tipo', '=', 26)
      ->where('categoria', '=', 'anexo')
      ->where('nombre', '=', 'Metodología de trabajo')
      ->where('estado', '=', 1)
      ->first();

    return [
      'estado' => true,
      'descripcion' => $descripcion,
      'palabras_clave' => $palabras_clave->palabras_clave,
      'archivos' => [
        'estado_arte' => $archivo1?->url,
        'metodologia' => $archivo2?->url,
      ]
    ];
  }

  public function registrar4(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'resumen_ejecutivo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('resumen_ejecutivo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'estado_arte', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('estado_arte')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'planteamiento_problema', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('planteamiento_problema')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'justificacion', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('justificacion')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'contribucion_impacto', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('contribucion_impacto')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'objetivos', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('objetivos')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'metodologia_trabajo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('metodologia_trabajo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'referencias_bibliograficas', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('referencias_bibliograficas')]);

    if ($request->hasFile('file1')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('tipo', '=', 27)
        ->where('categoria', '=', 'anexo')
        ->where('nombre', '=', 'Estado del arte')
        ->update([
          'estado' => 0
        ]);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'tipo' => 27,
          'categoria' => 'anexo',
          'nombre' => 'Estado del arte',
          'archivo' => $name,
          'estado' => 1,
          'comentario' => Carbon::now(),
        ]);
    } else {
      $count = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('tipo', '=', 27)
        ->where('categoria', '=', 'anexo')
        ->where('nombre', '=', 'Estado del arte')
        ->where('estado', '=', 1)
        ->count();

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'Necesita cargar el primer anexo (estado del arte)'];
      }
    }

    if ($request->hasFile('file2')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
      $this->uploadFile($request->file('file2'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('tipo', '=', 26)
        ->where('categoria', '=', 'anexo')
        ->where('nombre', '=', 'Metodología de trabajo')
        ->update([
          'estado' => 0
        ]);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'tipo' => 26,
          'categoria' => 'anexo',
          'nombre' => 'Metodología de trabajo',
          'archivo' => $name,
          'estado' => 1,
          'comentario' => Carbon::now(),
        ]);
    } else {
      $count = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('tipo', '=', 26)
        ->where('categoria', '=', 'anexo')
        ->where('nombre', '=', 'Metodología de trabajo')
        ->where('estado', '=', 1)
        ->count();

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'Necesita cargar el segundo anexo (metodología de trabajo)'];
      }
    }

    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'step' => 4,
      ]);

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar5(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.proyecto_integrante_id',
        'a.actividad',
        'a.justificacion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'a.id AS value',
        DB::raw("CONCAT(c.nombre, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS label"),
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return ['estado' => true, 'actividades' => $actividades, 'integrantes' => $integrantes];
  }

  public function addActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'proyecto_integrante_id' => $request->input('responsable')["value"],
        'actividad' => $request->input('actividad'),
        'justificacion' => $request->input('justificacion'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
      ]);

    return ['message' => 'info', 'detail' => 'Actividad añadida'];
  }

  public function eliminarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Actividad eliminada'];
  }

  public function editActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->input('id'))
      ->update([
        'proyecto_integrante_id' => $request->input('responsable')["value"],
        'actividad' => $request->input('actividad'),
        'justificacion' => $request->input('justificacion'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
      ]);

    return ['message' => 'info', 'detail' => 'Actividad actualizada'];
  }

  public function verificar6(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.id AS partida_id',
        'b.codigo',
        'b.partida',
        'b.tipo',
        'a.monto',
        'a.justificacion'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->orderBy('a.tipo')
      ->get();

    //  Ids no repetidos
    $partidaIds = $presupuesto->pluck('partida_id');

    $partidas = DB::table('Partida_proyecto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
        'b.tipo',
      ])
      ->where('a.tipo_proyecto', '=', 'PMULTI')
      ->where('a.postulacion', '=', 1)
      ->whereNotIn('b.id', $partidaIds)
      ->get();

    //  Info de presupuesto
    $info = [
      'bienes_monto' => 0.00,
      'bienes_cantidad' => 0,
      'servicios_monto' => 0.00,
      'servicios_cantidad' => 0,
      'otros_monto' => 0.00,
      'otros_cantidad' => 0
    ];

    foreach ($presupuesto as $data) {
      if ($data->tipo == "Bienes") {
        $info["bienes_monto"] += $data->monto;
        $info["bienes_cantidad"]++;
      }
      if ($data->tipo == "Servicios") {
        $info["servicios_monto"] += $data->monto;
        $info["servicios_cantidad"]++;
      }
      if ($data->tipo == "Otros") {
        $info["otros_monto"] += $data->monto;
        $info["otros_cantidad"]++;
      }
    }

    $div = ($info["bienes_monto"] + $info["servicios_monto"] + $info["otros_monto"]);

    if ($div != 0) {
      $info["bienes_porcentaje"] = number_format(($info["bienes_monto"] / $div) * 100, 2);
      $info["servicios_porcentaje"] = number_format(($info["servicios_monto"] / $div) * 100, 2);
      $info["otros_porcentaje"] = number_format(($info["otros_monto"] / $div) * 100, 2);
    } else {
      $info["bienes_porcentaje"] = 0;
      $info["servicios_porcentaje"] = 0;
      $info["otros_porcentaje"] = 0;
    }

    return [
      'estado' => true,
      'partidas' => $partidas,
      'presupuesto' => $presupuesto,
      'info' => $info
    ];
  }

  public function verificar8(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    return ['estado' => true];
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_descripcion AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('codigo', '=', 'tipo_investigacion');
      })
      ->join('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->join('Area AS e', 'e.id', '=', 'd.area_id')
      ->join('Linea_investigacion AS f', 'f.id', '=', 'a.linea_investigacion_id')
      ->join('Ocde AS g', 'g.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_doc AS h', function (JoinClause $join) {
        $join->on('h.proyecto_id', '=', 'a.id')
          ->where('h.tipo', '=', 3)
          ->where('h.estado', '=', 1)
          ->where('h.categoria', '=', 'tesis')
          ->where('h.nombre', '=', 'Tesis Doctoral');
      })
      ->leftJoin('Proyecto_doc AS i', function (JoinClause $join) {
        $join->on('i.proyecto_id', '=', 'a.id')
          ->where('i.tipo', '=', 4)
          ->where('i.estado', '=', 1)
          ->where('i.categoria', '=', 'tesis')
          ->where('i.nombre', '=', 'Tesis Maestría');
      })
      ->select([
        'a.titulo',
        'c.grupo_nombre',
        'e.nombre AS area',
        'd.nombre AS facultad',
        'f.nombre AS linea',
        'b.detalle AS tipo_investigacion',
        'a.localizacion',
        'g.linea AS ocde',
        DB::raw("CASE
          WHEN h.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS url1"),
        DB::raw("CASE
          WHEN i.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS url2"),
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->join('Dependencia AS d', 'd.id', '=', 'b.dependencia_id')
      ->select([
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.codigo',
        'd.dependencia',
        'c.nombre AS facultad',
        'b.cti_vitae',
        'b.codigo_orcid',
        'b.scopus_id',
        'b.google_scholar',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->where('a.condicion', '=', 'Responsable')
      ->first();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'c.nombre AS condicion',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS integrante"),
        'b.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
        'duracion'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.convocatorias.psinfipu', [
      'proyecto' => $proyecto,
      'responsable' => $responsable,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
    ]);
    return $pdf->stream();
  }

  public function enviar(Request $request) {
    $count = DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 6)
      ->update([
        'estado' => 5
      ]);

    if ($count > 0) {
      return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
    } else {
      return ['message' => 'error', 'detail' => 'Ya ha enviado su solicitud'];
    }
  }
}

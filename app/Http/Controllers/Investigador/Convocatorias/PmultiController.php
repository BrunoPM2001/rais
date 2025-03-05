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

  public function eliminarPropuesta(Request $request) {
    $proyectoId = $request->query('id');

    if ($proyectoId) {
      DB::table('Proyecto_descripcion')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_actividad')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_presupuesto')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto')
        ->where('id', '=', $proyectoId)
        ->delete();

      return ['message' => 'info', 'detail' => 'Propuesta eliminada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'No se pudo eliminar la propuesta'];
    }
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

    $req4 = DB::table('Usuario_investigador')
      ->whereNotNull('cti_vitae')
      ->where('cti_vitae', '!=', '')
      ->whereNotNull('codigo_orcid')
      ->where('codigo_orcid', '!=', '')
      ->whereNotNull('google_scholar')
      ->where('google_scholar', '!=', '')
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req4 == 0 && $errores[] = "Necesita tener CTI Vitae, orcid registrado y google scholar para participar";

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

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Eval_docente_investigador AS c', function (JoinClause $join) {
        $join->on('c.investigador_id', '=', 'b.id')
          ->where('c.estado', '=', 'Vigente');
      })
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
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
          $item->renacyt || $item->renacyt != "" ? 'Tiene renacyt' : 'No tiene renacyt',
          $item->cdi ? 'Tiene CDI' : 'No tiene CDI',
        ];
        $item->disabled = $item->renacyt && $item->renacyt != "" && $item->cdi ? false : true;
        return $item;
      });

    return $listado;
  }

  public function listadoDocentes(Request $request) {

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Titular')
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->facultad,
        ];
        return $item;
      });

    return $listado;
  }

  public function listadoTesistas(Request $request) {

    $grupos = DB::table('Proyecto_integrante AS a')
      ->select(['a.grupo_id'])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->whereIn('a.proyecto_integrante_tipo_id', [56, 57, 58])
      ->pluck('a.grupo_id')
      ->values();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Repo_sum AS c', 'c.codigo_alumno', '=', 'b.codigo')
      ->leftJoin('Grupo AS e', 'e.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS g', 'g.investigador_id', '=', 'b.id')
      ->leftJoin('Proyecto_integrante_tipo AS h', function (JoinClause $join) {
        $join->on('h.id', '=', 'g.proyecto_integrante_tipo_id')
          ->where('h.nombre', '=', 'Tesista');
      })
      ->leftJoin('Proyecto AS i', 'i.id', '=', 'g.proyecto_id') // Se mantiene el join sin condiciones adicionales
      ->where(function ($query) {
        $query->where('i.estado', 1)
          ->orWhereNull('i.id'); // Permitir registros sin proyecto
      })
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'b.tipo',
        'a.investigador_id',
        'e.id AS grupo_id',
        'a.id AS grupo_integrante_id',
        'e.grupo_nombre_corto AS labelTag',
        'f.nombre AS facultad',
        DB::raw("CASE
              WHEN c.programa LIKE 'E.P.%' THEN 'Licenciatura o Segunda Especialidad'
              WHEN c.programa LIKE 'Maest%' THEN 'Maestría'
              WHEN c.programa LIKE 'Doct%' THEN 'Doctorado'
              ELSE 'Licenciatura o Segunda Especialidad'
          END AS tipo_programa"),
        DB::raw("COUNT(h.id) AS cantidad_tesista")
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
          "Participaciones como tesista: " . $item->cantidad_tesista
        ];
        $item->disabled = $item->cantidad_tesista > 0;
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

  public function listadoGestor(Request $request) {
    $investigadores = DB::table('Usuario_investigador AS a')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', apellido1, ' ', apellido2, ', ', nombres) AS value"),
        'id AS investigador_id',
        'doc_numero',
        'apellido1',
        'apellido2',
        'nombres'
      )
      ->where('tipo', '=', 'EXTERNO')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function agregarIntegrante(Request $request) {
    $count = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->input('id'))
      ->where('investigador_id', '=', $request->input('investigador_id'))
      ->count();

    if ($count == 0) {
      if ($request->input('proyecto_integrante_tipo_id') == 57) {
        $deudas = DB::table('view_deudores')
          ->where('investigador_id', '=', $request->input('investigador_id'))
          ->count();

        if ($deudas > 0) {
          return ['message' => 'warning', 'detail' => 'El investigador seleccionado tiene ' . $deudas . ' deudas'];
        }
      }

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

  public function agregarGestor(Request $request) {
    if ($request->input('tipo') == "Nuevo") {

      $investigador_id = DB::table('Usuario_investigador')
        ->insertGetId([
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'nombres' => $request->input('nombres'),
          'sexo' => $request->input('sexo'),
          'institucion' => $request->input('institucion'),
          'tipo' => 'Externo',
          'pais' => $request->input('pais'),
          'email1' => $request->input('email1'),
          'doc_tipo' => $request->input('doc_tipo'),
          'doc_numero' => $request->input('doc_numero'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $investigador_id,
          'proyecto_integrante_tipo_id' => 94,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    } else {

      $cuenta = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('investigador_id', '=', $request->input('investigador_id'))
        ->count();

      if ($cuenta > 0) {
        return ['message' => 'error', 'detail' => 'Esta persona ya figura como integrante del proyecto'];
      }

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $request->input('investigador_id'),
          'proyecto_integrante_tipo_id' => 94,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    return ['message' => 'success', 'detail' => 'Integrante añadido correctamente'];
  }

  public function eliminarIntegrante(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('proyecto_integrante_id', '=', $request->query('id'))
      ->delete();

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

  public function agregarPartida(Request $request) {
    $date = Carbon::now();

    DB::table('Proyecto_presupuesto')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'partida_id' => $request->input('partida')["value"],
        'monto' => $request->input('monto'),
        'justificacion' => $request->input('justificacion'),
        'created_at' => $date,
        'updated_at' => $date,
      ]);

    return ['message' => 'success', 'detail' => 'Partida agregada correctamente'];
  }

  public function eliminarPartida(Request $request) {
    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Partida eliminada correctamente'];
  }

  public function actualizarPartida(Request $request) {
    $date = Carbon::now();

    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'partida_id' => $request->input('partida')["value"],
        'monto' => $request->input('monto'),
        'justificacion' => $request->input('justificacion'),
        'updated_at' => $date,
      ]);

    return ['message' => 'info', 'detail' => 'Partida actualizada correctamente'];
  }

  public function validarPresupuesto(Request $request) {
    $alerta = [];

    $partidas = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida_proyecto AS b', function (JoinClause $join) {
        $join->on('b.partida_id', '=', 'a.partida_id')
          ->where('b.tipo_proyecto', '=', 'PMULTI');
      })
      ->leftJoin('Partida_proyecto_grupo AS c', 'c.partida_proyecto_id', '=', 'b.id')
      ->leftJoin('Partida_grupo AS d', 'd.id', '=', 'c.partida_grupo_id')
      ->select([
        'd.nombre',
        'd.monto_max',
        DB::raw("SUM(a.monto) AS total")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->groupBy('d.id')
      ->get();

    foreach ($partidas as $item) {
      if ($item->monto_max < $item->total && $item->nombre != null) {
        $alerta[] = $item->nombre . ": " . $item->monto_max;
      }
    };

    if (sizeof($alerta) == 0) {
      return ['message' => 'info', 'detail' => 'Su proyecto respeta los límites de la directiva'];
    } else {
      return ['message' => 'warning', 'detail' => 'El presupuesto presenta excesos en la(s) siguiente(s) categoría(s). ' . implode(',', $alerta) . '; para mayor detalle revisar la directiva correspondiente.', $alerta];
    }
  }

  public function verificar7(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $docs = DB::table('Proyecto_doc')
      ->select([
        'id',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->where('tipo', '=', 25)
      ->where('categoria', '=', 'documento')
      ->where('nombre', '=', 'Documento de colaboración externa')
      ->get();

    return ['estado' => true, 'docs' => $docs];
  }

  public function agregarDoc(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'tipo' => 25,
          'categoria' => 'documento',
          'nombre' => 'Documento de colaboración externa',
          'comentario' => $request->input('comentario'),
          'archivo' => $name,
          'estado' => 1,
        ]);

      return ['message' => 'success', 'detail' => 'Documento añadido'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function eliminarDoc(Request $request) {
    DB::table('Proyecto_doc')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Documento eliminado correctamente'];
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
      ->leftJoin('Proyecto_descripcion AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('codigo', '=', 'tipo_investigacion');
      })
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
      ->leftJoin('Linea_investigacion AS f', 'f.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Ocde AS g', 'g.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_descripcion AS j', function (JoinClause $join) {
        $join->on('j.proyecto_id', '=', 'a.id')
          ->where('j.codigo', '=', 'area_tematica');
      })
      ->leftJoin('Proyecto_descripcion AS k', function (JoinClause $join) {
        $join->on('k.proyecto_id', '=', 'a.id')
          ->where('k.codigo', '=', 'objetivo_ods');
      })
      ->leftJoin('Ods AS l', 'l.id', '=', 'k.detalle')
      ->select([
        'c.grupo_nombre',
        'd.nombre AS facultad',
        'e.nombre AS area',
        'j.detalle AS area_tematica',
        'g.linea AS ocde',
        'a.palabras_clave',
        'a.titulo',
        'f.nombre AS linea',
        'l.descripcion AS ods',
        'a.localizacion',
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

    $docs = DB::table('Proyecto_doc')
      ->select([
        'comentario',
        'archivo',
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->where('tipo', '=', 25)
      ->where('nombre', '=', 'Documento de colaboración externa')
      ->get();

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
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'c.nombre AS tipo_integrante',
        'd.nombre AS facultad',
        'g.grupo_nombre',
        DB::raw("CASE
          WHEN e.key IS NOT NULL THEN 'Sí'
          ELSE 'No' END AS compromiso")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->groupBy('b.id')
      ->get();

    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.actividad',
        'a.justificacion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.partida',
        'a.justificacion',
        'b.tipo',
        'a.monto',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('investigador.convocatorias.pmulti', [
      'proyecto' => $proyecto,
      'docs' => $docs,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);
    return $pdf->stream();
  }

  public function enviar(Request $request) {
    //  Verificar autorización de grupo
    $req1 = DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 6)
      ->where('autorizacion_grupo', '=', 1)
      ->count();

    if ($req1 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita que el coordinador de su grupo autorice la propuesta de proyecto'];
    }

    $count = DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 6)
      ->update([
        'estado' => 5,
        'updated_at' => Carbon::now()
      ]);

    if ($count > 0) {
      return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
    } else {
      return ['message' => 'error', 'detail' => 'Ya ha enviado su solicitud'];
    }
  }
}

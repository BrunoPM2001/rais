<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PsinfinvController extends S3Controller {
  //  Verifica las condiciones para participar
  public function verificar(Request $request, $proyecto_id = null) {
    $errores = [];
    $req4 = null;

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
        ->where('b.tipo_proyecto', '=', 'PSINFINV')
        ->count();

      $req2 == 0 && $errores[] = "No figura como responsable del proyecto";
    } else {
      $req3 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PSINFINV')
        ->where('b.periodo', '=', 2024)
        ->where('b.estado', '!=', 6)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->count();

      $req3 > 0 && $errores[] = "Ya ha enviado un proyecto";

      $req4 = DB::table('Grupo_integrante AS a')
        ->join('Proyecto AS b', 'b.grupo_id', '=', 'a.grupo_id')
        ->where('a.condicion', '=', 'Titular')
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('b.tipo_proyecto', '=', 'PSINFINV')
        ->where('b.periodo', '=', 2024)
        ->count();

      $req4 > 0 && $errores[] = "Su grupo de investigación ya está presentando un proyecto";

      $detail = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->select([
          'b.id',
          'b.step'
        ])
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PSINFINV')
        ->where('b.periodo', '=', 2024)
        ->where('b.estado', '=', 6)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();
    }

    if (!empty($errores)) {
      return ['estado' => false, 'errores' => $errores];
    } else {
      if ($req4 == null) {
        return ['estado' => true];
      } else {
        return ['estado' => true, 'id' => $detail->id, 'step' => $detail->step];
      }
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

    if ($request->query('id')) {
      $proyecto = DB::table('Proyecto AS a')
        ->join('Proyecto_descripcion AS b', function (JoinClause $join) {
          $join->on('a.id', '=', 'b.proyecto_id')
            ->where('codigo', '=', 'tipo_investigacion');
        })
        ->select([
          'a.linea_investigacion_id',
          'a.ocde_id',
          'a.titulo',
          'a.localizacion',
          'b.detalle AS tipo_investigacion'
        ])
        ->where('a.id', '=', $request->query('id'))
        ->first();

      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'proyecto' => $proyecto,
        'ocde' => $ocde,
      ];
    } else {
      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'ocde' => $ocde,
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
          'codigo' => 'tipo_investigacion',
        ], [
          'detalle' => $request->input('tipo_investigacion')["value"],
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
          'tipo_proyecto' => 'PSINFINV',
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
          'codigo' => 'tipo_investigacion',
          'detalle' => $request->input('tipo_investigacion')["value"],
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'proyecto_integrante_tipo_id' => 7,
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

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'resumen_esperado',
        'antecedentes',
        'objetivos_generales',
        'objetivos_especificos',
        'justificacion',
        'hipotesis',
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

    $archivo1 = DB::table('File AS a')
      ->select([
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('tabla', '=', 'Proyecto')
      ->where('tabla_id', '=', $request->query('id'))
      ->where('bucket', '=', 'proyecto-doc')
      ->where('recurso', '=', 'METODOLOGIA_TRABAJO')
      ->where('estado', '=', 20)
      ->first();

    $archivo2 = DB::table('File AS a')
      ->select([
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('tabla', '=', 'Proyecto')
      ->where('tabla_id', '=', $request->query('id'))
      ->where('bucket', '=', 'proyecto-doc')
      ->where('recurso', '=', 'PROPIEDAD_INTELECTUAL')
      ->where('estado', '=', 20)
      ->first();

    return [
      'estado' => true,
      'descripcion' => $descripcion,
      'palabras_clave' => $palabras_clave->palabras_clave,
      'archivos' => [
        'metodologia' => $archivo1?->url,
        'propiedad' => $archivo2?->url,
      ]
    ];
  }

  public function registrar2(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'resumen_ejecutivo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('resumen_ejecutivo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'resumen_esperado', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('resumen_esperado')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'antecedentes', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('antecedentes')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'objetivos_generales', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('objetivos_generales')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'objetivos_especificos', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('objetivos_especificos')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'justificacion', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('justificacion')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'hipotesis', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('hipotesis')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'metodologia_trabajo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('metodologia_trabajo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'referencias_bibliograficas', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('referencias_bibliograficas')]);

    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'step' => 3,
      ]);

    if ($request->hasFile('file1')) {
      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);

      DB::table('File')
        ->where('tabla', '=', 'Proyecto')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('recurso', '=', 'METODOLOGIA_TRABAJO')
        ->where('estado', '=', 20)
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Proyecto',
          'tabla_id' => $request->input('id'),
          'bucket' => 'proyecto-doc',
          'key' => $name,
          'recurso' => 'METODOLOGIA_TRABAJO',
          'estado' => 20,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    if ($request->hasFile('file2')) {
      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
      $this->uploadFile($request->file('file2'), "proyecto-doc", $name);

      DB::table('File')
        ->where('tabla', '=', 'Proyecto')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('recurso', '=', 'PROPIEDAD_INTELECTUAL')
        ->where('estado', '=', 20)
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Proyecto',
          'tabla_id' => $request->input('id'),
          'bucket' => 'proyecto-doc',
          'key' => $name,
          'recurso' => 'PROPIEDAD_INTELECTUAL',
          'estado' => 20,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar3(Request $request) {
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

    return [
      'estado' => true,
      'data' => $data,
    ];
  }

  public function verificar4(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis',
        'a.excluido'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return ['estado' => true, 'integrantes' => $integrantes];
  }

  public function listadoGrupoDocente(Request $request) {
    $grupo = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        DB::raw("CONCAT(b.tipo, ' - ' , a.condicion, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'a.grupo_id'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('b.tipo', 'LIKE', '%DOCENTE%')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function listadoGrupoExterno(Request $request) {
    $grupo = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        DB::raw("CONCAT(b.tipo, ' - ' , a.condicion, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'a.grupo_id'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('b.tipo', '=', 'Externo')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function listadoGrupoEstudiante(Request $request) {
    $grupo = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        DB::raw("CONCAT(b.tipo, ' - ' , a.condicion, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'a.grupo_id'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('b.tipo', 'LIKE', 'Estudiante%')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function agregarIntegrante(Request $request) {
    $count = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->input('id'))
      ->where('investigador_id', '=', $request->input('investigador_id'))
      ->count();

    if ($count == 0) {

      if ($request->input('tipo_tesis') == null) {
        DB::table('Proyecto_integrante')
          ->insert([
            'proyecto_id' => $request->input('id'),
            'grupo_id' => $request->input('grupo_id'),
            'investigador_id' => $request->input('investigador_id'),
            'grupo_integrante_id' => $request->input('grupo_integrante_id'),
            'proyecto_integrante_tipo_id' => $request->input('proyecto_integrante_tipo_id'),
            'contribucion' => $request->input('contribucion'),
            'excluido' => 'Incluido',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
      } else {
        DB::table('Proyecto_integrante')
          ->insert([
            'proyecto_id' => $request->input('id'),
            'grupo_id' => $request->input('grupo_id'),
            'investigador_id' => $request->input('investigador_id'),
            'grupo_integrante_id' => $request->input('grupo_integrante_id'),
            'proyecto_integrante_tipo_id' => $request->input('proyecto_integrante_tipo_id'),
            'contribucion' => $request->input('contribucion'),
            'tipo_tesis' => $request?->input('tipo_tesis')["value"],
            'titulo_tesis' => $request->input('titulo_tesis'),
            'excluido' => 'Incluido',
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
      ->select([
        'a.titulo',
        'c.grupo_nombre',
        'e.nombre AS area',
        'd.nombre AS facultad',
        'f.nombre AS linea',
        'b.detalle AS tipo_investigacion',
        'a.localizacion',
        'g.linea AS ocde',
        'a.palabras_clave'
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

    $pdf = Pdf::loadView('investigador.convocatorias.psinfinv', [
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

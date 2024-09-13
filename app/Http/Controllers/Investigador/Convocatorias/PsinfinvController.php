<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PsinfinvController extends S3Controller {
  //  Verifica las condiciones para participar
  public function verificar(Request $request, $proyecto_id = null) {
    $errores = [];

    //  Ser coordinador de un grupo de investigación
    $req1 = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', function (JoinClause $join) {
        $join->on('b.investigador_id', '=', 'a.id')
          ->where('b.cargo', '=', 'Coordinador');
      })
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req1 == 0 && $errores[] = "Necesita ser coordinador de un grupo de investigación";

    if ($proyecto_id != null) {
      $req2 = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $proyecto_id)
        ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('condicion', '=', 'Responsable')
        ->count();

      $req2 == 0 && $errores[] = "No figura como responsable del proyecto";
    }

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
    }

    $datos = DB::table('Usuario_investigador AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS c', 'c.id', '=', 'b.area_id')
      ->join('Grupo_integrante AS d', function (JoinClause $join) {
        $join->on('d.investigador_id', '=', 'a.id')
          ->where('d.cargo', '=', 'Coordinador');
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
            ->where('b.cargo', '=', 'Coordinador');
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
}

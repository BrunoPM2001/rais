<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoFEXController extends S3Controller {

  public function listado(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Proyecto_integrante_tipo AS c', 'b.proyecto_integrante_tipo_id', '=', 'c.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'a.tipo_proyecto',
        'c.nombre AS condicion',
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
        'a.periodo',
        DB::raw("'no' AS antiguo")
      )
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_proyecto', ['PFEX'])
      ->orderByDesc('a.periodo')
      ->get();

    $proyectos_antiguos = DB::table('Proyecto_H AS a')
      ->leftJoin('Proyecto_integrante_H AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.tipo AS tipo_proyecto',
        'b.condicion',
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
        'a.periodo',
        DB::raw("'si' AS antiguo")
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.tipo', '=', 'SIN-CON')
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos->merge($proyectos_antiguos);
  }

  public function lineasUnmsm() {
    $lineas = DB::table('Linea_investigacion')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', nombre) AS label")
      ])
      ->where('estado', '=', 1)
      ->orderBy('codigo')
      ->get();

    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label"),
        'parent_id'
      ])
      ->get();

    $paises = DB::table('Pais')
      ->select([
        'code AS value',
        'name AS label'
      ])->get();

    return [
      'lineas' => $lineas,
      'ocde' => $ocde,
      'paises' => $paises,
    ];
  }

  public function datosPaso1(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        'titulo',
        'linea_investigacion_id',
        'ocde_id',
        'aporte_unmsm',
        'aporte_no_unmsm',
        'financiamiento_fuente_externa',
        'entidad_asociada',
        'resolucion_rectoral'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $extras = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', ['moneda_tipo', 'fuente_financiadora', 'otra_fuente', 'web_fuente', 'participacion_unmsm', 'pais'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $lineas = $this->lineasUnmsm();

    return [
      'proyecto' => $proyecto,
      'extras' => $extras
    ] + $lineas;
  }

  public function datosPaso2(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        DB::raw("COALESCE(fecha_inicio, '') AS fecha_inicio"),
        DB::raw("COALESCE(fecha_fin, '') AS fecha_fin"),
        DB::raw("COALESCE(palabras_clave, '') AS palabras_clave"),
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $extras = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', ['resumen', 'objetivos', 'duracion_annio', 'duracion_mes', 'duracion_dia'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    return [
      'proyecto' => $proyecto,
      'extras' => $extras
    ];
  }

  public function datosPaso3(Request $request) {
    $documentos = DB::table('Proyecto_fex_doc AS a')
      ->select([
        'id',
        'doc_tipo',
        'nombre',
        'comentario',
        'fecha',
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    return $documentos;
  }

  public function datosPaso4(Request $request) {
    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->join('Proyecto_integrante_tipo AS d', 'd.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'd.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        'b.doc_numero',
        DB::raw("IF(a.condicion = 'Responsable', 'Sí', 'No') AS responsable"),
        'c.nombre AS facultad',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return $miembros;
  }

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();
    $id = 0;
    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($request->input('id')) {
      $id = $request->input('id');

      if ($this->validar($id, $investigador_id)) {
        return response()->json(['error' => 'Unauthorized'], 401);
      }

      DB::table('Proyecto')
        ->where('id', '=', $id)
        ->update([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"] ?? null,
          'ocde_id' => $request->input('ocde_3')["value"] ?? null,
          'titulo' => $request->input('titulo'),
          'resolucion_rectoral' => $request->input('resolucion_rectoral'),
          'aporte_unmsm' => $request->input('aporte_unmsm'),
          'aporte_no_unmsm' => $request->input('aporte_no_unmsm'),
          'financiamiento_fuente_externa' => $request->input('financiamiento_fuente_externa'),
          'entidad_asociada' => $request->input('entidad_asociada'),
          'monto_asignado' =>
          $request->input('aporte_unmsm')
            + $request->input('aporte_no_unmsm')
            + $request->input('financiamiento_fuente_externa')
            + $request->input('entidad_asociada'),
          'step' => 2,
          'updated_at' => $date,
        ]);
    } else {
      $id = DB::table('Proyecto')
        ->insertGetId([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"] ?? null,
          'ocde_id' => $request->input('ocde_3')["value"] ?? null,
          'titulo' => $request->input('titulo'),
          'fecha_inscripcion' => $date,
          'tipo_proyecto' => 'PFEX',
          'periodo' => $date->format("Y"),
          'resolucion_rectoral' => $request->input('resolucion_rectoral'),
          'aporte_unmsm' => $request->input('aporte_unmsm'),
          'aporte_no_unmsm' => $request->input('aporte_no_unmsm'),
          'financiamiento_fuente_externa' => $request->input('financiamiento_fuente_externa'),
          'entidad_asociada' => $request->input('entidad_asociada'),
          'monto_asignado' =>
          $request->input('aporte_unmsm')
            + $request->input('aporte_no_unmsm')
            + $request->input('financiamiento_fuente_externa')
            + $request->input('entidad_asociada'),
          'estado' => 6,
          'step' => 2,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $investigador_id,
          'proyecto_integrante_tipo_id' => 44,
          'condicion' => 'Responsable',
          'created_at' => $date,
          'updated_at' => $date,
        ]);
    }

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'moneda_tipo'
      ], [
        'detalle' => $request->input('moneda_tipo')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'fuente_financiadora'
      ], [
        'detalle' => $request->input('fuente_financiadora')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'otra_fuente'
      ], [
        'detalle' => $request->input('otra_fuente') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'web_fuente'
      ], [
        'detalle' => $request->input('web_fuente') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'participacion_unmsm'
      ], [
        'detalle' => $request->input('participacion_unmsm')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'pais'
      ], [
        'detalle' => $request->input('pais')["value"] ?? ""
      ]);

    return ['message' => 'success', 'id' => $id];
  }

  public function registrarPaso2(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $date = Carbon::now();
    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'updated_at' => $date
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'resumen'
      ], [
        'detalle' => $request->input('resumen')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'objetivos'
      ], [
        'detalle' => $request->input('objetivos')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_annio'
      ], [
        'detalle' => $request->input('años') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_mes'
      ], [
        'detalle' => $request->input('meses') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_dia'
      ], [
        'detalle' => $request->input('dias') ?? ""
      ]);
  }

  //  Paso 3
  public function registrarPaso3(Request $request) {
    $date = Carbon::now();
    $date_name = $date->format('Ymd-His');

    if ($request->hasFile('file')) {

      $nameFile = $request->input('id') . "/" . $request->input('doc_tipo') . "_" . $date_name . "." . $request->file('file')->getClientOriginalExtension();

      $this->uploadFile($request->file('file'), "proyecto-fex-doc", $nameFile);

      DB::table('Proyecto_fex_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'doc_tipo' => $request->input('doc_tipo'),
          'nombre' => $request->input('nombre'),
          'comentario' => $request->input('comentario'),
          'bucket' => 'proyecto-fex-doc',
          'key' => $nameFile,
          'fecha' => $date
        ]);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function updateDoc(Request $request) {
    DB::table('Proyecto_fex_doc')
      ->where([
        'id' => $request->input('id')
      ])
      ->update([
        'nombre' => $request->input('nombre'),
        'comentario' => $request->input('comentario')
      ]);

    return ['message' => 'info', 'detail' => 'Datos actualizados correctamente'];
  }

  public function deleteDoc(Request $request) {
    DB::table('Proyecto_fex_doc')
      ->where([
        'id' => $request->query('id')
      ])
      ->delete();

    return ['message' => 'info', 'detail' => 'Archivo eliminado correctamente'];
  }

  public function validar($proyecto_id, $investigador_id) {
    $cuenta = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $proyecto_id)
      ->where('investigador_id', '=', $investigador_id)
      ->count();

    return $cuenta > 0 ? false : true;
  }
}

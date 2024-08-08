<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectosFEXController extends S3Controller {
  public function listado() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $moneda = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'moneda_tipo');

    $participacion_ummsm = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'participacion_ummsm');

    $fuente_financiadora = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'fuente_financiadora');

    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($moneda, 'moneda', 'moneda.proyecto_id', '=', 'a.id')
      ->leftJoinSub($participacion_ummsm, 'p_unmsm', 'p_unmsm.proyecto_id', '=', 'a.id')
      ->leftJoinSub($fuente_financiadora, 'fuente', 'fuente.proyecto_id', '=', 'a.id')
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
        'a.estado'
      )
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->groupBy('a.id')
      ->get();

    return $proyectos;
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

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();
    $id = 0;

    if ($request->input('id')) {
      $id = $request->input('id');
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
}

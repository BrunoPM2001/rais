<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectosFEXController extends Controller {
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

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();

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

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'moneda_tipo',
        'detalle' => $request->input('moneda')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'fuente_financiadora',
        'detalle' => $request->input('fuente')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'otra_fuente',
        'detalle' => $request->input('fuente_input') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'web_fuente',
        'detalle' => $request->input('sitio') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'participacion_unmsm',
        'detalle' => $request->input('participacion')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'pais',
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
}

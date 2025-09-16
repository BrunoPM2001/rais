<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipamientoCientificoController extends S3Controller {

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
          WHEN 2 THEN 'Observado'
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
      ->whereIn('a.tipo_proyecto', ['ECI'])
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos;
  }

  public function formatoDj(Request $request) {

    $proyecto = DB::table('view_declaracion_jurada AS a')
      ->select(
        'a.tipo_proyecto',
        'a.periodo',
        'a.responsable',
        'a.facultad',
        'a.codigo_docente',
        'a.dni',
        'a.categoria',
        'a.clase',
        'a.grupo_nombre_corto',
        'a.grupo_nombre',
        'a.codigo_proyecto',
        'a.titulo_proyecto',
        'a.total_presupuesto',
        'a.subvencion_investigador'
      )
      ->where('a.proyecto_id', '=', $request->query('proyectoId'))
      ->first();

    $tipo = 'Programa de Equipamiento Científico para la Investigación de la UNMSM';

    $pdf = Pdf::loadView('investigador.dj.eci', [
      'proyecto' => $proyecto,
      'tipo' => $tipo,
      'periodo' => $proyecto->periodo,
    ]);
    return $pdf->stream();
  }

  public function uploadDocumento(Request $request) {


    if ($request->hasFile('file')) {
      $proyecto = DB::table('view_declaracion_jurada AS dj')
        ->select(
          'dj.tipo_proyecto',
          'dj.periodo',
          'dj.responsable',
        )
        ->where('dj.proyecto_id', '=', $request->input('proyecto_id'))
        ->first();

      $nameFile = $proyecto->periodo . '/' . $proyecto->tipo_proyecto . '/' . str_replace(' ', '_', $proyecto->responsable)
        . '.' . $request->file('file')->getClientOriginalExtension();

      $this->uploadFile($request->file('file'), "dj-subvencion", $nameFile);

      DB::table('File')->insert([
        'tabla_id'   => $request->input('proyecto_id'),
        'tabla' => 'Proyecto',
        'bucket' => 'dj-subvencion',
        'key' => $nameFile,
        'recurso' => 'DJ_FIRMADA',
        'estado' => '20',
        'created_at'    => now(),
        'updated_at'    => now(),
      ]);

      DB::table('Proyecto')
        ->where('id', '=', $request->input('proyecto_id'))
        ->update([
          'dj_aceptada' => 1,
          'updated_at' => Carbon::now()
        ]);

      return ['message' => 'success', 'detail' => 'Declaración Jurada enviada correctamente'];
    } else {
      return ['message' => 'warning', 'detail' => 'No ha cargado ningún achivo'];
    }
  }

  public function djFirmada(Request $request) {

    $djFirmada = DB::table('File as doc')
      ->select(
        'tabla_id AS proyecto_id',
        DB::raw("CONCAT('/minio/dj-subvencion/', doc.key) AS url")
      )
      ->where('tabla_id', '=', $request->input('proyecto_id'))
      ->where('tabla', '=', 'Proyecto')
      ->where('recurso', '=', 'DJ_FIRMADA')
      ->where('bucket', '=', 'dj-subvencion')
      ->where('estado', '=', '20')
      ->first();

    return $djFirmada;
  }
}

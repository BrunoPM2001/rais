<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\S3Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProyectoConFinanciamientoController extends S3Controller {

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
        'a.dj_aceptada',
        DB::raw("'no' AS antiguo")
      )
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_proyecto', ['PCONFIGI', 'PCONFIGI-INV', 'PINTERDIS', 'PRO-CTIE'])
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
        DB::raw("'si' AS antiguo")
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.tipo', '=', 'CON-CON')
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos->merge($proyectos_antiguos);
  }

  public function formatoDj(Request $request) {
    $proyectoId = $request->query('proyectoId');

    $proyecto = DB::table('view_declaracion_jurada AS dj')
      ->select(
        'dj.tipo_proyecto',
        'dj.periodo',
        'dj.responsable',
        'dj.facultad',
        'dj.codigo_docente',
        'dj.dni',
        'dj.categoria',
        'dj.clase',
        'dj.grupo_nombre_corto',
        'dj.grupo_nombre',
        'dj.codigo_proyecto',
        'dj.titulo_proyecto',
        'dj.total_presupuesto',
        'dj.subvencion_investigador'
      )
      ->where('dj.proyecto_id', '=', $proyectoId)
      ->first();

    switch ($proyecto->tipo_proyecto) {
      case 'PCONFIGI':
        $tipo = 'DECLARACIÓN JURADA DE CUMPLIMIENTO PARA RECIBIR ASIGNACIÓN FINANCIERA AL PROYECTO DE INVESTIGACIÓN PARA GRUPOS DE INVESTIGACIÓN DE LA UNMSM';
        break;
      case 'PCONFIGI-INV':
        $tipo = 'Proyectos de Innovación para  Grupos de Investigación “INNOVA SAN MARCOS';
        break;
      case 'PRO-CTIE':
        $tipo = 'Proyectos de Ciencia, Tecnología, Innovación y Emprendimiento (PRO-CTIE) para Estudiantes de la UNMSM';
        break;
      case 'ECI':
        $tipo = 'Programa de Equipamiento Científico para la Investigación de la UNMSM';
        break;
      case 'PSINFIPU':
        $tipo = 'Proyectos de Publicación Académica para Grupos de Investigación';
        break;
      default:
        $tipo = 'Tipo de Proyecto Desconocido';
    }

    $pdf = Pdf::loadView(
      'investigador.dj.pconfigi',
      [
        'proyecto' => $proyecto,
        'tipo' => $tipo,
        'periodo' => $proyecto->periodo,
      ]
    );
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

      $dj_doc = DB::table('Declaracion_jurada_doc')->insert([
        'proyecto_id'   => $request->input('proyecto_id'),
        'periodo'       => $proyecto->periodo,
        'tipo_proyecto' => $proyecto->tipo_proyecto,
        'responsable'   => $proyecto->responsable,
        'url'           => $nameFile, // Esto es la ruta relativa en MinIO
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

    $djFirmada = DB::table('Declaracion_jurada_doc as doc')
      ->select(
        'proyecto_id',
        DB::raw("CONCAT('/minio/dj-subvencion/', url) AS url")
      )
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->first();

    return $djFirmada;
  }
}

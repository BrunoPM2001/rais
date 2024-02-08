<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class Evaluacion_facultadController extends Controller {
  public function getConvocatorias() {
    $convocatorias = DB::table('Evaluacion_facultad AS a')
      ->select(
        'tipo_proyecto',
        'periodo',
        DB::raw('COUNT(*) AS facultades'),
        DB::raw('SUM(cupos) AS cupos'),
        DB::raw('MIN(fecha_inicio) AS fecha_inicio'),
        DB::raw('MAX(fecha_fin) AS fecha_fin'),
        DB::raw('MIN(evaluacion_fecha_inicio) AS fecha_inicio_evaluacion'),
        DB::raw('MAX(evaluacion_fecha_fin) AS fecha_fin_evaluacion')
      )
      ->groupBy('tipo_proyecto', 'periodo')
      ->get();

    return ['data' => $convocatorias];
  }

  public function getDetalleConvocatoria($periodo, $tipo_proyecto) {
    $convocatorias = DB::table('Evaluacion_evaluador AS a')
      ->join('Evaluacion_facultad AS b', 'b.id', '=', 'a.evaluacion_facultad_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select(
        'b.id',
        'c.nombre as facultad',
        'b.cupos',
        'b.puntaje_minimo',
        'b.fecha_inicio',
        'b.fecha_fin',
        'b.evaluacion_fecha_inicio',
        'b.evaluacion_fecha_fin',
        DB::raw('COUNT(*) AS evaluadores')
      )
      ->where('b.periodo', '=', $periodo)
      ->where('b.tipo_proyecto', '=', $tipo_proyecto)
      ->groupBy('a.evaluacion_facultad_id')
      ->get();

    return ['data' => $convocatorias];
  }

  public function getEvaluadoresConvocatoria($id) {
    $evaluadores = DB::table('Evaluacion_evaluador AS a')
      ->join('Usuario_evaluador AS b', 'b.id', '=', 'a.usuario_evaluador_id')
      ->select(
        'b.tipo',
        'b.apellidos',
        'b.nombres',
        'b.institucion',
        'b.cargo',
        'b.codigo_regina'
      )
      ->where('a.evaluacion_facultad_id', '=', $id)
      ->get();

    return ['data' => $evaluadores];
  }

  public function main() {
    return view('admin.facultad.convocatorias');
  }
}

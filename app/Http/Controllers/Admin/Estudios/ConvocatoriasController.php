<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ConvocatoriasController extends Controller {
  public function listarConvocatorias() {
    $convocatorias = DB::table('Convocatoria')
      ->select(
        'id',
        'tipo',
        'fecha_inicial',
        'fecha_final',
        'fecha_corte',
        'periodo',
        'estado'
      )
      ->where('evento', '=', 'registro')
      ->get();

    return ['data' => $convocatorias];
  }

  public function getOneConvocatoria($parent_id) {
    $convocatoria = DB::table('Convocatoria')
      ->select(
        'id',
        'tipo',
        'evento',
        'fecha_inicial',
        'fecha_final',
        'fecha_corte',
        'periodo',
        'convocatoria',
        'estado'
      )
      ->where('id', '=', $parent_id)
      ->orWhere('parent_id', '=', $parent_id)
      ->get();

    return ['data' => $convocatoria];
  }

  public function listaEvaluaciones() {
    $evaluaciones = DB::table('Evaluacion_template')
      ->select(
        'id',
        'tipo',
        'periodo',
        'estado'
      )
      ->get();

    return ['data' => $evaluaciones];
  }

  public function verCriteriosEvaluacion($evaluacion_id) {
    $criterios = DB::table('Evaluacion_template_opcion AS a')
      ->join('Evaluacion_template AS b', 'b.id', '=', 'a.evaluacion_template_id')
      ->select(
        'a.id',
        'a.opcion',
        'a.puntaje_max',
        'a.nivel',
        'a.orden',
        'a.editable',
        'a.otipo',
        'a.puntos_adicionales'
      )
      ->where('b.id', '=', $evaluacion_id)
      ->orderBy('a.orden')
      ->get();

    $evaluacion = DB::table('Evaluacion_template')
      ->select(
        'id',
        'tipo',
        'periodo',
        'estado'
      )
      ->where('id', '=', $evaluacion_id)
      ->get();

    return ['evaluacion' => $evaluacion, 'criterios' => $criterios];
  }
}

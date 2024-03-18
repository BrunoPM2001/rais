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
        'estado'
      )
      ->get();

    return ['data' => $evaluaciones];
  }
}

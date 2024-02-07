<?php

namespace App\Http\Controllers;

use App\Models\Evaluacion_facultad;
use Illuminate\Http\Request;

class Evaluacion_facultadController extends Controller {
  public function getConvocatorias() {
    $convocatorias = Evaluacion_facultad::select(
      'tipo_proyecto',
      'periodo',
      Evaluacion_facultad::raw('COUNT(*) AS facultades'),
      Evaluacion_facultad::raw('SUM(cupos) AS cupos'),
      Evaluacion_facultad::raw('MIN(fecha_inicio) AS fecha_inicio'),
      Evaluacion_facultad::raw('MAX(fecha_fin) AS fecha_fin')
    )
      ->groupBy('tipo_proyecto', 'periodo')
      ->get();

    return ['data' => $convocatorias];
  }

  public function getDetalleConvocatoria($periodo, $tipo_proyecto) {
    $convocatorias = Evaluacion_facultad::select(
      'facultad_id',
      'cupos',
      'puntaje_minimo',
      'fecha_inicio',
      'fecha_fin',
      'evaluacion_fecha_inicio',
      'evaluacion_fecha_fin'
    )
      ->where('periodo', $periodo)
      ->where('tipo_proyecto', $tipo_proyecto)
      ->get();
    return ['data' => $convocatorias];
  }
}

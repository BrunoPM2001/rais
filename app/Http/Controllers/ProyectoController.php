<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller {

  public function getAllEvaluadores() {
    $proyectos = DB::table('Proyecto AS p')
      ->join('Linea_investigacion AS l', 'l.id', '=', 'p.linea_investigacion_id')
      ->join('Facultad AS f', 'f.id', '=', 'p.facultad_id')
      ->leftJoin('Proyecto_evaluacion AS e', 'e.proyecto_id', '=', 'p.id')
      ->leftJoin('Usuario_evaluador AS u', 'e.evaluador_id', '=', 'u.id')
      ->select(
        'p.id',
        'p.tipo_proyecto',
        'l.nombre AS linea',
        'f.nombre AS facultad',
        'p.titulo',
        DB::raw('GROUP_CONCAT( CONCAT(u.apellidos, ", ", u.nombres) SEPARATOR "\n") AS evaluadores')
      )
      ->where('p.periodo', '=', 2024)
      ->where('p.step', '=', 8)
      ->where('p.estado', '<', 6)
      ->where('p.estado', '>', 1)
      ->groupBy('p.id')
      ->get();

    return ['data' => $proyectos];
  }

  public function getAllProyectosEvaluados($periodo, $tipo_proyecto) {
    $proyectos = DB::table('Proyecto_evaluacion AS pe')
      ->join('Proyecto AS p', 'p.id', '=', 'pe.proyecto_id')
      ->join('Evaluacion_proyecto AS e', function ($join) {
        $join->on('e.proyecto_id', '=', 'pe.proyecto_id')
          ->on('e.evaluador_id', '=', 'pe.evaluador_id');
      })
      ->join('Usuario_evaluador AS u', 'pe.evaluador_id', '=', 'u.id')
      ->join('Facultad AS f', 'f.id', '=', 'p.facultad_id')
      ->join('Linea_investigacion AS l', 'l.id', '=', 'p.linea_investigacion_id')
      ->select(
        'pe.id',
        'p.id AS proyecto_id',
        DB::raw('CONCAT(u.apellidos, " ", u.nombres) AS evaluador'),
        'p.tipo_proyecto',
        'p.titulo',
        'f.nombre AS facultad',
        'l.nombre AS linea',
        'p.periodo',
        DB::raw('COUNT(e.id) AS opciones_evaluadas')
      )
      ->where('p.periodo', '=', $periodo)
      ->where('p.tipo_proyecto', '=', $tipo_proyecto)
      ->whereNotNull('e.evaluacion_opcion_id')
      ->groupBy('pe.id')
      ->get();

    return ['data' => $proyectos];
  }
}

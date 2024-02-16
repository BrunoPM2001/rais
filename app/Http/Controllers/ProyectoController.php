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

  public function getAllProyectosEvaluados() {
    $proyectos = DB::table('Proyecto_evaluacion AS pe')
      ->join('Proyecto AS p', 'p.id', '=', 'pe.proyecto_id')
      ->join('Usuario_evaluador AS u', 'pe.evaluador_id', '=', 'u.id')
      ->join('Facultad AS f', 'f.id', '=', 'p.facultad_id')
      ->join('Linea_investigacion AS l', 'l.id', '=', 'p.linea_investigacion_id')
      ->select(
        'pe.id',
        'p.id AS proyecto_id',
        DB::raw('CONCAT(u.apellidos, ", ", u.nombres) AS evaluador'),
        'p.tipo_proyecto',
        'p.titulo',
        'f.nombre AS facultad',
        'l.nombre AS linea'
      )
      ->where('p.periodo', '=', 2024)
      ->get();

    return ['data' => $proyectos];
  }
}

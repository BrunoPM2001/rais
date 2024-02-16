<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller {

  public function getAllEvaluadores() {
    $proyectos = DB::table('Proyecto AS p')
      ->join('Linea_investigacion AS l', 'l.id', '=', 'p.linea_investigacion_id')
      ->join('Facultad AS f', 'f.id', '=', 'p.facultad_id')
      ->leftJoin('Evaluadores AS e', 'e.proyecto_id', '=', 'p.id')
      ->select(
        'p.id',
        'p.tipo_proyecto',
        'l.nombre AS linea',
        'f.nombre AS facultad',
        'p.titulo',
        DB::raw('GROUP_CONCAT(p.evaluador_id SEPARATOR ", ") AS evaluadores')
      )
      ->where('p.periodo', '=', 2024)
      ->where('p.step', '=', 8)
      ->where('p.estado', '<', 6)
      ->where('p.estado', '>', 1)
      ->groupBy('p.id')
      ->get();

    return ['data' => $proyectos];
  }
}

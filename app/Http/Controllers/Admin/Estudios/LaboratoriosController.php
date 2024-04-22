<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LaboratoriosController extends Controller {
  public function listado() {
    $laboratorios = DB::table('Laboratorio AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.codigo',
        'a.laboratorio',
        'a.responsable',
        'b.nombre AS facultad',
        'a.categoria_uso'
      )
      ->get();

    return ['data' => $laboratorios];
  }
}

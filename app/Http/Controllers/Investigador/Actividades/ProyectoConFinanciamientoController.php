<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProyectoConFinanciamientoController extends Controller {

  public function listado() {
    $dependencias = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'a.tipo_proyecto',
        'b.condicion',
        'a.estado'
      )
      ->where('b.investigador_id', '=',)
      ->get();

    return ['data' => $dependencias];
  }
}

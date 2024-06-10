<?php

namespace App\Http\Controllers\Admin\Economia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionComprobantesController extends Controller {
  public function listado() {
    $proyectos = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id',
        'b.codigo_proyecto',
        'b.tipo_proyecto',
        'b.periodo',
        'a.estado'
      ])
      ->get();

    return $proyectos;
  }
}

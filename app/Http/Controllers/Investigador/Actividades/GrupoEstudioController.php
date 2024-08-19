<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoEstudioController extends Controller {

  public function listado(Request $request) {
    $proyectos_antiguos = DB::table('Proyecto_H AS a')
      ->leftJoin('Proyecto_integrante_H AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.tipo AS tipo_proyecto',
        'b.condicion',
        'a.status AS estado',
        'a.periodo',
        DB::raw("'si' AS antiguo")
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.tipo', '=', 'Grupo')
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos_antiguos;
  }
}

<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoFEXController extends Controller {

  public function listado(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Proyecto_integrante_tipo AS c', 'b.proyecto_integrante_tipo_id', '=', 'c.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'a.tipo_proyecto',
        'c.nombre AS condicion',
        'a.estado',
        'a.periodo',
        DB::raw("'no' AS antiguo")
      )
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_proyecto', ['PFEX'])
      ->orderByDesc('a.periodo')
      ->get();

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
      ->where('a.tipo', '=', 'SIN-CON')
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos->merge($proyectos_antiguos);
  }
}

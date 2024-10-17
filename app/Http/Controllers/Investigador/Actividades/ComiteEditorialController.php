<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComiteEditorialController extends Controller {

  public function listado(Request $request) {
    $proyectos_antiguos = DB::table('Proyecto_H AS a')
      ->leftJoin('Proyecto_integrante_H AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.tipo AS tipo_proyecto',
        'b.condicion',
        DB::raw("CASE(a.status)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluacion'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecución'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluído'
        ELSE 'Sin estado' END AS estado"),
        'a.periodo',
        DB::raw("'si' AS antiguo")
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.tipo', '=', 'Publicacion')
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos_antiguos;
  }
}

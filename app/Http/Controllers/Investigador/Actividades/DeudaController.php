<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeudaController extends Controller {
  public function listado(Request $request) {
    $nuevo = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->join('Proyecto_integrante_tipo AS d', 'd.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'b.id',
        DB::raw("'Nuevo' as origen"),
        'c.codigo_proyecto',
        'c.titulo',
        'c.tipo_proyecto',
        'd.nombre AS condicion',
        'c.periodo',
        DB::raw("CASE 
          WHEN (b.tipo IS NULL OR b.tipo <= 0) THEN 'NO'
          WHEN b.tipo = 1 THEN 'Deuda Académica'
          WHEN b.tipo = 2 THEN 'Deuda Económica'
          WHEN b.tipo = 3 THEN 'Deuda Económica y Académica'
          WHEN b.tipo > 3 THEN 'SUBSANADA'
        END as deuda"),
        'b.informe AS deuda_detalle'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('b.tipo', [1, 2, 3]);

    $antiguo = DB::table('Proyecto_integrante_H AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_h_id', '=', 'a.id')
      ->join('Proyecto_H AS c', 'c.id', '=', 'a.proyecto_id')
      ->select([
        'b.id',
        DB::raw("'Antiguo' as origen"),
        'c.codigo as codigo_proyecto',
        'c.titulo',
        'c.tipo as tipo_proyecto',
        'a.condicion',
        'c.periodo',
        DB::raw("CASE 
          WHEN b.tipo = 1 THEN 'Deuda Académica'
          WHEN b.tipo = 2 THEN 'Deuda Económica'
          WHEN b.tipo = 3 THEN 'Deuda Económica y Académica'
          WHEN b.tipo > 3 THEN 'SUBSANADA'
        END as deuda"),
        'b.informe AS deuda_detalle'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('b.tipo', [1, 2, 3]);

    $deudas = $nuevo
      ->union($antiguo)
      ->orderBy('periodo', 'desc')
      ->get();

    return $deudas;
  }
}

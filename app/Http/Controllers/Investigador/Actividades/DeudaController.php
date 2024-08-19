<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeudaController extends Controller {
  public function listado(Request $request) {
    $deudas = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->join('Proyecto_integrante_tipo AS d', 'd.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'b.id',
        'c.codigo_proyecto',
        'c.titulo',
        'c.tipo_proyecto',
        'd.nombre AS condicion',
        'c.periodo',
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNull('b.fecha_sub')
      ->get();

    return $deudas;
  }
}

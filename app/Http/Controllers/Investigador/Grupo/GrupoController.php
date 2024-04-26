<?php

namespace App\Http\Controllers\Investigador\Grupo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller {

  public function listadoGrupos(Request $request) {
    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select(
        'b.grupo_nombre',
        'b.grupo_categoria',
        'a.condicion',
        'a.cargo',
        'b.resolucion_fecha',
        'b.estado',
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo', '=', 'grupo')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    return ['data' => $grupos];
  }

  public function listadoSolicitudes(Request $request) {
    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select(
        'b.grupo_nombre',
        'b.grupo_categoria',
        'a.condicion',
        'a.cargo',
        'b.resolucion_fecha',
        'b.estado',
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo', '=', 'solicitud')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    return ['data' => $grupos];
  }
}

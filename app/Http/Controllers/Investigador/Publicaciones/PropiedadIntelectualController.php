<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropiedadIntelectualController extends Controller {

  public function listado(Request $request) {
    $patentes = DB::table('Patente AS a')
      ->leftJoin('Patente_autor AS b', 'b.patente_id', '=', 'a.id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select(
        'a.id',
        'a.titulo',
        'a.updated_at',
        'a.estado',
        'b.puntaje',
        'a.step'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->groupBy('a.id')
      ->orderByDesc('a.updated_at')
      ->get();

    return ['data' => $patentes];
  }
}

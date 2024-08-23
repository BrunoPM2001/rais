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
        DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
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

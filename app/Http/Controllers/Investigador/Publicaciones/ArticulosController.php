<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticulosController extends Controller {

  public function listado(Request $request) {
    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_revista AS c', 'c.issn', '=', 'a.issn')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw("IF(a.publicacion_nombre IS NULL OR a.publicacion_nombre = '',CONCAT(c.revista,' ',c.issn),CONCAT(a.publicacion_nombre,' ',a.issn)) AS revista"),
        'a.observaciones_usuario',
        DB::raw('YEAR(a.fecha_publicacion) AS año_publicacion'),
        'b.puntaje',
        'a.estado'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_publicacion', ['articulo'])
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function listadoRevistasIndexadas() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select(
        'nombre AS label',
        'nombre AS value',
      )
      ->where('estado', '!=', 0)
      ->get();

    return $revistas;
  }
}

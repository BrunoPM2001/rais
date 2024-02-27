<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Publicacion_autorController extends Controller {
  public function getConstanciaPuntajePublicaciones($investigador_id) {
    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.titulo',
        'c.categoria',
        DB::raw('COUNT(*) AS Cantidad'),
        DB::raw('(c.puntaje * COUNT(*)) AS Puntaje')
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->groupBy('b.categoria_id')
      ->groupBy('c.titulo')
      ->groupBy('c.categoria')
      ->orderBy('c.titulo')
      ->orderBy('c.categoria')
      ->get();

    return ['data' => $publicaciones];
  }

  //  TODO - Verificar que las observaciones sean de esa columna
  public function getConstanciaPublicacionesCientificas($investigador_id) {
    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.tipo',
        'c.categoria',
        DB::raw('YEAR(b.fecha_publicacion) AS año'),
        'b.titulo',
        'b.publicacion_nombre',
        'b.issn',
        'b.isbn',
        'b.universidad',
        'b.pais',
        'b.observaciones_usuario',
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->orderBy('c.tipo')
      ->orderBy('c.categoria')
      ->orderByDesc('año')
      ->get();

    return ['data' => $publicaciones];
  }
}

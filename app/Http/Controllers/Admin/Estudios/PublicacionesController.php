<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicacionesController extends Controller {
  public function listado(Request $request) {
    if (!$request->query('investigador_id')) {
      $publicaciones = DB::table('Publicacion')
        ->select(
          'id',
          'codigo_registro',
          DB::raw("CASE (tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE tipo_publicacion END AS tipo"),
          'isbn',
          'issn',
          'editorial',
          'evento_nombre',
          'titulo',
          'fecha_publicacion',
          'estado',
          'source AS procedencia'
        )
        ->orderByDesc('id')
        ->get();

      return ['data' => $publicaciones];
    } else {
      $publicaciones = DB::table('Publicacion_autor AS a')
        ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
        ->select(
          'b.id',
          'b.codigo_registro',
          DB::raw("CASE (b.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE b.tipo_publicacion END AS tipo"),
          'b.isbn',
          'b.issn',
          'b.editorial',
          'b.evento_nombre',
          'b.titulo',
          'b.fecha_publicacion',
          'b.estado',
          'b.source AS procedencia'
        )
        ->where('a.investigador_id', '=', $request->query('investigador_id'))
        ->orderByDesc('b.id')
        ->get();

      return ['data' => $publicaciones];
    }
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicacionesController extends Controller {
  public function listado(Request $request) {
    if (!$request->query('investigador_id')) {
      $publicaciones = DB::table('Publicacion AS a')
        ->join('Publicacion_categoria AS b', 'b.id', '=', 'a.categoria_id')
        ->select(
          'a.id',
          'a.codigo_registro',
          'b.tipo',
          'a.isbn',
          'a.issn',
          'a.editorial',
          'a.evento_nombre',
          'a.titulo',
          'a.fecha_publicacion',
          'a.estado',
          'a.source AS procedencia'
        )
        ->orderBy('a.id')
        ->get();

      return ['data' => $publicaciones];
    } else {
      $publicaciones = DB::table('Publicacion_autor AS a')
        ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
        ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
        ->select(
          'b.id',
          'b.codigo_registro',
          'c.tipo',
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
        ->get();

      return ['data' => $publicaciones];
    }
  }

  public function listadoInvestigador($investigador_id) {
    $publicaciones = DB::table('Publicacion AS a')
      ->join('Publicacion_categoria AS b', 'b.id', '=', 'a.categoria_id')
      ->join('Publicacion_autor AS c', 'c.publicacion_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.codigo_registro',
        'b.tipo',
        'a.isbn',
        'a.issn',
        'a.editorial',
        'a.evento_nombre',
        'a.titulo',
        'a.fecha_publicacion',
        'a.estado',
        'a.source AS procedencia'
      )
      ->where('c.investigador_id', '=', $investigador_id)
      ->orderBy('a.id')
      ->get();

    return ['data' => $publicaciones];
  }
}

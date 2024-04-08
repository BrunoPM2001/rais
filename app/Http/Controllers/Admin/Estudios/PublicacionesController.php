<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PublicacionesController extends Controller {
  public function listado() {
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

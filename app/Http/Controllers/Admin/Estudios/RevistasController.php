<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RevistasController extends Controller {

  public function listado() {
    $revistas = DB::table('Publicacion_revista')
      ->select(
        'id',
        'issn',
        'issne',
        'revista',
        'casa',
        'isi',
        'pais',
        'cobertura'
      )
      ->get();

    return ['data' => $revistas];
  }

  public function listadoDBindex() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select(
        'id',
        'nombre',
        'estado',
        'created_at',
        'updated_at'
      )
      ->get();

    return ['data' => $revistas];
  }

  public function listadoDBwos() {
    $revistas = DB::table('Publicacion_db_wos')
      ->select(
        'id',
        'nombre',
        'estado',
        'created_at',
        'updated_at'
      )
      ->get();

    return ['data' => $revistas];
  }
}

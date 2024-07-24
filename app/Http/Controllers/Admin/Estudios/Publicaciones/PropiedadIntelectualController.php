<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropiedadIntelectualController extends S3Controller {

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
      ->groupBy('a.id')
      ->orderByDesc('a.updated_at')
      ->get();

    return ['data' => $patentes];
  }
}

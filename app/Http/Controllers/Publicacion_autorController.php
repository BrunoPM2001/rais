<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class Publicacion_autorController extends Controller {

  public function getConstanciaPuntajePublicaciones($investigador_id) {

    $docente = DB::table('Usuario_investigador AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        DB::raw('CONCAT(a.apellido1, " ", a.apellido2, " ", a.nombres) AS nombre'),
        'b.nombre AS facultad'
      )
      ->where('a.id', '=', $investigador_id)
      ->get()
      ->toArray();

    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.titulo',
        'c.categoria',
        DB::raw('COUNT(*) AS cantidad'),
        DB::raw('(c.puntaje * COUNT(*)) AS puntaje')
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->groupBy('b.categoria_id')
      ->groupBy('c.titulo')
      ->groupBy('c.categoria')
      ->orderBy('c.titulo')
      ->orderBy('c.categoria')
      ->get()
      ->toArray();

    $pdf = Pdf::loadView('admin.constancias.puntajePublicacionesPDF', ['docente' => $docente[0], 'publicaciones' => $publicaciones]);
    return $pdf->stream();
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

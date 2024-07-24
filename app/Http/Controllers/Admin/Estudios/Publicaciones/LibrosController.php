<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibrosController extends S3Controller {

  public function datosPaso1(Request $request) {
    $publicacion = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->select([
        'a.id',
        'b.categoria',
        'a.isbn',
        'a.titulo',
        'a.editorial',
        'a.ciudad',
        'a.pais',
        'a.edicion',
        'a.volumen',
        'a.pagina_total',
        'a.fecha_publicacion',
        'a.url',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $palabras_clave = DB::table('Publicacion_palabra_clave')
      ->select([
        'clave AS label'
      ])
      ->where('publicacion_id', '=', $request->query('id'))
      ->get();

    $utils = new PublicacionesUtilsController();
    $paises = $utils->getPaises();

    return [
      'data' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'paises' => $paises
    ];
  }

  public function reporte(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'codigo_registro',
        'isbn',
        'titulo',
        'editorial',
        'ciudad',
        'pais',
        'edicion',
        'volumen',
        'pagina_total',
        'fecha_publicacion',
        'url',
        'estado'
      ])
      ->where('id', '=', $request->query('publicacion_id'))
      ->first();

    $palabras_clave = DB::table('Publicacion_palabra_clave')
      ->select([
        'clave'
      ])
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->pluck('clave')
      ->implode(', ');

    $utils = new PublicacionesUtilsController();
    $proyectos = $utils->proyectos_asociados($request);
    $autores = $utils->listarAutores($request);

    $pdf = Pdf::loadView('investigador.publicaciones.libro', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'proyectos' => $proyectos,
      'autores' => $autores,
    ]);
    return $pdf->stream();
  }
}

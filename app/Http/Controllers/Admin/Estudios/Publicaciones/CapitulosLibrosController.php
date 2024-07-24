<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapitulosLibrosController extends S3Controller {

  public function datosPaso1(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'id',
        'titulo',
        'doi',
        'pagina_inicial',
        'pagina_final',
        'fecha_publicacion',
        'publicacion_nombre',
        'isbn',
        'editorial',
        'edicion',
        'volumen',
        'pagina_total',
        'ciudad',
        'pais',
        'url'
      ])
      ->where('id', '=', $request->query('id'))
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
        'titulo',
        'doi',
        'pagina_inicial',
        'pagina_final',
        'fecha_publicacion',
        'publicacion_nombre',
        'isbn',
        'editorial',
        'edicion',
        'volumen',
        'pagina_total',
        'ciudad',
        'pais',
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

    $pdf = Pdf::loadView('investigador.publicaciones.capitulo', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'proyectos' => $proyectos,
      'autores' => $autores,
    ]);
    return $pdf->stream();
  }
}

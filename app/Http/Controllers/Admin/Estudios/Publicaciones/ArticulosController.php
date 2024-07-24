<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticulosController extends S3Controller {

  public function datosPaso1(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'id',
        'doi',
        'art_tipo',
        'titulo',
        'pagina_inicial',
        'pagina_final',
        'fecha_publicacion',
        'publicacion_nombre',
        'issn',
        'issn_e',
        'volumen',
        'edicion',
        'url',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $palabras_clave = DB::table('Publicacion_palabra_clave')
      ->select([
        'clave AS label'
      ])
      ->where('publicacion_id', '=', $request->query('id'))
      ->get();

    $indexada = DB::table('Publicacion_index AS a')
      ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.publicacion_id', '=', $request->query('id'))
      ->get();

    $utils =  new PublicacionesUtilsController();
    $revistas = $utils->listadoRevistasIndexadas();

    return [
      'data' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'indexada' => $indexada,
      'revistas' => $revistas
    ];
  }

  public function reporte(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'codigo_registro',
        'doi',
        'art_tipo',
        'titulo',
        'pagina_inicial',
        'pagina_final',
        'fecha_publicacion',
        'publicacion_nombre',
        'issn',
        'issn_e',
        'volumen',
        'edicion',
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

    $indexada = DB::table('Publicacion_index AS a')
      ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
      ->select([
        'b.nombre AS label'
      ])
      ->where('a.publicacion_id', '=', $request->query('publicacion_id'))
      ->pluck('label')
      ->implode(', ');

    $utils = new PublicacionesUtilsController();
    $proyectos = $utils->proyectos_asociados($request);
    $autores = $utils->listarAutores($request);

    $pdf = Pdf::loadView('investigador.publicaciones.articulo', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'indexada' => $indexada,
      'proyectos' => $proyectos,
      'autores' => $autores,
    ]);
    return $pdf->stream();
  }
}

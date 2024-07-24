<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoController extends S3Controller {

  public function datosPaso1(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'id',
        'titulo',
        'tipo_presentacion',
        'publicacion_nombre',
        'isbn',
        'editorial',
        'volumen',
        'ciudad_edicion',
        'issn',
        'issn_e',
        'pagina_inicial',
        'pagina_final',
        'fecha_publicacion',
        'evento_nombre',
        'fecha_inicio',
        'fecha_fin',
        'ciudad',
        'pais',
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
        'tipo_presentacion',
        'publicacion_nombre',
        'isbn',
        'editorial',
        'volumen',
        'ciudad_edicion',
        'issn',
        'issn_e',
        'pagina_inicial',
        'pagina_final',
        'fecha_publicacion',
        'evento_nombre',
        'fecha_inicio',
        'fecha_fin',
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

    $pdf = Pdf::loadView('investigador.publicaciones.evento', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'proyectos' => $proyectos,
      'autores' => $autores,
    ]);
    return $pdf->stream();
  }
}

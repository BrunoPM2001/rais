<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TesisPropiasController extends S3Controller {

  public function datosPaso1(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'id',
        'titulo',
        'url',
        'tipo_tesis',
        'fecha_publicacion',
        'pagina_total',
        'universidad',
        'lugar_publicacion',
        'pais',
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
        'url',
        'tipo_tesis',
        'fecha_publicacion',
        'pagina_total',
        'universidad',
        'lugar_publicacion',
        'pais',
        'estado'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $palabras_clave = DB::table('Publicacion_palabra_clave')
      ->select([
        'clave'
      ])
      ->where('publicacion_id', '=', $request->query('id'))
      ->pluck('clave')
      ->implode(', ');

    $utils = new PublicacionesUtilsController();
    $proyectos = $utils->proyectos_asociados($request);
    $autores = $utils->listarAutores($request);

    $pdf = Pdf::loadView('investigador.publicaciones.tesis_propia', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'proyectos' => $proyectos,
      'autores' => $autores,
    ]);
    return $pdf->stream();
  }

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();

    if ($request->input('id') == null) {

      $util = new PublicacionesUtilsController();

      if ($util->verificarTituloUnico($request)) {

        $publicacion_id = DB::table('Publicacion')->insertGetId([
          'titulo' => $request->input('titulo'),
          'url' => $request->input('url'),
          'tipo_tesis' => $request->input('tipo_tesis')["value"],
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'pagina_total' => $request->input('pagina_total'),
          'universidad' => $request->input('universidad'),
          'lugar_publicacion' => $request->input('lugar_publicacion'),
          'pais' => $request->input('pais')["value"],
          'validado' => 0,
          'step' => 2,
          'tipo_publicacion' => 'tesis',
          'estado' => 6,
          'created_at' => $date,
          'updated_at' => $date
        ]);

        foreach ($request->input('palabras_clave') as $palabra) {
          DB::table('Publicacion_palabra_clave')->insert([
            'publicacion_id' => $publicacion_id,
            'clave' => $palabra["label"]
          ]);
        }

        return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'publicacion_id' => $publicacion_id];
      } else {
        return ['message' => 'error', 'detail' => 'Está usando el título de una publicación que ya está registrada'];
      }
    } else {
      $publicacion_id = $request->input('id');
      DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->update([
          'titulo' => $request->input('titulo'),
          'url' => $request->input('url'),
          'tipo_tesis' => $request->input('tipo_tesis')["value"],
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'pagina_total' => $request->input('pagina_total'),
          'universidad' => $request->input('universidad'),
          'lugar_publicacion' => $request->input('lugar_publicacion'),
          'pais' => $request->input('pais')["value"],
          'updated_at' => $date
        ]);

      DB::table('Publicacion_palabra_clave')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();

      foreach ($request->input('palabras_clave') as $palabra) {
        DB::table('Publicacion_palabra_clave')->insert([
          'publicacion_id' => $publicacion_id,
          'clave' => $palabra["label"]
        ]);
      }

      return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados'];
    }
  }
}

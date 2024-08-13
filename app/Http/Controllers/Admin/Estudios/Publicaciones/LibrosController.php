<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
    $publicacion = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_categoria AS b', 'b.id', '=', 'a.categoria_id')
      ->select([
        'a.codigo_registro',
        'a.isbn',
        'a.titulo',
        'a.editorial',
        'a.lugar_publicacion',
        'a.pais',
        'a.edicion',
        'a.volumen',
        'a.pagina_total',
        'a.fecha_publicacion',
        'a.url',
        'a.estado',
        'a.updated_at',
        'b.categoria'
      ])
      ->where('a.id', '=', $request->query('id'))
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

    $pdf = Pdf::loadView('admin.estudios.publicaciones.libro', [
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
          'isbn' => $request->input('isbn'),
          'titulo' => $request->input('titulo'),
          'editorial' => $request->input('editorial'),
          'ciudad' => $request->input('ciudad'),
          'pais' => $request->input('pais')["value"],
          'edicion' => $request->input('edicion'),
          'volumen' => $request->input('volumen'),
          'pagina_total' => $request->input('pagina_total'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'url' => $request->input('url'),
          'validado' => 0,
          'step' => 1,
          'tipo_publicacion' => 'libro',
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
          'isbn' => $request->input('isbn'),
          'titulo' => $request->input('titulo'),
          'editorial' => $request->input('editorial'),
          'ciudad' => $request->input('ciudad'),
          'pais' => $request->input('pais')["value"],
          'edicion' => $request->input('edicion'),
          'volumen' => $request->input('volumen'),
          'pagina_total' => $request->input('pagina_total'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'url' => $request->input('url'),
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

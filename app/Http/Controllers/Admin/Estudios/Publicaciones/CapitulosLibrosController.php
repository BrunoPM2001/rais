<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
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

  public function infoNuevo() {
    $utils = new PublicacionesUtilsController();
    $paises = $utils->getPaises();

    return $paises;
  }

  public function reporte(Request $request) {
    $publicacion = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_categoria AS b', 'b.id', '=', 'a.categoria_id')
      ->leftJoin('File AS c', function (JoinClause $join) {
        $join->on('c.tabla_id', '=', 'a.id')
          ->where('c.tabla', '=', 'Publicacion')
          ->where('c.estado', '=', 20);
      })
      ->select([
        'a.id',
        'a.codigo_registro',
        'a.titulo',
        'a.doi',
        'a.nombre_libro',
        'a.pagina_inicial',
        'a.pagina_final',
        'a.fecha_publicacion',
        'a.publicacion_nombre',
        'a.isbn',
        'a.editorial',
        'a.edicion',
        'a.volumen',
        'a.pagina_total',
        'a.lugar_publicacion AS ciudad',
        'a.pais',
        'a.url',
        'a.resolucion',
        'a.estado',
        'a.observaciones_usuario',
        'a.updated_at',
        'b.categoria',
        'c.key AS anexo'
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

    $pdf = Pdf::loadView('admin.estudios.publicaciones.capitulo', [
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
          'doi' => $request->input('doi'),
          'pagina_inicial' => $request->input('pagina_inicial'),
          'pagina_final' => $request->input('pagina_final'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'publicacion_nombre' => $request->input('publicacion_nombre'),
          'isbn' => $request->input('isbn'),
          'editorial' => $request->input('editorial'),
          'edicion' => $request->input('edicion'),
          'volumen' => $request->input('volumen'),
          'pagina_total' => $request->input('pagina_total'),
          'ciudad' => $request->input('ciudad'),
          'pais' => $request->input('pais')["value"],
          'url' => $request->input('url'),
          'validado' => 0,
          'step' => 1,
          'tipo_publicacion' => 'capitulo',
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
        return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'id' => $publicacion_id];
      } else {
        return ['message' => 'error', 'detail' => 'Está usando el título de una publicación que ya está registrada'];
      }
    } else {
      $publicacion_id = $request->input('id');
      DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->update([
          'titulo' => $request->input('titulo'),
          'doi' => $request->input('doi'),
          'pagina_inicial' => $request->input('pagina_inicial'),
          'pagina_final' => $request->input('pagina_final'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'publicacion_nombre' => $request->input('publicacion_nombre'),
          'isbn' => $request->input('isbn'),
          'editorial' => $request->input('editorial'),
          'edicion' => $request->input('edicion'),
          'volumen' => $request->input('volumen'),
          'pagina_total' => $request->input('pagina_total'),
          'ciudad' => $request->input('ciudad'),
          'pais' => $request->input('pais')["value"],
          'url' => $request->input('url'),
          'tipo_publicacion' => 'capitulo',
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

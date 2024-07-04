<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibrosController extends Controller {

  public function listado(Request $request) {
    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_revista AS c', 'c.issn', '=', 'a.issn')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw("IF(a.publicacion_nombre IS NULL OR a.publicacion_nombre = '',CONCAT(c.revista,' ',c.issn),CONCAT(a.publicacion_nombre,' ',a.issn)) AS revista"),
        DB::raw('COALESCE(a.issn, a.isbn) AS isbn'),
        DB::raw('YEAR(a.fecha_publicacion) AS año_publicacion'),
        'b.puntaje',
        'a.estado',
        'a.step'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_publicacion', ['libro'])
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function registrarPaso1(Request $request) {
    if ($request->input('publicacion_id') == null) {
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
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

      DB::table('Publicacion_autor')->insert([
        'publicacion_id' => $publicacion_id,
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'tipo' => 'interno',
        'categoria' => $request->input('categoria_autor')["value"],
        'presentado' => 1,
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

      foreach ($request->input('palabras_clave') as $palabra) {
        DB::table('Publicacion_palabra_clave')->insert([
          'publicacion_id' => $publicacion_id,
          'clave' => $palabra["label"]
        ]);
      }
      return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'publicacion_id' => $publicacion_id];
    } else {
      $publicacion_id = $request->input('publicacion_id');
      $count = DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->where('estado', '!=', 5)
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
          'validado' => 0,
          'step' => 1,
          'tipo_publicacion' => 'libro',
          'estado' => 6,
          'updated_at' => Carbon::now()
        ]);

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
      }

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

  public function datosPaso1(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
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
        ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.id', '=', $request->query('publicacion_id'))
        ->first();

      $palabras_clave = DB::table('Publicacion_palabra_clave')
        ->select([
          'clave AS label'
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $utils = new PublicacionesUtilsController();
      $paises = $utils->getPaises();

      return [
        'data' => $publicacion,
        'palabras_clave' => $palabras_clave,
        'paises' => $paises
      ];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
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

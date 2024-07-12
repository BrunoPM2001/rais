<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticulosController extends S3Controller {

  public function listado(Request $request) {
    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_revista AS c', 'c.issn', '=', 'a.issn')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw("IF(a.publicacion_nombre IS NULL OR a.publicacion_nombre = '',CONCAT(c.revista,' ',c.issn),CONCAT(a.publicacion_nombre,' ',a.issn)) AS revista"),
        'a.observaciones_usuario',
        DB::raw('YEAR(a.fecha_publicacion) AS año_publicacion'),
        'b.puntaje',
        'a.estado',
        'a.step'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_publicacion', ['articulo'])
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function registrarPaso1(Request $request) {
    if ($request->input('publicacion_id') == null) {
      $publicacion_id = DB::table('Publicacion')->insertGetId([
        'doi' => $request->input('doi'),
        'art_tipo' => $request->input('art_tipo')["value"],
        'titulo' => $request->input('titulo'),
        'pagina_inicial' => $request->input('pagina_inicial'),
        'pagina_final' => $request->input('pagina_final'),
        'fecha_publicacion' => $request->input('fecha_publicacion'),
        'publicacion_nombre' => $request->input('publicacion_nombre'),
        'issn' => $request->input('issn'),
        'issn_e' => $request->input('issn_e'),
        'volumen' => $request->input('volumen'),
        'edicion' => $request->input('edicion'),
        'url' => $request->input('url'),
        'validado' => 0,
        'step' => 1,
        'tipo_publicacion' => 'articulo',
        'estado' => 6,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

      DB::table('Publicacion_autor')->insert([
        'publicacion_id' => $publicacion_id,
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'tipo' => 'interno',
        'categoria' => 'Autor',
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

      foreach ($request->input('indexada') as $indexada) {
        DB::table('Publicacion_index')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_indexada_id' => $indexada["value"],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
      }
      return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'publicacion_id' => $publicacion_id];
    } else {
      $publicacion_id = $request->input('publicacion_id');
      $count = DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->whereIn('estado', [2, 6])
        ->update([
          'doi' => $request->input('doi'),
          'art_tipo' => $request->input('art_tipo')["value"],
          'titulo' => $request->input('titulo'),
          'pagina_inicial' => $request->input('pagina_inicial'),
          'pagina_final' => $request->input('pagina_final'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'publicacion_nombre' => $request->input('publicacion_nombre'),
          'issn' => $request->input('issn'),
          'issn_e' => $request->input('issn_e'),
          'volumen' => $request->input('volumen'),
          'edicion' => $request->input('edicion'),
          'url' => $request->input('url'),
          'validado' => 0,
          'tipo_publicacion' => 'articulo',
          'estado' => 6,
          'updated_at' => Carbon::now()
        ]);

      DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->where('estado', '!=', 5)
        ->update([
          'step' => 2
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

      DB::table('Publicacion_index')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();

      foreach ($request->input('indexada') as $indexada) {
        DB::table('Publicacion_index')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_indexada_id' => $indexada["value"],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
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
        ->where('id', '=', $request->query('publicacion_id'))
        ->first();

      $palabras_clave = DB::table('Publicacion_palabra_clave')
        ->select([
          'clave AS label'
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $indexada = DB::table('Publicacion_index AS a')
        ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
        ->select([
          'b.id AS value',
          'b.nombre AS label'
        ])
        ->where('a.publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $utils =  new PublicacionesUtilsController();
      $revistas = $utils->listadoRevistasIndexadas();

      return [
        'data' => $publicacion,
        'palabras_clave' => $palabras_clave,
        'indexada' => $indexada,
        'revistas' => $revistas
      ];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
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

  public function validarPublicacion(Request $request) {
    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;
    $query = $request->query('query');

    $subquery = DB::table('Publicacion_autor')
      ->select('publicacion_id')
      ->where('investigador_id', '=', $investigador_id);

    $publicaciones = DB::table('Publicacion AS a')
      ->whereNotIn('a.id', $subquery)
      ->select(
        'a.id',
        'a.titulo AS value'
      )
      ->where('a.titulo', 'LIKE', '%' . $query . '%')
      ->groupBy('a.id')
      ->limit(10)
      ->get();

    return $publicaciones;
  }
}

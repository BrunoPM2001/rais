<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

    $indexada_wos = DB::table('Publicacion_wos AS a')
      ->join('Publicacion_db_wos AS b', 'b.id', '=', 'a.publicacion_db_wos_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.publicacion_id', '=', $request->query('id'))
      ->get();

    $utils =  new PublicacionesUtilsController();
    $revistas = $utils->listadoRevistasIndexadas();
    $wos = $utils->listadoWos();

    return [
      'data' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'indexada' => $indexada,
      'indexada_wos' => $indexada_wos,
      'revistas' => $revistas,
      'wos' => $wos
    ];
  }

  public function reporte(Request $request) {
    $publicacion = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_categoria AS b', 'b.id', '=', 'a.categoria_id')
      ->select([
        'a.codigo_registro',
        'a.art_tipo',
        'a.titulo',
        'a.doi',
        'a.publicacion_nombre',
        'a.pagina_inicial',
        'a.pagina_final',
        'a.fecha_publicacion',
        'a.issn',
        'a.issn_e',
        'a.volumen',
        'a.edicion',
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

    $indexada = DB::table('Publicacion_index AS a')
      ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
      ->select([
        'b.nombre AS label'
      ])
      ->where('a.publicacion_id', '=', $request->query('id'))
      ->pluck('label')
      ->implode(', ');

    $utils = new PublicacionesUtilsController();
    $proyectos = $utils->proyectos_asociados($request);
    $autores = $utils->listarAutores($request);

    $pdf = Pdf::loadView('admin.estudios.publicaciones.articulo', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'indexada' => $indexada,
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
          'step' => 4,
          'tipo_publicacion' => 'articulo',
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

        foreach ($request->input('indexada') as $indexada) {
          DB::table('Publicacion_index')->insert([
            'publicacion_id' => $publicacion_id,
            'publicacion_db_indexada_id' => $indexada["value"],
            'created_at' => $date,
            'updated_at' => $date
          ]);
        }

        foreach ($request->input('wos') as $wos) {
          DB::table('Publicacion_wos')->insert([
            'publicacion_id' => $publicacion_id,
            'publicacion_db_wos_id' => $wos["value"],
            'created_at' => $date,
            'updated_at' => $date
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

      DB::table('Publicacion_index')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();

      foreach ($request->input('indexada') as $indexada) {
        DB::table('Publicacion_index')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_indexada_id' => $indexada["value"],
          'created_at' => $date,
          'updated_at' => $date
        ]);
      }

      DB::table('Publicacion_wos')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();

      foreach ($request->input('wos') as $wos) {
        DB::table('Publicacion_wos')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_wos_id' => $wos["value"],
          'created_at' => $date,
          'updated_at' => $date
        ]);
      }
      return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados'];
    }
  }
}

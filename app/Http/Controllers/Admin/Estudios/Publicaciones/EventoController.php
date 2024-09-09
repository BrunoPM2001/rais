<?php

namespace App\Http\Controllers\Admin\Estudios\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
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
        'a.codigo_registro',
        'a.titulo',
        'a.tipo_presentacion',
        'a.publicacion_nombre',
        'a.isbn',
        'a.editorial',
        'a.volumen',
        'a.ciudad_edicion',
        'a.issn',
        'a.issn_e',
        'a.pagina_inicial',
        'a.pagina_final',
        'a.fecha_publicacion',
        'a.evento_nombre',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.ciudad',
        'a.pais',
        'a.url',
        'a.resolucion',
        'a.estado',
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

    $pdf = Pdf::loadView('admin.estudios.publicaciones.evento', [
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
          'tipo_presentacion' => $request->input('tipo_presentacion')["value"],
          'publicacion_nombre' => $request->input('publicacion_nombre'),
          'isbn' => $request->input('isbn'),
          'editorial' => $request->input('editorial'),
          'volumen' => $request->input('volumen'),
          'ciudad_edicion' => $request->input('ciudad_edicion'),
          'issn' => $request->input('issn'),
          'issn_e' => $request->input('issn_e'),
          'pagina_inicial' => $request->input('pagina_inicial'),
          'pagina_final' => $request->input('pagina_final'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'evento_nombre' => $request->input('evento_nombre'),
          'fecha_inicio' => $request->input('fecha_inicio'),
          'fecha_fin' => $request->input('fecha_fin'),
          'ciudad' => $request->input('ciudad'),
          'pais' => $request->input('pais')["value"],
          'url' => $request->input('url'),
          'validado' => 0,
          'step' => 2,
          'estado' => 6,
          'tipo_publicacion' => 'evento',
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
          'tipo_presentacion' => $request->input('tipo_presentacion')["value"],
          'publicacion_nombre' => $request->input('publicacion_nombre'),
          'isbn' => $request->input('isbn'),
          'editorial' => $request->input('editorial'),
          'volumen' => $request->input('volumen'),
          'ciudad_edicion' => $request->input('ciudad_edicion'),
          'issn' => $request->input('issn'),
          'issn_e' => $request->input('issn_e'),
          'pagina_inicial' => $request->input('pagina_inicial'),
          'pagina_final' => $request->input('pagina_final'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'evento_nombre' => $request->input('evento_nombre'),
          'fecha_inicio' => $request->input('fecha_inicio'),
          'fecha_fin' => $request->input('fecha_fin'),
          'ciudad' => $request->input('ciudad'),
          'pais' => $request->input('pais')["value"],
          'url' => $request->input('url'),
          'step' => 2,
          'updated_at' => Carbon::now()
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

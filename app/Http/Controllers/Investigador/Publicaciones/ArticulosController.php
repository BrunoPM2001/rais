<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticulosController extends Controller {

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
        'a.estado'
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
      'step' => 2,
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
      'estado' => 0,
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
        ->get();

      $palabras_clave = DB::table('Publicacion_palabra_clave')
        ->select([
          'clave'
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $indexada = DB::table('Publicacion_index')
        ->select([
          'publicacion_db_indexada_id'
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return [
        'data' => $publicacion,
        'palabras_clave' => $palabras_clave,
        'indexada' => $indexada
      ];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  //  TODO - Poner todo en un nuevo controlador
  public function listadoRevistasIndexadas() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select(
        'id AS value',
        'nombre AS label',
      )
      ->where('estado', '!=', 0)
      ->get();

    return $revistas;
  }

  public function proyectos_asociados(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $proyectos = DB::table('Publicacion_proyecto')
        ->select([
          'id',
          'codigo_proyecto',
          'nombre_proyecto',
          'entidad_financiadora',
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $proyectos;
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function proyectos_registrados(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_descripcion AS b', function ($join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.codigo', '=', 'fuente_financiadora');
      })
      ->select(
        DB::raw("CONCAT(a.codigo_proyecto, ' | ', a.titulo) AS value"),
        'a.id AS proyecto_id',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("IFNULL(b.detalle, 'UNMSM') AS entidad_financiadora")
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $proyectos;
  }

  public function agregarProyecto(Request $request) {
    if ($request->input('proyecto_id') != null) {
      DB::table('Publicacion_proyecto')->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => 'INTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);
    } else {
      DB::table('Publicacion_proyecto')->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => 'EXTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);
    }

    return ['message' => 'success', 'detail' => 'Proyecto agregado exitosamente'];
  }

  public function listarAutores(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $proyectos = DB::table('Publicacion_autor AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.id',
          'a.presentado',
          'a.categoria',
          DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
          'b.tipo',
          'a.filiacion',
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $proyectos;
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }
}

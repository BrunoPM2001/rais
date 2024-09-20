<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TesisPropiasController extends Controller {

  public function listado(Request $request) {
    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_revista AS c', 'c.issn', '=', 'a.issn')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw('YEAR(a.fecha_publicacion) AS año_publicacion'),
        'b.puntaje',
        'a.observaciones_usuario',
        DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
        'a.step'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_publicacion', ['ensayo', 'tesis'])
      ->whereNot('b.categoria', '=', '%asesor%')
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function registrarPaso1(Request $request) {
    if ($request->input('publicacion_id') == null) {
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
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

      DB::table('Publicacion_autor')->insert([
        'publicacion_id' => $publicacion_id,
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'tipo' => 'interno',
        'categoria' => 'Tesista',
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

      return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'publicacion_id' => $publicacion_id];
    } else {
      $publicacion_id = $request->input('publicacion_id');
      $count = DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->whereIn('estado', [2, 6])
        ->update([
          'titulo' => $request->input('titulo'),
          'url' => $request->input('url'),
          'tipo_tesis' => $request->input('tipo_tesis')["value"],
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'pagina_total' => $request->input('pagina_total'),
          'universidad' => $request->input('universidad'),
          'lugar_publicacion' => $request->input('lugar_publicacion'),
          'pais' => $request->input('pais')["value"],
          'step' => 2,
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
        ->where('id', '=', $request->query('publicacion_id'))
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

    $pdf = Pdf::loadView('investigador.publicaciones.tesis_propia', [
      'publicacion' => $publicacion,
      'palabras_clave' => $palabras_clave,
      'proyectos' => $proyectos,
      'autores' => $autores["listado"],
    ]);
    return $pdf->stream();
  }
}

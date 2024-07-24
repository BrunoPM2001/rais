<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Admin\Estudios\Publicaciones\ArticulosController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\CapitulosLibrosController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\EventoController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\LibrosController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\PublicacionesUtilsController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\TesisAsesoriaController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\TesisPropiasController;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicacionesController extends Controller {
  public function listado(Request $request) {
    if (!$request->query('investigador_id')) {
      $publicaciones = DB::table('Publicacion')
        ->select(
          'id',
          'codigo_registro',
          DB::raw("CASE (tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE tipo_publicacion END AS tipo"),
          'isbn',
          'issn',
          'editorial',
          'evento_nombre',
          'titulo',
          'fecha_publicacion',
          'estado',
          'source AS procedencia'
        )
        ->orderByDesc('id')
        ->get();

      return ['data' => $publicaciones];
    } else {
      $publicaciones = DB::table('Publicacion_autor AS a')
        ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
        ->select(
          'b.id',
          'b.codigo_registro',
          DB::raw("CASE (b.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE b.tipo_publicacion END AS tipo"),
          'b.isbn',
          'b.issn',
          'b.editorial',
          'b.evento_nombre',
          'b.titulo',
          'b.fecha_publicacion',
          'b.estado',
          'b.source AS procedencia'
        )
        ->where('a.investigador_id', '=', $request->query('investigador_id'))
        ->orderByDesc('b.id')
        ->get();

      return ['data' => $publicaciones];
    }
  }

  public function detalle(Request $request) {
    $data = DB::table('Publicacion AS a')
      ->leftJoin('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Publicacion')
          ->where('b.estado', '=', 20);
      })
      ->select([
        'a.id',
        'a.codigo_registro',
        'a.validado',
        'a.categoria_id',
        'a.comentario',
        'a.observaciones_usuario',
        'a.fecha_inscripcion',
        'a.estado',
        'a.tipo_publicacion',
        'b.id AS file_id',
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $categorias = DB::table('Publicacion_categoria')
      ->select([
        'id AS value',
        'categoria AS label',
      ])
      ->where('created_at', '>', '2018-05-05');

    switch ($data->tipo_publicacion) {
      case 'articulo':
        $categorias = $categorias
          ->where('tipo', '=', 'Artículo en Revista')
          ->get();
        break;
      case 'capitulo':
        $categorias = $categorias
          ->where('tipo', '=', 'Capítulo en Libro')
          ->get();
        break;
      case 'evento':
        $categorias = $categorias
          ->where('tipo', '=', 'Libro de Resúmenes')
          ->get();
        break;
      case 'libro':
        $categorias = $categorias
          ->where('tipo', '=', 'Libro')
          ->get();
        break;
      case 'tesis-asesoria':
        $categorias = $categorias
          ->where('tipo', '=', 'Tesis asesoria')
          ->get();
        break;
      case 'tesis':
        $categorias = $categorias
          ->where('tipo', '=', 'Tesis')
          ->get();
        break;
    }

    return [
      'data' => $data,
      'categorias' => $categorias,
    ];
  }

  public function updateDetalle(Request $request) {
    $cod = 0;
    if ($request->input('estado')["value"] == 1) {
      if ($request->input('categoria_id') == null) {
        return ['message' => 'warning', 'detail' => 'Necesita colocar una calificación en caso quiera registrar la publicación'];
      } else {
        $pub = DB::table('Publicacion')
          ->select([
            'codigo_registro'
          ])
          ->where('id', '=', $request->input('id'))
          ->first();

        if ($pub->codigo_registro == null) {
          $cod = DB::table('Publicacion')
            ->select([
              'codigo_registro'
            ])
            ->orderByDesc('codigo_registro')
            ->first();

          DB::table('Publicacion')
            ->where('id', '=', $request->input('id'))
            ->update([
              'codigo_registro' => $cod->codigo_registro + 1,
              'validado' => $request->input('validado')["value"],
              'categoria_id' => $request->input('categoria_id')["value"],
              'comentario' => $request->input('comentario'),
              'observaciones_usuario' => $request->input('observaciones_usuario'),
              'estado' => $request->input('estado')["value"],
              'updated_at' => Carbon::now(),
            ]);

          return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados correctamente'];
        }
      }
    }

    DB::table('Publicacion')
      ->where('id', '=', $request->input('id'))
      ->update([
        'validado' => $request->input('validado')["value"],
        'categoria_id' => $request->input('categoria_id')["value"],
        'comentario' => $request->input('comentario'),
        'observaciones_usuario' => $request->input('observaciones_usuario'),
        'estado' => $request->input('estado')["value"],
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados correctamente'];
  }

  public function getTabs(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'tipo_publicacion'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $data = [];

    switch ($publicacion->tipo_publicacion) {
      case "articulo":
        $util1 = new ArticulosController();
        $data = $util1->datosPaso1($request);
        break;
      case "libro":
        $util1 = new LibrosController();
        $data = $util1->datosPaso1($request);
        break;
      case "capitulo":
        $util1 = new CapitulosLibrosController();
        $data = $util1->datosPaso1($request);
        break;
      case "tesis":
        $util1 = new TesisPropiasController();
        $data = $util1->datosPaso1($request);
        break;
      case "tesis-asesoria":
        $util1 = new TesisAsesoriaController();
        $data = $util1->datosPaso1($request);
        break;
      case "evento":
        $util1 = new EventoController();
        $data = $util1->datosPaso1($request);
        break;
      default:
        break;
    }

    $util2 = new PublicacionesUtilsController();
    $proyectos = $util2->proyectos_asociados($request);
    $autores = $util2->listarAutores($request);
    return [
      'detalle' => $data,
      'proyectos' => $proyectos,
      'autores' => $autores,
      'tipo' => $publicacion->tipo_publicacion
    ];
  }
}

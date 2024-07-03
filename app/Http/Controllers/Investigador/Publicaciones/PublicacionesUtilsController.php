<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicacionesUtilsController extends S3Controller {

  /*
  |-----------------------------------------------------------
  | Pasos 2, 3 y 4
  |-----------------------------------------------------------
  |
  | Funciones para los pasos 2, 3 y 4 de cada publicación, ya
  | que estos se repiten.
  |
  */

  //  Paso 2
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
      ->whereNotNull('codigo_proyecto')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $proyectos;
  }

  public function agregarProyecto(Request $request) {

    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->where('estado', '!=', 5)
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_proyecto')
      ->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => $request->input('proyecto_id') == null ? 'EXTERNO' : 'INTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);


    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->update([
        'step' => 2
      ]);

    return ['message' => 'success', 'detail' => 'Proyecto agregado exitosamente'];
  }

  public function eliminarProyecto(Request $request) {
    $count = DB::table('Publicacion_proyecto')
      ->where('id', '=', $request->query('proyecto_id'))
      ->where('estado', '!=', 5)
      ->delete();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Proyecto eliminado de la lista exitosamente'];
  }

  //  Paso 3
  public function listarAutores(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $autores = DB::table('Publicacion_autor AS a')
        ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.id',
          'a.presentado',
          'a.categoria',
          'a.autor',
          DB::raw("COALESCE(b.tipo, 'Externo') AS tipo"),
          DB::raw("COALESCE(CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres), 
                  CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres)) AS nombres"),
          'a.filiacion',
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $autores;
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function searchDocenteRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'DOCENTE%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchEstudianteRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'ESTUDIANTE%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchExternoRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'EXTERNO%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function agregarAutor(Request $request) {

    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->where('estado', '!=', 5)
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    switch ($request->input('tipo')) {
      case "externo":
        DB::table('Publicacion_autor')->insert([
          'publicacion_id' => $request->input('publicacion_id'),
          'tipo' => $request->input('tipo'),
          'nombres' => $request->input('nombres'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'autor' => $request->input('autor'),
          'categoria' => $request->input('categoria'),
          'filiacion' => $request->input('filiacion'),
          'presentado' => 0,
          'estado' => 0,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
        break;
      default:
        DB::table('Publicacion_autor')->insert([
          'publicacion_id' => $request->input('publicacion_id'),
          'investigador_id' => $request->input('investigador_id'),
          'tipo' => $request->input('tipo'),
          'autor' => $request->input('autor'),
          'categoria' => $request->input('categoria'),
          'filiacion' => $request->input('filiacion'),
          'presentado' => 0,
          'estado' => 0,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
        break;
    }

    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->where('estado', '!=', 5)
      ->update([
        'step' => 3
      ]);

    return ['message' => 'success', 'detail' => 'Autor agregado exitosamente'];
  }

  public function editarAutor(Request $request) {
    $count = DB::table('Publicacion_autor')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 5)
      ->update([
        'autor' => $request->input('autor'),
        'categoria' => $request->input('categoria'),
        'filiacion' => $request->input('filiacion'),
        'updated_at' => Carbon::now()
      ]);

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Datos del autor editado exitosamente'];
  }

  public function eliminarAutor(Request $request) {
    $count = DB::table('Publicacion_autor')
      ->where('estado', '=', 5)
      ->where('id', '=', $request->query('id'))
      ->delete();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Autor eliminado de la lista exitosamente'];
  }

  //  Paso 4
  public function enviarPublicacion(Request $request) {
    if ($request->hasFile('file')) {
      $count = DB::table('Publicacion')
        ->where('id', '=', $request->input('publicacion_id'))
        ->where('estado', '!=', '5')
        ->update([
          'step' => 4,
          'estado' => 5
        ]);

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
      } else {

        $date = Carbon::now();
        $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
        $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

        $this->uploadFile($request->file('file'), "publicacion", $nameFile);

        return ['message' => 'success', 'detail' => 'Publicación enviada correctamente'];
      }
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar el archivo'];
    }
  }

  /*
  |-----------------------------------------------------------
  | Listado de data
  |-----------------------------------------------------------
  |
  | Listado de revistas, países, etc. Usados al momento de 
  | registrar más de un tipo de controlador de publicación.
  |
  */

  public function listadoRevistasIndexadas() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select([
        'id AS value',
        'nombre AS label',
      ])
      ->where('estado', '!=', 0)
      ->get();

    return $revistas;
  }

  public function getPaises() {
    $paises = DB::table('Pais')
      ->select(['name AS value'])
      ->get();
    return $paises;
  }
}

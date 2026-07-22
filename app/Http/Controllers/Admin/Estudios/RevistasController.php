<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevistasController extends Controller {

  public function listado() {
    $revistas = DB::table('Publicacion_revista')
      ->select(
        'id',
        'issn',
        'issne',
        'revista',
        'casa',
        DB::raw("CASE(isi)
          WHEN 0 THEN 'No'
          WHEN 1 THEN 'Sí'
          ELSE 'No'
        END AS isi"),
        'pais',
        'cobertura'
      )
      ->get();

    return $revistas;
  }

  public function addRevista(Request $request) {
    $count = DB::table('Publicacion_revista')
      ->where('issn', '=', $request->input('issn'), 'or')
      ->where('issne', '=', $request->input('issne'), 'or')
      ->where('revista', '=', $request->input('revista'))
      ->count();

    if ($count == 0) {
      DB::table('Publicacion_revista')
        ->insert([
          'issn' => $request->input('issn'),
          'issne' => $request->input('issne'),
          'revista' => $request->input('revista'),
          'casa' => $request->input('casa'),
          'isi' => $request->input('isi')["value"],
          'pais' => $request->input('pais')["value"],
          'cobertura' => $request->input('cobertura'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      return ['message' => 'success', 'detail' => 'Revista registrada correctamente'];
    } else {
      return ['message' => 'warning', 'detail' => 'Ya hay una revista con ese ISSN, ISSNE o nombre'];
    }
  }

  public function updateRevista(Request $request) {
    DB::table('Publicacion_revista')
      ->where('id', '=', $request->input('id'))
      ->update([
        'issn' => $request->input('issn'),
        'issne' => $request->input('issne'),
        'revista' => $request->input('revista'),
        'casa' => $request->input('casa'),
        'isi' => $request->input('isi')["value"],
        'pais' => $request->input('pais')["value"],
        'cobertura' => $request->input('cobertura'),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'Revista actualizada correctamente'];
  }

  public function listadoDBindex() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select(
        'id',
        'nombre',
        DB::raw("CASE(estado)
          WHEN 1 THEN 'Activo'
          ELSE 'No activo'
        END AS estado"),
        'created_at',
        'updated_at'
      )
      ->get();

    return $revistas;
  }

  public function updateDBindex(Request $request) {
    DB::table('Publicacion_db_indexada')
      ->where('id', '=', $request->input('id'))
      ->update([
        'nombre' => $request->input('nombre'),
        'estado' => $request->input('estado')["value"],
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'DB indexada actualizada correctamente'];
  }

  public function listadoDBwos() {
    $revistas = DB::table('Publicacion_db_wos')
      ->select(
        'id',
        'nombre',
        DB::raw("CASE(estado)
          WHEN 1 THEN 'Activo'
          ELSE 'No activo'
        END AS estado"),
        'created_at',
        'updated_at'
      )
      ->get();

    return $revistas;
  }

  public function updateDBwos(Request $request) {
    DB::table('Publicacion_db_wos')
      ->where('id', '=', $request->input('id'))
      ->update([
        'nombre' => $request->input('nombre'),
        'estado' => $request->input('estado')["value"],
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'DB wos actualizada correctamente'];
  }

  public function listadoRevistaEditores() {
    $listado = DB::table('Publicacion_revista_editor AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.id',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS editor"),
        'a.issn',
        'a.issne',
        'a.revista',
        'a.casa',
        'a.pais',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.estado',
        'a.created_at',
        'a.updated_at',
      ])
      ->get();

    return $listado;
  }
}

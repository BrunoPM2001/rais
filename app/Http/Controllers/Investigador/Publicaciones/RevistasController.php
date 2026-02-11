<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevistasController extends S3Controller {
  public function listado(Request $request) {
    $listado = DB::table('Publicacion_revista_editor')
      ->select([
        'id',
        'issn',
        'issne',
        'revista',
        'casa',
        'pais',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'created_at',
        'updated_at',
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    return $listado;
  }

  public function registrar(Request $request) {
    DB::table('Publicacion_revista_editor')
      ->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'issn' => $request->input('issn'),
        'issne' => $request->input('issne'),
        'revista' => $request->input('revista'),
        'casa' => $request->input('casa'),
        'pais' => $request->input('pais')["value"],
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin') != "" ? $request->input('fecha_fin') : null,
        'estado' => 0,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'success', 'detail' => 'Solicitud para editor registrada'];
  }
}

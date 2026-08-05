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

  public function datosPaso1(Request $request) {
    $publicacion_id = $request->query('publicacion_id');

    $data = DB::table('Publicacion_revista_editor')
      ->where('id', '=', $publicacion_id)
      ->first();

    return [
      'data' => $data
    ];
  }

  public function registrarPaso1(Request $request) {
    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;
    $publicacion_id = $request->input('publicacion_id');

    // Preparamos los datos comunes tanto para actualizar como para crear
    $payload = [
      'investigador_id' => $investigador_id,
      'issn' => $request->input('issn'),
      'issne' => $request->input('issn_e') ?? $request->input('issne'),
      'revista' => $request->input('revista'),
      'casa' => $request->input('casa'),
      'pais' => $request->input('pais'), 
      'fecha_inicio' => $request->input('fecha_inicio'),
      'fecha_fin' => $request->input('fecha_fin') != "" ? $request->input('fecha_fin') : null,
      'updated_at' => Carbon::now()
    ];

    // Lógica si es una EDICIÓN (el publicacion_id ya existe)
    if ($publicacion_id != null) {
      DB::table('Publicacion_revista_editor')
        ->where('id', '=', $publicacion_id)
        ->update($payload);

      return [
        'message' => 'success',
        'detail' => 'Registro actualizado correctamente',
        'publicacion_id' => $publicacion_id
      ];
    } 
    
    // Lógica si es un NUEVO REGISTRO
    $payload['estado'] = 0;
    $payload['created_at'] = Carbon::now();

    // Guardamos y capturamos el ID autogenerado
    $newId = DB::table('Publicacion_revista_editor')->insertGetId($payload);

    return [
      'message' => 'success',
      'detail' => 'Datos guardados correctamente',
      'publicacion_id' => $newId
    ];
  }
}
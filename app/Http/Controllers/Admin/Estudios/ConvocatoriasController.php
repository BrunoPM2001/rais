<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConvocatoriasController extends Controller {
  public function listarConvocatorias() {
    $convocatorias = DB::table('Convocatoria')
      ->select(
        'id',
        'tipo',
        'fecha_inicial',
        'fecha_final',
        'fecha_corte',
        'periodo',
        'convocatoria',
        'estado'
      )
      ->where('evento', '=', 'registro')
      ->get();

    return ['data' => $convocatorias];
  }

  public function getOneConvocatoria($parent_id) {
    $convocatoria = DB::table('Convocatoria')
      ->select(
        'tipo',
        'evento',
        'fecha_inicial',
        'fecha_final',
        'fecha_corte',
        'periodo',
        'convocatoria',
        'estado'
      )
      ->where('id', '=', $parent_id)
      ->first();

    $convocatoria_calendario = DB::table('Convocatoria')
      ->select(
        'fecha_inicial',
        'fecha_final',
      )
      ->where('parent_id', '=', $parent_id)
      ->where('evento', '=', 'calendario')
      ->first();

    $convocatoria_evaluacion = DB::table('Convocatoria')
      ->select(
        'fecha_inicial',
        'fecha_final',
      )
      ->where('parent_id', '=', $parent_id)
      ->where('evento', '=', 'evaluacion')
      ->first();

    return ['data' => [
      'tipo' => $convocatoria->tipo ?? "",
      'evento' => $convocatoria->evento ?? "",
      'periodo' => $convocatoria->periodo ?? "",
      'convocatoria' => $convocatoria->convocatoria ?? "",
      'fecha_inicial' => $convocatoria->fecha_inicial ?? "",
      'fecha_final' => $convocatoria->fecha_final ?? "",
      'fecha_corte' => $convocatoria->fecha_final ?? "",
      'fecha_inicial_calendario' => $convocatoria_calendario->fecha_inicial ?? "",
      'fecha_final_calendario' => $convocatoria_calendario->fecha_final ?? "",
      'fecha_inicial_evaluacion' => $convocatoria_evaluacion->fecha_inicial ?? "",
      'fecha_final_evaluacion' => $convocatoria_evaluacion->fecha_final ?? "",
    ]];
  }

  public function listaEvaluaciones() {
    $evaluaciones = DB::table('Evaluacion_template')
      ->select(
        'id',
        'tipo',
        'periodo',
        'estado'
      )
      ->get();

    return ['data' => $evaluaciones];
  }

  public function verCriteriosEvaluacion($evaluacion_id) {
    $criterios = DB::table('Evaluacion_template_opcion AS a')
      ->join('Evaluacion_template AS b', 'b.id', '=', 'a.evaluacion_template_id')
      ->select(
        'a.id',
        'a.opcion',
        'a.puntaje_max',
        'a.nivel',
        'a.orden',
        'a.editable',
        'a.otipo',
        'a.puntos_adicionales'
      )
      ->where('b.id', '=', $evaluacion_id)
      ->orderBy('a.orden')
      ->get();

    $evaluacion = DB::table('Evaluacion_template')
      ->select(
        'id',
        'tipo',
        'periodo',
        'estado'
      )
      ->where('id', '=', $evaluacion_id)
      ->get();

    return ['evaluacion' => $evaluacion, 'criterios' => $criterios];
  }

  public function deleteConvocatoria(Request $request) {
    DB::table('Convocatoria')
      ->select(
        'id',
        'tipo',
        'evento',
        'fecha_inicial',
        'fecha_final',
        'fecha_corte',
        'periodo',
        'convocatoria',
        'estado'
      )
      ->where('id', '=', $request->id)
      ->orWhere('parent_id', '=', $request->id)
      ->delete();

    return ['message' => 'info', 'detail' => 'Convocatoria eliminada completamente'];
  }

  public function updateConvocatoria(Request $request) {
    //  Convocatoria de registro
    DB::table('Convocatoria')
      ->where('id', '=', $request->id)
      ->update([
        'periodo' => $request->periodo,
        'convocatoria' => $request->convocatoria,
        'fecha_inicial' => $request->fecha_inicial,
        'fecha_final' => $request->fecha_final,
        'fecha_corte' => $request->fecha_corte
      ]);

    //  Convocatoria calendario
    DB::table('Convocatoria')
      ->where('parent_id', '=', $request->id)
      ->where('evento', '=', 'calendario')
      ->updateOrInsert([
        'parent_id' => $request->id,
        'evento' => 'calendario',
        'fecha_inicial' => $request->fecha_inicial_calendario,
        'fecha_final' => $request->fecha_final_calendario,
      ]);

    //  Convocatoria evaluacion
    DB::table('Convocatoria')
      ->where('parent_id', '=', $request->id)
      ->where('evento', '=', 'evaluacion')
      ->updateOrInsert([
        'parent_id' => $request->id,
        'evento' => 'evaluacion',
        'fecha_inicial' => $request->fecha_inicial_evaluacion,
        'fecha_final' => $request->fecha_final_evaluacion,
      ]);

    return ['message' => 'info', 'detail' => 'Convocatoria actualizada'];
  }

  public function createConvocatoria(Request $request) {
    //  Convocatoria de registro
    $id = DB::table('Convocatoria')
      ->insertGetId([
        'tipo' => $request->tipo,
        'periodo' => $request->periodo,
        'evento' => 'registro',
        'estado' => 1,
        'convocatoria' => $request->convocatoria,
        'fecha_inicial' => $request->fecha_inicial,
        'fecha_final' => $request->fecha_final,
        'fecha_corte' => $request->fecha_corte
      ]);

    //  Convocatoria calendario
    DB::table('Convocatoria')
      ->insert([
        'parent_id' => $id,
        'evento' => 'calendario',
        'fecha_inicial' => $request->fecha_inicial_calendario,
        'fecha_final' => $request->fecha_final_calendario,
      ]);

    //  Convocatoria evaluacion
    DB::table('Convocatoria')
      ->insert([
        'parent_id' => $id,
        'evento' => 'evaluacion',
        'fecha_inicial' => $request->fecha_inicial_evaluacion,
        'fecha_final' => $request->fecha_final_evaluacion,
      ]);

    return ['message' => 'success', 'detail' => 'Convocatoria creada'];
  }
}

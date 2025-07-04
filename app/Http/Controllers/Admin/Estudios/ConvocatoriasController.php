<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConvocatoriasController extends Controller {

  public function createConvocatoria(Request $request) {
    //  Convocatoria de registro
    $id = DB::table('Convocatoria')
      ->insertGetId([
        'tipo' => $request->input('tipo'),
        'descripcion' => $request->input('descripcion'),
        'periodo' => $request->input('periodo'),
        'evento' => 'registro',
        'estado' => 1,
        'convocatoria' => $request->input('convocatoria'),
        'fecha_inicial' => $request->input('fecha_inicio_registro'),
        'fecha_final' => $request->input('fecha_fin_registro'),
        'fecha_corte' => $request->input('fecha_corte_registro'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    //  Convocatoria calendario
    DB::table('Convocatoria')
      ->insert([
        'tipo' => $request->input('tipo'),
        'periodo' => $request->input('periodo'),
        'parent_id' => $id,
        'evento' => 'calendario',
        'fecha_inicial' => $request->input('fecha_inicio_calendario'),
        'fecha_final' => $request->input('fecha_fin_calendario'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    //  Convocatoria evaluacion
    DB::table('Convocatoria')
      ->insert([
        'tipo' => $request->input('tipo'),
        'periodo' => $request->input('periodo'),
        'parent_id' => $id,
        'evento' => 'evaluacion',
        'fecha_inicial' => $request->input('fecha_inicio_evaluacion'),
        'fecha_final' => $request->input('fecha_fin_evaluacion'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'success', 'detail' => 'Convocatoria creada'];
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
        'evento' => 'calendario'
      ], [
        'fecha_inicial' => $request->fecha_inicial_calendario,
        'fecha_final' => $request->fecha_final_calendario,
      ]);

    //  Convocatoria evaluacion
    DB::table('Convocatoria')
      ->where('parent_id', '=', $request->id)
      ->where('evento', '=', 'evaluacion')
      ->updateOrInsert([
        'parent_id' => $request->id,
        'evento' => 'evaluacion'
      ], [
        'fecha_inicial' => $request->fecha_inicial_evaluacion,
        'fecha_final' => $request->fecha_final_evaluacion,
      ]);

    return ['message' => 'info', 'detail' => 'Convocatoria actualizada'];
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

  public function listarConvocatorias() {
    $convocatorias = DB::table('Convocatoria')
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
      ->whereIn('evento', ['registro', 'evaluacion'])
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


  public function listadoProyectosCopia() {
    $proyectos = DB::table('Evaluacion_template AS a')
      ->join('Evaluacion_template_opcion AS b', 'b.evaluacion_template_id', '=', 'a.id')
      ->select([
        'a.id AS value',
        DB::raw("CONCAT(a.periodo, ' - ', a.tipo) AS label")
      ])
      ->groupBy('a.id')
      ->get();

    return $proyectos;
  }

  public function createEvaluacion(Request $request) {
    $date = Carbon::now();

    $id = DB::table('Evaluacion_template')
      ->insertGetId([
        'tipo' => $request->input('tipo')["value"],
        'periodo' => $request->input('periodo'),
        'estado' => 'EN PROCESO',
        'created_at' => $date,
        'updated_at' => $date
      ]);

    if ($request->input('copiar')["value"] == "Sí") {
      $criterios = DB::table('Evaluacion_template_opcion')
        ->select([
          'opcion',
          'puntaje_max',
          'nivel',
          'orden',
          'editable',
          'otipo',
          'puntos_adicionales',
        ])
        ->where('evaluacion_template_id', '=', $request->input('proyecto_copia')["value"])
        ->get();

      foreach ($criterios as $item) {
        DB::table('Evaluacion_template_opcion')
          ->insert([
            'evaluacion_template_id' => $id,
            'opcion' => $item->opcion,
            'puntaje_max' => $item->puntaje_max,
            'nivel' => $item->nivel,
            'orden' => $item->orden,
            'editable' => $item->editable,
            'otipo' => $item->otipo,
            'puntos_adicionales' => $item->puntos_adicionales,
            'tipo' => $request->input('tipo')["value"],
            'periodo' => $request->input('periodo'),
            'created_at' => $date,
            'updated_at' => $date
          ]);
      }

      return ['message' => 'success', 'detail' => 'Criterios copiados correctamente'];
    }
    return ['message' => 'success', 'detail' => 'Evaluación creada en blanco exitosamente'];
  }

  //  Criterios
  public function detalleCriterios(Request $request) {
    $criterios = DB::table('Evaluacion_template_opcion AS a')
      ->join('Evaluacion_template AS b', 'b.id', '=', 'a.evaluacion_template_id')
      ->select(
        'a.id',
        'a.opcion',
        'a.puntaje_max',
        'a.nivel',
        'a.orden',
        DB::raw("CASE(a.editable)
          WHEN 1 THEN 'Sí'
          ELSE 'No'
        END AS editable"),
        'a.otipo',
        'a.periodo',
        'a.puntos_adicionales'
      )
      ->where('b.id', '=', $request->query('id'))
      ->orderBy('a.orden')
      ->get();

    $evaluacion = DB::table('Evaluacion_template')
      ->select(
        'id',
        'tipo',
        'periodo',
        'estado'
      )
      ->where('id', '=', $request->query('id'))
      ->first();

    return ['evaluacion' => $evaluacion, 'criterios' => $criterios];
  }

  public function createCriterio(Request $request) {
    $date = Carbon::now();
    $last = DB::table('Evaluacion_template AS a')
      ->leftJoin('Evaluacion_template_opcion AS b', 'b.evaluacion_template_id', '=', 'a.id')
      ->select([
        'b.orden',
        'a.tipo'
      ])
      ->where('a.id', '=', $request->input('id'))
      ->orderByDesc('b.orden')
      ->first();

    DB::table('Evaluacion_template_opcion')
      ->insert([
        'evaluacion_template_id' => $request->input('id'),
        'opcion' => $request->input('opcion'),
        'puntaje_max' => $request->input('puntaje_max'),
        'nivel' => $request->input('nivel')["value"],
        'periodo' => $request->input('periodo'),
        'editable' => $request->input('editable')["value"],
        'otipo' => $request->input('otipo')["value"],
        'puntos_adicionales' => $request->input('puntos_adicionales'),
        'orden' => ($last->orden ?? 0 + 1),
        'tipo' => $last->tipo,
        'created_at' => $date,
        'updated_at' => $date
      ]);

    return ['message' => 'success', 'detail' => 'Criterio añadido correctamente'];
  }

  public function editCriterio(Request $request) {
    DB::table('Evaluacion_template_opcion')
      ->where('id', '=', $request->input('id'))
      ->update([
        'opcion' => $request->input('opcion'),
        'puntaje_max' => $request->input('puntaje_max'),
        'nivel' => $request->input('nivel')["value"],
        'editable' => $request->input('editable')["value"],
        'otipo' => $request->input('otipo')["value"],
        'puntos_adicionales' => $request->input('puntos_adicionales'),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Criterio actualizado'];
  }

  public function reOrdenarCriterios(Request $request) {
    $index = 1;
    foreach ($request->input('criterios') as $item) {
      DB::table('Evaluacion_template_opcion')
        ->where('id', '=', $item["id"])
        ->update([
          'orden' => $index
        ]);
      $index++;
    }

    return ['message' => 'info', 'detail' => 'Autores reordenados'];
  }

  public function aprobarCriterios(Request $request) {
    DB::table('Evaluacion_template')
      ->where('id', '=', $request->input('id'))
      ->update([
        'estado' => 'APROBADO',
        'updated_at' => Carbon::now()
      ]);

    $registros = DB::table('Evaluacion_template_opcion')
      ->select([
        'opcion',
        'puntaje_max',
        'nivel',
        'orden',
        'tipo',
        'periodo',
        'editable',
        'otipo',
        'puntos_adicionales'
      ])
      ->where('evaluacion_template_id', '=', $request->input('id'))
      ->get()
      ->map(function ($item) {
        return (array) $item;
      })
      ->toArray();

    DB::table('Evaluacion_opcion')->insert($registros);

    return ['message' => 'info', 'detail' => 'Criterios aprobados'];
  }

  //  Lista de partidas en conjunto
  public function listadoGruposPartidas() {
    $grupos = DB::table('Partida_grupo AS a')
      ->leftJoin('Partida_proyecto_grupo AS b', 'b.partida_grupo_id', '=', 'a.id')
      ->select([
        'a.id',
        'a.tipo_proyecto',
        'a.nombre',
        'a.monto_max',
        DB::raw("COUNT(b.id) AS partidas")
      ])
      ->groupBy('a.id')
      ->get();

    return $grupos;
  }

  public function dataGrupoPartidas(Request $request) {
    $partidas = DB::table('Partida_proyecto_grupo AS a')
      ->join('Partida_grupo AS b', 'b.id', '=', 'a.partida_grupo_id')
      ->join('Partida_proyecto AS c', 'c.id', '=', 'a.partida_proyecto_id')
      ->join('Partida AS d', 'd.id', '=', 'c.partida_id')
      ->select([
        'a.id',
        'd.tipo',
        'd.partida',
      ])
      ->where('b.id', '=', $request->query('id'))
      ->get();

    return $partidas;
  }

  public function addGrupo(Request $request) {
    DB::table('Partida_grupo')
      ->insert([
        'tipo_proyecto' => $request->input('tipo_proyecto'),
        'nombre' => $request->input('nombre'),
        'monto_max' => $request->input('monto_max'),
      ]);

    return ['message' => 'success', 'detail' => 'Grupo creado correctamente'];
  }

  public function dataCreatePartida(Request $request) {
    $partidas = DB::table('Partida_proyecto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
        'b.tipo',
      ])
      ->where('a.tipo_proyecto', '=', $request->query('tipo_proyecto'))
      ->get();

    return $partidas;
  }

  public function addPartida(Request $request) {
    DB::table('Partida_proyecto_grupo')
      ->insert([
        'partida_grupo_id' => $request->input('id'),
        'partida_proyecto_id' => $request->input('partida_id'),
      ]);

    return ['message' => 'success', 'detail' => 'Partida agregada'];
  }

  public function deletePartida(Request $request) {
    DB::table('Partida_proyecto_grupo')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Partida eliminada'];
  }

  public function saveGrupo(Request $request) {
    DB::table('Partida_grupo')
      ->where('id', '=', $request->input('id'))
      ->update([
        'nombre' => $request->input('nombre'),
        'monto_max' => $request->input('monto_max'),
      ]);

    return ['message' => 'info', 'detail' => 'Datos actualizados'];
  }
}

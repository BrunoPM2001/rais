<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionEvaluadorController extends Controller {

  public function listado() {
    $date = Carbon::now();
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->leftJoin('Proyecto_evaluacion AS d', 'd.proyecto_id', '=', 'a.id')
      ->join('Convocatoria AS e', function ($join) use ($date) {
        $join->on('e.tipo', '=', 'a.tipo_proyecto')
          ->on('e.periodo', '=', 'a.periodo')
          ->where('e.evento', '=', 'evaluacion')
          ->where('e.fecha_inicial', '<', $date)
          ->where('e.fecha_final', '>', $date);
      })
      ->leftJoin('Usuario_evaluador AS f', 'f.id', '=', 'd.evaluador_id')
      ->select([
        'a.id',
        'a.tipo_proyecto',
        'a.periodo',
        'b.nombre AS linea',
        'c.nombre AS facultad',
        'a.titulo',
        'e.fecha_inicial',
        'e.fecha_final',
        DB::raw("GROUP_CONCAT(CONCAT(f.nombres, ' ', f.apellidos) SEPARATOR ', ') AS evaluadores")
      ])
      ->whereIn('a.estado', [3, 5])
      ->groupBy('a.id')
      ->get();

    return $proyectos;
  }

  public function evaluadoresProyecto(Request $request) {
    $evaluadores = DB::table('Proyecto_evaluacion AS a')
      ->join('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->select([
        'b.id',
        DB::raw("CONCAT(b.nombres, ' ', b.apellidos) AS evaluador")
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $evaluadores;
  }

  public function searchEvaluadorBy(Request $request) {
    $date = Carbon::now();
    $evaluadores = DB::table('Usuario_evaluador AS a')
      ->leftJoin('Proyecto_evaluacion AS b', 'b.evaluador_id', '=', 'a.id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->leftJoin('Convocatoria AS d', function ($join) use ($date) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo')
          ->where('d.evento', '=', 'evaluacion')
          ->where('d.fecha_inicial', '<', $date)
          ->where('d.fecha_final', '>', $date);
      })
      ->select(
        'a.id',
        DB::raw("CONCAT(a.nombres, ' ', a.apellidos, ' | ', COUNT(d.id)) AS value"),
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.id')
      ->limit(10)
      ->get();

    return $evaluadores;
  }

  public function updateEvaluadores(Request $request) {

    $elementosA = $request->input('evaluadores');
    $elementosC = $request->input('proyectos');

    $a_ids = array_column($elementosA, 'id');
    $c_ids = array_column($elementosC, 'id');

    foreach ($c_ids as $proyecto_id) {
      $elementosB = DB::table('Proyecto_evaluacion')
        ->select(['evaluador_id AS id'])
        ->where('proyecto_id', '=', $proyecto_id)
        ->get()
        ->toArray();

      $b_ids = array_column($elementosB, 'id');

      //  Validar que se hayan escogido diferentes evaluadores
      if (count($a_ids) !== count(array_unique($a_ids))) {
        return ['message' => 'error', 'detail' => 'Los evaluadores escogidos deben ser diferentes'];
      }

      // Encontrar los IDs en $a que no están en $b
      $ids_in_a_not_in_b = array_diff($a_ids, $b_ids);

      // Encontrar los IDs en $b que no están en $a
      $ids_in_b_not_in_a = array_diff($b_ids, $a_ids);


      if (!empty($ids_in_b_not_in_a)) {
        foreach ($ids_in_b_not_in_a as $id) {
          DB::table('Proyecto_evaluacion')
            ->where('evaluador_id', '=', $id)
            ->where('proyecto_id', '=', $proyecto_id)
            ->delete();
        }
      }

      if (!empty($ids_in_a_not_in_b)) {
        foreach ($ids_in_a_not_in_b as $id) {
          DB::table('Proyecto_evaluacion')
            ->insert([
              'evaluador_id' => $id,
              'proyecto_id' => $proyecto_id
            ]);
        }
      }
    }

    if (sizeof($a_ids) == 0) {
      DB::table('Proyecto')
        ->whereIn('id', $c_ids)
        ->update([
          'estado' => 5
        ]);
    } else {
      DB::table('Proyecto')
        ->whereIn('id', $c_ids)
        ->update([
          'estado' => 3
        ]);
    }

    return ['message' => 'success', 'detail' => 'Evaluadores actualizados'];
  }
}

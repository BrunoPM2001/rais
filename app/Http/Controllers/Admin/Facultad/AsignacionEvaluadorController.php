<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AsignacionEvaluadorController extends Controller {
  public function main() {
    return view('admin.facultad.asignacion_evaluadores');
  }

  public function searchEvaluadorBy($input) {
    $evaluadores = DB::table('Usuario_evaluador AS a')
      ->select(
        'id',
        'apellidos',
        'nombres'
      )
      ->orWhere('apellidos', 'LIKE', '%' . $input . '%')
      ->orWhere('nombres', 'LIKE', '%' . $input . '%')
      ->limit(10)
      ->get();

    return $evaluadores;
  }

  public function getEvaluadoresProyecto($id) {
    $evaluadores = DB::table('Proyecto_evaluacion AS a')
      ->join('Usuario_evaluador AS b', 'a.evaluador_id', '=', 'b.id')
      ->select(
        'b.id',
        DB::raw('CONCAT(b.apellidos, ", ", b.nombres) AS evaluador')
      )
      ->where('a.proyecto_id', '=', $id)
      ->get();

    return ['data' => $evaluadores];
  }
}

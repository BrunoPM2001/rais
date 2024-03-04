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
}

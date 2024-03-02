<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;

class AsignacionEvaluadorController extends Controller {
  public function main() {
    return view('admin.facultad.asignacion_evaluadores');
  }
}

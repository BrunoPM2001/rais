<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProyectosEvaluadosController extends Controller {
  public function main() {
    return view('admin.facultad.proyectos_evaluados');
  }
}

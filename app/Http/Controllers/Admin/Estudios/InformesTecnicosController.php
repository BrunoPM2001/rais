<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InformesTecnicosController extends Controller {

  public function listado($periodo) {
    $proyectos = DB::table('Proyectos AS a')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        ''
      );
  }
}

<?php

namespace App\Http\Controllers\Investigador\Informes;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Informe_academicoController extends S3Controller {
  public function listadoProyectos(Request $request) {
    $informes = DB::table('Proyecto AS a')
      ->join('Geco_proyecto AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante AS c', 'c.proyecto_id', '=', 'b.proyecto_id')
      ->join('Usuario_investigador AS d', 'd.id', '=', 'c.investigador_id')
      ->select(
        'b.id',
        'a.periodo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
      )
      ->where('c.condicion', '=', 'Responsable')
      ->where('d.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    return $informes;
  }
}

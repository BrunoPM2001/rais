<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ConsolidadoGeneralController extends Controller {

  public function reporte($periodo) {
    //  Listado de proyectos
    $tipos = DB::table('Proyecto AS a')
      ->select(
        'a.tipo_proyecto'
      )
      ->where('a.periodo', '=', $periodo)
      ->groupBy('a.tipo_proyecto')
      ->orderBy('a.tipo_proyecto')
      ->get();

    //  Proyectos
    $proyectos = DB::table('Proyecto AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'b.nombre AS facultad',
        'a.tipo_proyecto',
        DB::raw('COUNT(a.id) AS cuenta')
      )
      ->where('a.periodo', '=', $periodo)
      ->groupBy('a.facultad_id', 'a.tipo_proyecto')
      ->orderBy('a.facultad_id')
      ->orderBy('a.tipo_proyecto')
      ->get();

    return ['tipos' => $tipos, 'proyectos' => $proyectos];
  }
}

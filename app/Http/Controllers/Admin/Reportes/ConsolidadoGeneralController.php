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

    $countExp = [];

    foreach ($tipos as $tipo) {
      $countExp[] = DB::raw('COUNT(IF(a.tipo_proyecto = "' . $tipo->tipo_proyecto . '", 1, NULL)) AS "' . $tipo->tipo_proyecto . '"');
    }


    //  Proyectos
    $proyectos = DB::table('Proyecto AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'b.nombre AS facultad',
        DB::raw('COUNT(a.tipo_proyecto) AS total_cuenta'),
        ...$countExp,
      )
      ->where('a.periodo', '=', $periodo)
      ->groupBy('a.facultad_id')
      ->orderBy('a.facultad_id')
      ->get();

    $pdf = Pdf::loadView('admin.reportes.consolidadoGeneralPDF', [
      'proyectos' => $proyectos,
      'tipos' => $tipos,
      'periodo' => $periodo
    ]);
    $pdf->setPaper('A4', 'landscape');
    return $pdf->stream();
  }
}

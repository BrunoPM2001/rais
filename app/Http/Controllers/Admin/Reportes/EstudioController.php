<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class EstudioController extends Controller {

  public function tiposProyecto() {
    $tiposHistoricos = DB::table('Proyecto_H')
        ->select('tipo as value')
        ->distinct();

    //$tiposActuales = DB::table('Proyecto')
        //->select('tipo_proyecto as value')
        //->distinct();

    $tipos = $tiposHistoricos
        //->union($tiposActuales)
        ->get();

    return $tipos;
  }

  public function periodosPorTipo($tipo) {
    $historico = DB::table('Proyecto_H')
        ->select('periodo')
        ->where('tipo', $tipo);

    //$actual = DB::table('Proyecto')
    //    ->select('periodo')
    //    ->where('tipo_proyecto', $tipo);

    $periodos = $historico
        //->union($actual)
        ->distinct()
        ->orderByDesc('periodo')
        ->get();

    return $periodos;
  }

  public function reporte($tipo, $periodo, $facultad) {
    $proyectos = DB::table('Proyecto_H AS a')
      ->join('Proyecto_integrante_H AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        'd.nombre AS facultad_proyecto',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.monto',
        'b.condicion',
        'b.codigo AS codigo_investigador',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, ", ", c.nombres) AS nombres')
      )
      ->where('a.tipo', '=', $tipo)
      ->where('a.periodo', '=', $periodo)
      ->where('a.facultad_id', '=', $facultad)
      ->where('a.status', '=', 1)
      ->where('a.excluido', '=', 0)
      ->orderBy('a.facultad_id')
      ->orderBy('a.titulo')
      ->orderBy('b.condicion', 'desc')
      ->get();

    $pdf = Pdf::loadView('admin.reportes.estudioPDF', ['lista' => $proyectos, 'periodo' => $periodo]);
    return $pdf->stream();
  }
}

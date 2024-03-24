<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller {
  public function reporte($facultad_id, $periodo) {
    $presupuesto = DB::table('Proyecto AS a')
      ->join('Proyecto_presupuesto AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Partida AS c', 'c.id', '=', 'b.partida_id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'c.tipo',
        'c.codigo',
        'c.partida',
        'b.monto'
      )
      ->where('a.facultad_id', '=', $facultad_id)
      ->where('a.periodo', '=', $periodo)
      ->orderBy('a.facultad_id')
      ->orderBy('a.titulo')
      ->orderBy('c.tipo')
      ->orderBy('c.codigo')
      ->get();
    $montos = DB::table('Proyecto AS a')
      ->join('Proyecto_presupuesto AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Partida AS c', 'c.id', '=', 'b.partida_id')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw('SUM(b.monto) AS monto')
      )
      ->where('a.facultad_id', '=', $facultad_id)
      ->where('a.periodo', '=', $periodo)
      ->groupBy('a.id')
      ->orderBy('a.facultad_id')
      ->orderBy('a.titulo')
      ->get();

    $pdf = Pdf::loadView('admin.reportes.presupuestoPDF', [
      'lista' => $presupuesto,
      'montos' => $montos,
      'periodo' => $periodo
    ]);
    return $pdf->stream();
  }
}

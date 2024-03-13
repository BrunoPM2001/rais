<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DocenteController extends Controller {
  public function reporte($investigador_id) {
    //  ANTIGUOS
    $proyectos_antiguos = DB::table('Proyecto_integrante_H AS a')
      ->join('Proyecto_H AS b', 'b.id', '=', 'a.proyecto_id')
      ->select(
        'b.codigo',
        'b.titulo',
        'b.periodo',
        'b.tipo',
        'a.condicion'
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->orderBy('b.periodo', 'desc')
      ->orderBy('b.codigo', 'desc')
      ->get();

    //  Nuevos
    $proyectos_nuevos = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select(
        'b.codigo_proyecto',
        'b.titulo',
        'b.periodo',
        'b.tipo_proyecto',
        'a.condicion'
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->orderBy('b.periodo', 'desc')
      ->orderBy('b.codigo_proyecto', 'desc')
      ->get();

    //  Investigador
    $investigador = DB::table('Usuario_investigador AS a')
      ->select(
        'a.codigo',
        DB::raw('CONCAT(a.apellido1, " ", a.apellido2, ", ", a.nombres) AS nombres')
      )
      ->where('a.id', '=', $investigador_id)
      ->get();

    $pdf = Pdf::loadView('admin.reportes.docentePDF', [
      'proyectos_antiguos' => $proyectos_antiguos,
      'proyectos_nuevos' => $proyectos_nuevos,
      'investigador' => $investigador
    ]);
    return $pdf->stream();
  }
}

<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller {
  public function reporte($estado, $facultad, $miembros) {
    $lista = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select(
        'a.grupo_nombre_corto',
        'a.grupo_nombre',
        'd.nombre AS facultad_grupo',
        'a.estado',
        'b.condicion',
        'c.doc_numero',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, " ", c.nombres) AS nombre'),
        'b.tipo',
        'e.nombre AS facultad_miembro'
      )
      ->where('a.facultad_id', '=', $facultad)
      ->where('a.estado', '=', $estado)
      ->whereNull('b.fecha_exclusion')
      ->orderBy('a.grupo_nombre')
      ->orderBy('b.condicion')
      ->get();

    $pdf = Pdf::loadView('admin.reportes.grupoPDF', ['lista' => $lista]);
    return $pdf->stream();
    return ['data' => $lista];
  }
}

<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller {
  public function reporte($facultad, $tipo, $periodo) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->leftJoin('Proyecto_integrante_tipo AS d', 'd.id', '=', 'b.proyecto_integrante_tipo_id')
      ->leftJoin('Proyecto_presupuesto AS e', 'e.proyecto_id', '=', 'a.id')
      ->leftJoin('Grupo_integrante AS f', 'f.id', '=', 'b.grupo_integrante_id')
      ->leftJoin('Grupo AS g', 'g.id', '=', 'b.grupo_id')
      ->leftJoin('Facultad AS h', 'h.id', '=', 'c.facultad_id')
      ->leftJoin('Facultad AS i', 'i.id', '=', 'g.facultad_id')
      ->select(
        'g.grupo_nombre',
        'i.nombre AS facultad_grupo',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw('SUM(e.monto) AS presupuesto'),
        'd.nombre AS condicion',
        'c.codigo',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, ", ", c.nombres) AS nombres'),
        'f.tipo',
        'h.nombre AS facultad_miembro',
        'f.condicion AS condicion_gi'
      )
      ->where('a.facultad_id', '=', $facultad)
      ->where('a.tipo_proyecto', '=', $tipo)
      ->where('a.periodo', '=', $periodo)
      ->where('a.estado', '>', '0')
      ->groupBy('a.id', 'c.id', 'd.id', 'f.id', 'g.id')
      ->get();

    $pdf = Pdf::loadView('admin.reportes.proyectoPDF', ['lista' => $proyectos, 'periodo' => $periodo]);
    return $pdf->stream();
  }
}

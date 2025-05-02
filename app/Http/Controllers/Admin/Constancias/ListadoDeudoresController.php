<?php

namespace App\Http\Controllers\Admin\Constancias;

use App\Exports\Admin\FromDataExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ListadoDeudoresController extends Controller {
  public function listado() {
    $deudasA = DB::table('Proyecto_integrante_deuda AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->leftJoin('Proyecto AS d', 'd.id', '=', 'b.proyecto_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
      ->leftJoin('Licencia AS f', 'f.investigador_id', '=', 'c.id')
      ->leftJoin('Licencia_tipo AS g', 'g.id', '=', 'f.licencia_tipo_id')
      ->leftJoin('Facultad AS h', 'h.id', '=', 'd.facultad_id')
      ->leftJoin('Proyecto_integrante_tipo AS i', 'i.id', '=', 'b.proyecto_integrante_tipo_id')
      ->select([
        'a.id',
        'c.codigo',
        'c.doc_numero',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'e.nombre AS facultad_investigador',
        'c.tipo',
        'g.tipo AS licencia_tipo',
        'i.nombre AS condicion',
        'd.tipo_proyecto',
        'd.codigo_proyecto',
        'd.titulo',
        'h.nombre AS facultad_proyecto',
        'd.periodo',
        'a.detalle',
        'a.categoria',
      ])
      ->whereBetween('a.tipo', [1, 3])
      ->whereNotIn('g.id', [6, 7])
      ->groupBy('a.id')
      ->orderByDesc('d.periodo');

    $deudasB = DB::table('Proyecto_integrante_deuda AS a')
      ->join('Proyecto_integrante_H AS b', 'b.id', '=', 'a.proyecto_integrante_h_id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->leftJoin('Proyecto_H AS d', 'd.id', '=', 'b.proyecto_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
      ->leftJoin('Licencia AS f', 'f.investigador_id', '=', 'c.id')
      ->leftJoin('Licencia_tipo AS g', 'g.id', '=', 'f.licencia_tipo_id')
      ->leftJoin('Facultad AS h', 'h.id', '=', 'd.facultad_id')
      ->select([
        'a.id',
        'c.codigo',
        'c.doc_numero',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'e.nombre AS facultad_investigador',
        'c.tipo',
        'g.tipo AS licencia_tipo',
        'b.condicion',
        'd.tipo AS tipo_proyecto',
        'd.codigo AS codigo_proyecto',
        'd.titulo',
        'h.nombre AS facultad_proyecto',
        'd.periodo',
        'a.detalle',
        'a.categoria',
      ])
      ->whereBetween('a.tipo', [1, 3])
      ->whereNotIn('g.id', [6, 7])
      ->groupBy('a.id')
      ->orderByDesc('d.periodo')
      ->union($deudasA)
      ->get();

    return $deudasB;
  }

  public function listadoDeudoresExcel(Request $request) {

    $data = $request->all();

    $export = new FromDataExport($data);

    return Excel::download($export, 'listado.xlsx');
  }
}

<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller {

  public function searchCoordinador(Request $request) {
    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.grupo_id'
      ])
      ->where('a.cargo', '=', 'Coordinador')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function reporte(Request $request) {
    $lista = $request->query('grupo_id') ?
      DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
      ->join('Area AS f', 'f.id', '=', 'd.area_id')
      ->select(
        'a.grupo_nombre_corto',
        'a.grupo_nombre',
        'f.nombre AS area',
        'f.sigla',
        'd.nombre AS facultad_grupo',
        'a.grupo_categoria',
        'a.estado',
        DB::raw("CASE
          WHEN b.cargo = 'Coordinador'  THEN 'Coordinador'
          WHEN b.condicion = 'Titular' THEN 'Titular'
          WHEN b.condicion = 'Adherente' AND c.tipo != 'Externo' THEN 'Adherente'
          WHEN b.condicion = 'Adherente' AND c.tipo = 'Externo' THEN 'Adherente externo'
          ELSE b.condicion
        END AS condicion"),
        'c.codigo',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, ", ", c.nombres) AS nombre'),
        'c.tipo',
        'e.nombre AS facultad_miembro'
      )
      ->where('a.id', '=', $request->query('grupo_id'))
      ->whereNull('b.fecha_exclusion')
      ->orderBy('a.grupo_nombre')
      ->orderByRaw("CASE
        WHEN b.cargo = 'Coordinador' THEN 1
        WHEN b.condicion = 'Titular' THEN 2
        WHEN b.condicion = 'Adherente' AND c.tipo != 'Externo' THEN 3
        WHEN b.condicion = 'Adherente' AND c.tipo = 'Externo' THEN 4
        ELSE 5
      END")
      ->get()
      :
      DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
      ->join('Area AS f', 'f.id', '=', 'd.area_id')
      ->select(
        'a.grupo_nombre_corto',
        'a.grupo_nombre',
        'f.nombre AS area',
        'f.sigla',
        'd.nombre AS facultad_grupo',
        'a.grupo_categoria',
        'a.estado',
        DB::raw("CASE
          WHEN b.cargo = 'Coordinador'  THEN 'Coordinador'
          WHEN b.condicion = 'Titular' THEN 'Titular'
          WHEN b.condicion = 'Adherente' AND c.tipo != 'Externo' THEN 'Adherente'
          WHEN b.condicion = 'Adherente' AND c.tipo = 'Externo' THEN 'Adherente externo'
          ELSE b.condicion
        END AS condicion"),
        'c.codigo',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, ", ", c.nombres) AS nombre'),
        'c.tipo',
        'e.nombre AS facultad_miembro'
      )
      ->where('a.facultad_id', '=', $request->query('facultad'))
      ->where('a.estado', '=', $request->query('estado'))
      ->whereNull('b.fecha_exclusion')
      ->orderBy('a.grupo_nombre')
      ->orderByRaw("CASE
        WHEN b.cargo = 'Coordinador' THEN 1
        WHEN b.condicion = 'Titular' THEN 2
        WHEN b.condicion = 'Adherente' AND c.tipo != 'Externo' THEN 3
        WHEN b.condicion = 'Adherente' AND c.tipo = 'Externo' THEN 4
        ELSE 5
      END")
      ->get();

    $pdf = Pdf::loadView('admin.reportes.grupoPDF', ['lista' => $lista]);
    return $pdf->stream();
  }
}

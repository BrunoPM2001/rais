<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DeudaProyectosController extends Controller {
  public function listadoProyectos1($periodo, $tipo_proyecto, $deuda) {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.periodo',
        'a.titulo',
        'b.nombre AS facultad',
        DB::raw("CASE
          WHEN a.deuda <= 3 THEN 'Sí'
          WHEN a.deuda > 3 THEN 'No'
        END AS deuda"),
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.estado', '=', 1);

    //  Filtros
    $proyectos = $periodo == 'null' ? $proyectos : $proyectos->where('a.periodo', '=', $periodo);
    $proyectos = $tipo_proyecto == 'null' ? $proyectos : $proyectos->where('a.tipo_proyecto', '=', $tipo_proyecto);
    $proyectos = $deuda == 'null' ? $proyectos : $proyectos->where('a.deuda', '=', $deuda);

    return ['data' => $proyectos->get()];
  }

  public function listadoIntegrantes($proyecto_id) {
    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Licencia AS d', 'd.investigador_id', '=', 'c.id')
      ->leftJoin('Licencia_tipo AS e', 'e.id', '=', 'd.licencia_tipo_id')
      ->leftJoin('Proyecto_integrante_deuda AS f', 'f.proyecto_integrante_id', '=', 'a.id')
      ->select(
        'a.id',
        'c.doc_numero',
        'c.apellido1',
        'c.apellido2',
        'c.nombres',
        'b.nombre AS condicion',
        'e.tipo AS licencia',
        'f.categoria AS tipo_deuda',
        'f.informe',
        'f.detalle',
        'f.fecha_sub'
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->get();

    return ['data' => $integrantes];
  }

  public function listadoProyectos($year, $investigadorId) {
    $deudas = DB::table('view_proyectos')
      ->select([
        'proyecto_id AS id',
        'codigo AS codigo_proyecto',
        'tipo AS tipo_proyecto',
        'periodo',
        'xtitulo AS titulo',
        'facultad',
        DB::raw("CASE
          WHEN (deuda IS NULL OR deuda <= 0) THEN 'NO'
          WHEN deuda > 0 AND deuda <= 3 THEN 'SI'
          WHEN deuda > 3 THEN 'SUBSANADA'
        END as deuda"),
        'fecha_inscripcion AS created_at',
        'updated_at'
      ])
      ->whereNotIn('tipo', ['PFEX', 'FEX', 'SIN-CON'])
      ->orderBy('fecha_inscripcion', 'DESC')
      ->orderBy('facultad', 'DESC')
      ->get();

    // if (!empty($year)) {
    //   $deudas->where('t1.periodo', $year);
    // }

    // if ($investigadorId) {
    //   $deudas->where('t1.investigador_id', $investigadorId);
    // }

    return $deudas;
  }

  public function listadoProyectosNoDeuda() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $lista = DB::table('Proyecto AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        'b.nombre AS facultad',
        'res.responsable',
        'a.deuda',
        'a.periodo'
      )
      ->where(function ($query) {
        $query->orWhere('a.deuda', '<', '1')
          ->orWhere('a.deuda', '=', 2)
          ->orWhere('a.deuda', '=', 8)
          ->orWhereNull('a.deuda');
      })
      ->get();

    return ['data' => $lista];
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeudaProyectosController extends Controller {
  public function listadoIntegrantes(Request $request) {
    if ($request->query('tabla') == "Nuevo") {
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
        ->where('a.proyecto_id', '=', $request->query('id'))
        ->get();

      return $integrantes;
    } else {
      $integrantes = DB::table('Proyecto_integrante_H AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->leftJoin('Licencia AS c', 'c.investigador_id', '=', 'b.id')
        ->leftJoin('Licencia_tipo AS d', 'd.id', '=', 'c.licencia_tipo_id')
        ->leftJoin('Proyecto_integrante_deuda AS e', 'e.proyecto_integrante_h_id', '=', 'a.id')
        ->select(
          'a.id',
          'b.doc_numero',
          'b.apellido1',
          'b.apellido2',
          'b.nombres',
          'a.condicion',
          'd.tipo AS licencia',
          'e.categoria AS tipo_deuda',
          'e.informe',
          'e.detalle',
          'e.fecha_sub'
        )
        ->where('a.proyecto_id', '=', $request->query('id'))
        ->get();

      return $integrantes;
    }
  }

  public function listadoProyectos() {
    $deudas = DB::table('view_proyectos')
      ->select([
        DB::raw("CONCAT(proyecto_origen, '_', proyecto_id) AS id"),
        DB::raw("CASE
          WHEN proyecto_origen COLLATE utf8mb4_unicode_ci = 'PROYECTO_BASE' THEN 'Nuevo'
          WHEN proyecto_origen COLLATE utf8mb4_unicode_ci = 'PROYECTO' THEN 'Antiguo'
        END as proyecto_origen"),
        'proyecto_id',
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

    return $lista;
  }
}

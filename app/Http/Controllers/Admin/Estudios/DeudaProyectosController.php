<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DeudaProyectosController extends Controller {
  public function listadoProyectos($periodo, $tipo_proyecto, $deuda) {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.periodo',
        'a.titulo',
        'b.nombre AS facultad',
        'a.deuda',
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
      ->select(
        'a.id',
        'c.doc_numero',
        'c.apellido1',
        'c.apellido2',
        'c.nombres',
        'b.nombre AS condicion',
        'e.tipo',
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->get();

    return ['data' => $integrantes];
  }
}

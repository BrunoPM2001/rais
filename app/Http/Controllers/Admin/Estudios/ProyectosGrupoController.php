<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProyectosGrupoController extends Controller {
  public function listado($periodo) {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.proyecto_id', '=', 'a.id')
      ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_presupuesto AS f', 'f.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'c.nombre AS linea',
        'a.titulo',
        'd.investigador_id',
        'b.grupo_nombre',
        'e.nombre AS facultad',
        DB::raw('SUM(f.monto) AS monto'),
        'a.resolucion_rectoral',
        'a.updated_at',
        'a.estado'
      )
      ->where('d.condicion', '=', 'Responsable')
      ->where('a.periodo', '=', $periodo)
      ->groupBy('a.id')
      ->get();

    return ['data' => $proyectos];
  }

  public function detalle($proyecto_id) {
    $detalle = DB::table('Proyecto AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->join('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->select(
        'a.titulo',
        'a.codigo_proyecto',
        'a.comentario',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.palabras_clave',
        'b.nombre AS linea',
        'c.linea AS ocde',
        'a.localizacion'
      )
      ->where('a.id', '=', $proyecto_id)
      ->get();

    return ['data' => $detalle];
  }

  public function miembros($proyecto_id) {
    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select(
        'b.nombre AS tipo_integrante',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, " ", c.nombres) AS nombre'),
        'c.tipo AS tipo_investigador'
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->orderBy('b.id')
      ->get();

    return ['data' => $miembros];
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


//  TODO - PROYECTOS HISTÓRICOS
class ProyectosController extends Controller {
  public function listado($periodo) {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.proyecto_id', '=', 'a.id')
      ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_presupuesto AS f', 'f.proyecto_id', '=', 'a.id')
      ->join('Usuario_investigador AS g', 'g.id', '=', 'd.investigador_id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'c.nombre AS linea',
        'a.titulo',
        DB::raw('CONCAT(g.apellido1, " " , g.apellido2, ", ", g.nombres) AS responsable'),
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
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProyectosGrupoController extends Controller {
  public function listarProyectosGrupo() {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.proyecto_id', '=', 'a.id')
      ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'c.nombre AS linea',
        'a.titulo',
        'a.investigador_id',
        'b.grupo_nombre',
        'e.nombre AS facultad'
      )
      ->where('d.condicion', '=', 'Responsable')
      ->groupBy('a.id')
      ->get();

    return ['data' => $proyectos];
  }
}

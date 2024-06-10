<?php

namespace App\Http\Controllers\Admin\Economia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionComprobantesController extends Controller {
  public function listadoProyectos() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $proyectos = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->leftJoin('Geco_documento AS c', 'a.id', '=', 'c.geco_proyecto_id')
      ->join('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'b.id')
      ->select([
        'a.id',
        'b.id AS proyecto_id',
        'b.codigo_proyecto',
        DB::raw("MAX(c.updated_at) AS fecha_actualizacion"),
        'res.responsable',
        'd.nombre AS facultad',
        'b.tipo_proyecto',
        'b.periodo',
        'a.estado',
      ])
      ->groupBy('a.id')
      ->get();

    return $proyectos;
  }
}

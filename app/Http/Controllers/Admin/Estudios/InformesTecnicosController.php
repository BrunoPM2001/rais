<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InformesTecnicosController extends Controller {

  public function proyectosListado($periodo) {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $deuda = DB::table('Proyecto_integrante_deuda AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->select(
        'b.proyecto_id',
        'a.estado',
        'a.categoria'
      )
      ->groupBy('b.proyecto_id');

    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Informe_tecnico AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($deuda, 'deu', 'deu.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'b.estado',
        'deu.estado AS deuda',
        'deu.categoria AS tipo_deuda',
        'res.responsable',
        'c.nombre AS facultad',
        'a.titulo',
      )
      ->where('a.periodo', '=', $periodo)
      ->where('a.estado', '>', 0)
      ->groupBy('a.id')
      ->get();

    return ['data' => $proyectos];
  }

  public function informes($proyecto_id) {
    $informes = DB::table('Informe_tecnico AS a')
      ->leftJoin('Informe_tipo AS b', 'b.id', '=', 'a.informe_tipo_id')
      ->select(
        'a.id',
        'b.informe',
        'a.estado',
        'a.fecha_envio',
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->get();

    return ['data' => $informes];
  }
}

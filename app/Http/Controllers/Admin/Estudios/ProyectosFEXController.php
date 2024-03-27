<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProyectosFEXController extends Controller {
  public function listado() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $moneda = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'moneda_tipo');

    $participacion_ummsm = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'participacion_ummsm');

    $fuente_financiadora = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'fuente_financiadora');

    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($moneda, 'moneda', 'moneda.proyecto_id', '=', 'a.id')
      ->leftJoinSub($participacion_ummsm, 'p_unmsm', 'p_unmsm.proyecto_id', '=', 'a.id')
      ->leftJoinSub($fuente_financiadora, 'fuente', 'fuente.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'res.responsable',
        'b.nombre AS facultad',
        'moneda.detalle AS moneda',
        'a.aporte_no_unmsm',
        'a.aporte_unmsm',
        'a.financiamiento_fuente_externa',
        'a.monto_asignado',
        'p_unmsm.detalle AS participacion_unmsm',
        'fuente.detalle AS fuente_fin',
        'a.periodo',
        DB::raw('DATE(a.created_at) AS registrado'),
        DB::raw('DATE(a.updated_at) AS actualizado'),
        'a.estado'
      )
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->groupBy('a.id')
      ->get();

    return ['data' => $proyectos];
  }
}

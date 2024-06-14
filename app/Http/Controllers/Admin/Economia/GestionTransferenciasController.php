<?php

namespace App\Http\Controllers\Admin\Economia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionTransferenciasController extends Controller {
  public function listadoProyectos() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $latestEntries = DB::table('Geco_operacion AS a')
      ->select([
        'a.geco_proyecto_id',
        DB::raw('MAX(a.created_at) AS max_created_at')
      ])
      ->groupBy('a.geco_proyecto_id');

    $proyectos = DB::table('Geco_operacion AS a')
      ->join('Geco_proyecto AS b', 'b.id', '=', 'a.geco_proyecto_id')
      ->join('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'c.id')
      ->joinSub($latestEntries, 'latest', function ($join) {
        $join->on('a.geco_proyecto_id', '=', 'latest.geco_proyecto_id')
          ->on('a.created_at', '=', 'latest.max_created_at');
      })
      ->select([
        'b.id',
        'c.codigo_proyecto',
        'latest.max_created_at AS fecha_ultima_solicitud',
        'res.responsable',
        'c.tipo_proyecto',
        'c.periodo',
        'a.estado',
      ])
      ->orderByDesc('latest.max_created_at')
      ->get();

    return $proyectos;
  }

  public function getSolicitudData(Request $request) {
    $solicitud = DB::table('Geco_operacion')
      ->select([
        'id',
        'justificacion',
        'estado',
        'updated_at'
      ])
      ->where('geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->orderByDesc('created_at')
      ->first();

    $presupuesto = DB::table('Geco_proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.codigo',
        'b.partida',
        'a.monto'
      ])
      ->where('a.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->get();

    $historial = DB::table('Geco_operacion')
      ->select([
        'id',
        'created_at',
        'observacion',
        'estado'
      ])
      ->where('geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->orderByDesc('created_at')
      ->get();

    return ['data' => $solicitud, 'presupuesto' => $presupuesto, 'historial' => $historial];
  }

  public function movimientosTransferencia(Request $request) {
    $movimientos = DB::table('Geco_operacion_movimiento AS a')
      ->join('Geco_proyecto_presupuesto AS b', 'b.id', '=', 'a.geco_proyecto_presupuesto_id')
      ->join('Partida AS c', 'c.id', '=', 'b.partida_id')
      ->select([
        'c.codigo',
        'c.partida',
        'a.operacion',
        'a.monto_original',
        'a.monto',
      ])
      ->where('geco_operacion_id', '=', $request->query('geco_operacion_id'))
      ->get();

    return $movimientos;
  }
}

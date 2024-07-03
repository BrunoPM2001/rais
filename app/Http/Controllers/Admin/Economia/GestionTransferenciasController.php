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
    $presupuesto = [];
    $detalle = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.tipo_proyecto',
        'b.titulo',
        'b.codigo_proyecto',
      ])
      ->where('a.id', '=', $request->query('geco_proyecto_id'))
      ->first();

    $solicitud = DB::table('Geco_operacion')
      ->select([
        'id',
        'justificacion',
        'estado',
        'created_at'
      ])
      ->where('geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->orderByDesc('created_at')
      ->first();

    //  En caso sea una operación para no evaluar se retorna el presupuesto actual
    if ($solicitud->estado != 3) {
      $presupuesto = DB::table('Geco_proyecto_presupuesto AS a')
        ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
        ->select([
          'b.tipo',
          'b.codigo',
          'b.partida',
          'a.monto'
        ])
        ->where('a.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
        ->get();
    } else {
      $presupuesto = DB::table('Geco_proyecto_presupuesto AS a')
        ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
        ->select([
          'b.tipo',
          'b.codigo',
          'b.partida',
          'a.monto',
          DB::raw('CASE 
        WHEN a.monto_temporal = 0 THEN ""
        WHEN a.monto_temporal < a.monto THEN "-"
        WHEN a.monto_temporal > a.monto THEN "+"
        ELSE ""
      END AS operacion'),
          DB::raw('CASE 
        WHEN a.monto_temporal = 0 THEN 0
        WHEN a.monto_temporal < a.monto THEN (a.monto - a.monto_temporal)
        WHEN a.monto_temporal > a.monto THEN (a.monto_temporal - a.monto)
        ELSE ""
      END AS transferencia'),
          'a.monto_temporal AS monto_nuevo',
          DB::raw('(a.monto_temporal) AS monto_nuevo'),
        ])
        ->where('a.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
        ->get();
    }

    $groupedData = $presupuesto->groupBy('tipo');

    $result = $groupedData->map(function ($items, $tipo) {
      return [
        'tipo' => $tipo,
        'children' => $items->map(function ($item) {
          // Eliminar la propiedad 'tipo' de cada item
          $itemArray = (array) $item;
          unset($itemArray['tipo']);
          return $itemArray;
        })->toArray()
      ];
    })->values()->toArray();

    $historial = DB::table('Geco_operacion')
      ->select([
        'id',
        'created_at',
        'observacion',
        'estado'
      ])
      ->where('geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->where('estado', '>', 0)
      ->orderByDesc('created_at')
      ->get();

    return ['proyecto' => $detalle, 'solicitud' => $solicitud, 'presupuesto' => $result, 'historial' => $historial];
  }

  public function movimientosTransferencia(Request $request) {
    $movimientos = DB::table('Geco_operacion_movimiento AS a')
      ->join('Geco_proyecto_presupuesto AS b', 'b.id', '=', 'a.geco_proyecto_presupuesto_id')
      ->join('Partida AS c', 'c.id', '=', 'b.partida_id')
      ->select([
        'c.tipo',
        'c.codigo',
        'c.partida',
        'a.monto_original',
        'a.operacion',
        'a.monto',
        DB::raw('CASE 
                    WHEN a.operacion = "+" THEN a.monto_original + a.monto 
                    ELSE a.monto_original - a.monto 
                 END AS monto_nuevo')
      ])
      ->where('geco_operacion_id', '=', $request->query('geco_operacion_id'))
      ->get();

    return $movimientos;
  }
}

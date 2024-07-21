<?php

namespace App\Http\Controllers\Admin\Economia;

use App\Http\Controllers\S3Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionComprobantesController extends S3Controller {
  public function listadoProyectos() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $pendientes = DB::table('Geco_documento AS a')
      ->select([
        'geco_proyecto_id',
        DB::raw('COUNT(*) AS cuenta')
      ])
      ->where('estado', '=', 4)
      ->groupBy('geco_proyecto_id');

    $revisados = DB::table('Geco_documento AS a')
      ->select([
        'geco_proyecto_id',
        DB::raw('COUNT(*) AS cuenta')
      ])
      ->where('estado', '!=', 4)
      ->groupBy('geco_proyecto_id');

    $total = DB::table('Geco_documento AS a')
      ->select([
        'geco_proyecto_id',
        DB::raw('COUNT(*) AS cuenta')
      ])
      ->groupBy('geco_proyecto_id');

    $proyectos = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'b.id')
      ->leftJoinSub($pendientes, 'd', 'd.geco_proyecto_id', '=', 'a.id')
      ->leftJoinSub($revisados, 'e', 'e.geco_proyecto_id', '=', 'a.id')
      ->leftJoinSub($total, 'f', 'f.geco_proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        'b.codigo_proyecto',
        'res.responsable',
        'c.nombre AS facultad',
        'b.tipo_proyecto',
        'b.periodo',
        'd.cuenta AS pendientes',
        'e.cuenta AS revisados',
        'f.cuenta AS total',
      ])
      ->groupBy('b.id')
      ->orderByDesc('d.cuenta')
      ->get();

    return $proyectos;
  }

  public function detalleProyecto(Request $request) {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable'),
        'b.email3',
        'b.telefono_movil'
      )
      ->where('condicion', '=', 'Responsable');

    $detalle = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'b.id')
      ->select([
        'b.tipo_proyecto',
        'b.titulo',
        'b.codigo_proyecto',
        'a.estado',
        'res.responsable',
        'res.email3',
        'res.telefono_movil',
      ])
      ->where('a.id', '=', $request->query('geco_id'))
      ->first();

    return $detalle;
  }

  public function listadoComprobantes(Request $request) {
    $comprobantes = DB::table('Geco_documento')
      ->select([
        'id',
        'tipo',
        'numero',
        'fecha',
        DB::raw("ROUND(total_declarado, 2) AS total_declarado"),
        'created_at',
        'updated_at',
        'estado',
        'razon_social',
        'ruc',
        'observacion'
      ])
      ->where('geco_proyecto_id', '=', $request->query('geco_id'))
      ->get()
      ->map(function ($item) {
        $item->total_declarado = (float) $item->total_declarado;
        return $item;
      });

    return $comprobantes;
  }

  public function listadoPartidasComprobante(Request $request) {

    $partidas = DB::table('Geco_documento_item AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.partida',
        'a.total'
      ])
      ->where('a.geco_documento_id', '=', $request->query('geco_documento_id'))
      ->where('b.tipo', '!=', 'Otros')
      ->get();

    $comprobante = DB::table('Geco_documento_file')
      ->select([
        'key'
      ])
      ->where('geco_documento_id', '=', $request->query('geco_documento_id'))
      ->first();

    if ($comprobante->key != null) {
      $comprobante->url = '/minio/geco-documento/' . $comprobante->key;
    }

    return ['partidas' => $partidas, 'comprobante' => $comprobante->url];
  }

  public function updateEstadoComprobante(Request $request) {
    if ($request->input('estado')["value"] == 3 && $request->input('observacion') == "") {
      return ['message' => 'warning', 'detail' => 'Necesita detallar la observación'];
    } else if ($request->input('estado')["value"] == 3 && $request->input('observacion') != "") {
      $count = DB::table('Geco_documento')
        ->where('id', '=', $request->input('geco_documento_id'))
        ->update([
          'estado' => $request->input('estado')["value"],
          'observacion' => $request->input('observacion')
        ]);

      if ($count > 0) {
        return ['message' => 'info', 'detail' => 'Estado del comprobante actualizado'];
      } else {
        return ['message' => 'warning', 'detail' => 'No se pudo actualizar la información'];
      }
    } else {
      $count = DB::table('Geco_documento')
        ->where('id', '=', $request->input('geco_documento_id'))
        ->update([
          'estado' => $request->input('estado')["value"]
        ]);

      if ($count > 0) {
        return ['message' => 'info', 'detail' => 'Estado del comprobante actualizado'];
      } else {
        return ['message' => 'warning', 'detail' => 'No se pudo actualizar la información'];
      }
    }
  }

  public function listadoPartidasProyecto(Request $request) {
    $partidas = DB::table('Geco_proyecto_presupuesto AS a')
      ->leftJoin('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.codigo',
        'b.tipo',
        'b.partida',
        'a.monto',
        'a.monto_rendido_enviado',
        DB::raw("LEAST(a.monto, a.monto_rendido) AS monto_rendido"),
        'a.monto_excedido'
      ])
      ->where('a.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->where('b.tipo', '!=', 'Otros')
      ->where(function ($query) {
        $query
          ->orWhere('a.partida_nueva', '!=', '1')
          ->orWhereNull('a.partida_nueva');
      })
      ->get();

    return $partidas;
  }
}

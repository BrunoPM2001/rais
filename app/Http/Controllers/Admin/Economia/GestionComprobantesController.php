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

    $latestEntries = DB::table('Geco_documento AS a')
      ->select([
        'a.geco_proyecto_id',
        DB::raw('MAX(a.created_at) AS max_created_at'),
        DB::raw("SUM(IF(a.estado = 4, 1, 0)) AS cuenta")
      ])
      ->groupBy('a.geco_proyecto_id');

    $proyectos = DB::table('Geco_documento AS a')
      ->join('Geco_proyecto AS b', 'b.id', '=', 'a.geco_proyecto_id')
      ->join('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'c.id')
      ->joinSub($latestEntries, 'latest', function ($join) {
        $join->on('a.geco_proyecto_id', '=', 'latest.geco_proyecto_id')
          ->on('a.created_at', '=', 'latest.max_created_at');
      })
      ->select([
        'b.id',
        'c.codigo_proyecto',
        'latest.max_created_at AS fecha_ultimo_comprobante',
        'res.responsable',
        'd.nombre AS facultad',
        'c.tipo_proyecto',
        'c.periodo',
        'a.estado',
        'latest.cuenta AS cuenta',
      ])
      ->groupBy('a.geco_proyecto_id')
      ->orderByDesc('latest.max_created_at')
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
      ->get();

    return $partidas;
  }

  public function updateEstadoComprobante(Request $request) {
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
      ->where(function ($query) {
        $query
          ->orWhere('a.partida_nueva', '!=', '1')
          ->orWhereNull('a.partida_nueva');
      })
      ->get();

    return $partidas;
  }
}

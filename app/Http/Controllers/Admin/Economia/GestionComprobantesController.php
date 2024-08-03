<?php

namespace App\Http\Controllers\Admin\Economia;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
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

      $proyecto = DB::table('Geco_documento')
        ->select([
          'geco_proyecto_id'
        ])
        ->where('id', '=', $request->input('geco_documento_id'))
        ->first();

      //  Cálculos previos - Eliminar cuando todo esté normal
      $this->limpiarMontos($proyecto->geco_proyecto_id);
      $this->calcularEnviado($proyecto->geco_proyecto_id);
      $this->calcularRendicion($proyecto->geco_proyecto_id);
      $this->calcularExceso($proyecto->geco_proyecto_id);

      if ($count > 0) {
        $this->agregarAudit($request);
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

      $proyecto = DB::table('Geco_documento')
        ->select([
          'geco_proyecto_id'
        ])
        ->where('id', '=', $request->input('geco_documento_id'))
        ->first();

      //  Cálculos previos - Eliminar cuando todo esté normal
      $this->limpiarMontos($proyecto->geco_proyecto_id);
      $this->calcularEnviado($proyecto->geco_proyecto_id);
      $this->calcularRendicion($proyecto->geco_proyecto_id);
      $this->calcularExceso($proyecto->geco_proyecto_id);

      if ($count > 0) {
        $this->agregarAudit($request);
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

  public function recalcularMontos(Request $request) {
    //  Cálculos previos - Eliminar cuando todo esté normal
    $this->limpiarMontos($request->query('geco_proyecto_id'));
    $this->calcularEnviado($request->query('geco_proyecto_id'));
    $this->calcularRendicion($request->query('geco_proyecto_id'));
    $this->calcularExceso($request->query('geco_proyecto_id'));

    return ['message' => 'success', 'detail' => 'Montos rendidos, excedidos y de envío actualizados'];
  }

  /**
   * Métodos para el cálculo de las operaciones al aprobar y enviar comprobantes
   * - limpiarMontos:
   * ELimina los valores de las columnas monto_rendido, monto_rendido_enviado y
   * monto_excedido, este método se debe ejecutar previo al resto para volver a
   * calcular todo de manera correcta en base a los comprobantes y presupuesto
   * actual
   * 
   * - calcularRendicion: 
   * Calcula el valor de la columna monto_rendido en base a las partidas de los
   * comprobantes aprobados de un proyecto (ESTADO = 1).
   * 
   * - calcularEnviado:
   * Calcula el valor de la columna monto_rendido_enviado en base a las partidas
   * de los comprobantes enviados por los docentes en un proyecto (ESTADO = 4).
   * 
   * - calcularExceso:
   * Calcula el valor de la columna monto_excedido en base a la siguiente operación
   * siempre y cuando su resultado sea > a 0.
   * (monto_rendido + monto_rendido_enviado) - monto 
   */

  public function limpiarMontos($geco_proyecto_id) {
    DB::table('Geco_proyecto_presupuesto')
      ->where('geco_proyecto_id', '=', $geco_proyecto_id)
      ->update([
        'monto_rendido_enviado' => 0,
        'monto_rendido' => 0,
        'monto_excedido' => 0,
      ]);

    return true;
  }

  public function calcularRendicion($geco_proyecto_id) {
    $comprobantes = DB::table('Geco_documento AS a')
      ->join('Geco_documento_item AS b', 'b.geco_documento_id', '=', 'a.id')
      ->select([
        DB::raw("SUM(b.total) AS monto_rendido"),
        'b.partida_id',
      ])
      ->where('geco_proyecto_id', '=', $geco_proyecto_id)
      ->where('estado', '=', 1)
      ->groupBy('b.partida_id')
      ->get();

    foreach ($comprobantes as $item) {
      DB::table('Geco_proyecto_presupuesto')
        ->where('geco_proyecto_id', '=', $geco_proyecto_id)
        ->where('partida_id', '=', $item->partida_id)
        ->update([
          'monto_rendido' => $item->monto_rendido
        ]);
    }
  }

  public function calcularEnviado($geco_proyecto_id) {
    $comprobantes = DB::table('Geco_documento AS a')
      ->join('Geco_documento_item AS b', 'b.geco_documento_id', '=', 'a.id')
      ->select([
        DB::raw("SUM(b.total) AS monto_rendido_enviado"),
        'b.partida_id',
      ])
      ->where('geco_proyecto_id', '=', $geco_proyecto_id)
      ->where('estado', '=', 4)
      ->groupBy('b.partida_id')
      ->get();

    foreach ($comprobantes as $item) {
      DB::table('Geco_proyecto_presupuesto')
        ->where('geco_proyecto_id', '=', $geco_proyecto_id)
        ->where('partida_id', '=', $item->partida_id)
        ->update([
          'monto_rendido_enviado' => $item->monto_rendido_enviado
        ]);
    }
  }

  public function calcularExceso($geco_proyecto_id) {
    $presupuesto = DB::table('Geco_proyecto_presupuesto')
      ->select([
        'id',
        'monto',
        'monto_rendido_enviado',
        'monto_rendido',
      ])
      ->where('geco_proyecto_id', '=', $geco_proyecto_id)
      ->get();

    foreach ($presupuesto as $item) {
      $exceso = 0;

      if (($item->monto_rendido_enviado + $item->monto_rendido) > $item->monto) {
        $exceso = ($item->monto_rendido_enviado + $item->monto_rendido) - $item->monto;
      }

      DB::table('Geco_proyecto_presupuesto')
        ->where('id', '=', $item->id)
        ->update([
          'monto_excedido' => $exceso
        ]);
    }
  }

  /**
   * AUDITORÍA:
   * Dentro de la columna audit (JSON almacenado como string) se guardarán
   * los cambios de estado por parte de los administradores.
   */

  public function agregarAudit(Request $request) {
    $documento = DB::table('Geco_documento')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->input('geco_documento_id'))
      ->first();

    $audit = json_decode($documento->audit ?? "[]");

    $audit[] = [
      'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
      'estado' => $request->input('estado')["value"],
      'nombre' => $request->attributes->get('token_decoded')->nombre . " " . $request->attributes->get('token_decoded')->apellidos
    ];

    $audit = json_encode($audit);

    DB::table('Geco_documento')
      ->where('id', '=', $request->input('geco_documento_id'))
      ->update([
        'audit' => $audit
      ]);
  }

  public function verAuditoria(Request $request) {
    $documento = DB::table('Geco_documento')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $audit = json_decode($documento->audit ?? "[]");

    return $audit;
  }
}

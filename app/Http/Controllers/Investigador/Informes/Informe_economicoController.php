<?php

namespace App\Http\Controllers\Investigador\Informes;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Informe_economicoController extends S3Controller {
  public function listadoProyectos(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Geco_proyecto AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante AS c', 'c.proyecto_id', '=', 'b.proyecto_id')
      ->join('Usuario_investigador AS d', 'd.id', '=', 'c.investigador_id')
      ->select(
        'b.id',
        'a.periodo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.titulo',
        DB::raw('CASE 
            WHEN b.estado = 1 THEN "Completado"
            WHEN b.estado = 0 THEN "Pendiente"
            ELSE "Desconocido"
        END AS estado')
      )
      ->where('c.condicion', '=', 'Responsable')
      ->where('d.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    return $proyectos;
  }

  public function detalles(Request $request) {
    $cuenta = DB::table('Geco_proyecto AS b')
      ->join('Proyecto_integrante AS c', 'c.proyecto_id', '=', 'b.proyecto_id')
      ->join('Usuario_investigador AS d', 'd.id', '=', 'c.investigador_id')
      ->where('b.id', '=', $request->query('id'))
      ->where('c.condicion', '=', 'Responsable')
      ->where('d.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    //  Validar que sea el responsable del proyecto seleccionado
    if ($cuenta == 0) {
      return response()->json(['error' => 'Unauthorized'], 401);
    } else {
      //  Datos generales
      $datos = DB::table('Geco_proyecto AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->select([
          'b.titulo',
          'b.codigo_proyecto',
          'b.tipo_proyecto'
        ])
        ->where('a.id', '=', $request->query('id'))
        ->first();

      //  Cifras
      $porcentaje = 0;
      $rendido = DB::table('Geco_proyecto_presupuesto AS a')
        ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
        ->select([
          DB::raw("SUM(a.monto) AS total"),
          DB::raw("SUM(a.monto_rendido) AS rendido")
        ])
        ->whereIn('b.tipo', ['Bienes', 'Servicios'])
        ->where('geco_proyecto_id', '=', $request->query('id'))
        ->first();

      if ($rendido->total <= $rendido->rendido) {
        $porcentaje = 100;
      } else {
        $porcentaje = round(($rendido->rendido / $rendido->total) * 100, 2);
      }

      $partidas = DB::table('Geco_proyecto_presupuesto AS a')
        ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
        ->where('a.geco_proyecto_id', '=', $request->query('id'))
        ->where('b.tipo', '!=', 'Otros')
        ->count();

      $comprobantes_aprobados = DB::table('Geco_documento')
        ->where('geco_proyecto_id', '=', $request->query('id'))
        ->where('estado', '=', 1)
        ->count();

      $transferencias_aprobadas = DB::table('Geco_operacion')
        ->where('geco_proyecto_id', '=', $request->query('id'))
        ->where('estado', '=', 1)
        ->count();

      //  Asignación económica
      $asignacion = DB::table('Geco_proyecto_presupuesto AS a')
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
        ->where('a.geco_proyecto_id', '=', $request->query('id'))
        ->where('b.tipo', '!=', 'Otros')
        ->where(function ($query) {
          $query
            ->orWhere('a.partida_nueva', '!=', '1')
            ->orWhereNull('a.partida_nueva');
        })
        ->get();

      //  Comprobantes
      $comprobantes = DB::table('Geco_documento AS a')
        ->join('Geco_proyecto AS b', 'b.id', '=', 'a.geco_proyecto_id')
        ->leftJoin('Geco_documento_file AS c', 'c.geco_documento_id', '=', 'a.id')
        ->select(
          'a.id',
          'a.tipo',
          'a.numero',
          'a.fecha',
          'a.total_declarado',
          DB::raw("CONCAT('/minio/geco-documento/', c.key) AS url"),
          DB::raw('CASE 
            WHEN a.estado = 1 THEN "Aprobado"
            WHEN a.estado = 2 THEN "Rechazado"
            WHEN a.estado = 3 THEN "Observado"
            WHEN a.estado = 4 THEN "Enviado"
            WHEN a.estado = 5 THEN "Anulado"
            ELSE "Desconocido"
          END AS estado'),
          'a.observacion'
        )
        ->where('b.id', '=', $request->query('id'))
        ->get();

      //  Transferencia o presupuesto
      $presupuesto = [];
      $transferenciaPendiente = DB::table('Geco_operacion')
        ->where('geco_proyecto_id', '=', $request->query('id'))
        ->whereIn('estado', [3, 4])
        ->count();

      if ($transferenciaPendiente == 0) {
        $presupuesto = DB::table('Geco_proyecto_presupuesto AS a')
          ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
          ->select([
            'b.tipo',
            'b.codigo',
            'b.partida',
            'a.monto',
          ])
          ->where('a.geco_proyecto_id', '=', $request->query('id'))
          ->get();
      } else {
        $operacion = DB::table('Geco_operacion')
          ->select([
            'id'
          ])
          ->where('geco_proyecto_id', '=', $request->query('id'))
          ->orderByDesc('created_at')
          ->first();

        $movimientos = DB::table('Geco_operacion_movimiento')
          ->select([
            'geco_proyecto_presupuesto_id',
            'operacion',
            'monto',
          ])
          ->where('geco_operacion_id', '=', $operacion->id)
          ->get();

        $presupuesto = DB::table('Geco_proyecto_presupuesto AS a')
          ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
          ->select([
            'a.id',
            'b.tipo',
            'b.codigo',
            'b.partida',
            'a.monto',
            DB::raw("0 AS monto_nuevo")
          ])
          ->where('a.geco_proyecto_id', '=', $request->query('id'))
          ->get()
          ->map(function ($item) use ($movimientos) {
            $monto_nuevo = $item->monto;
            foreach ($movimientos as $movimiento) {
              if ($movimiento->geco_proyecto_presupuesto_id == $item->id) {
                if ($movimiento->operacion == "+") {
                  $monto_nuevo = $monto_nuevo + $movimiento->monto;
                } else {
                  $monto_nuevo = $monto_nuevo - $movimiento->monto;
                }
              }
            }
            $item->monto_nuevo = $monto_nuevo;
            return $item;
          });
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

      //  Puede solicitar transferencias
      $habilitado = true;
      $count = DB::table('Geco_operacion')
        ->where('geco_proyecto_id', '=', $request->query('id'))
        ->where('estado', '=', 3)
        ->count();

      if ($count > 0) {
        $habilitado = false;
      }

      //  Historial de transferencias
      $historial = DB::table('Geco_operacion')
        ->select([
          'id',
          'created_at',
          'observacion',
          'estado'
        ])
        ->where('geco_proyecto_id', '=', $request->query('id'))
        ->where('estado', '>', 0)
        ->orderByDesc('created_at')
        ->get();

      return [
        'cifras' => [
          'rendido' => $porcentaje,
          'partidas' => $partidas,
          'comprobantes' => $comprobantes_aprobados,
          'transferencias' => $transferencias_aprobadas
        ],
        'datos' => $datos,
        'asignacion' => $asignacion,
        'comprobantes' => $comprobantes,
        'transferencias' => [
          'habilitado' => $habilitado,
          'solicitud' => $result,
          'historial' => $historial,
        ]
      ];
    }
  }

  public function listarPartidas(Request $request) {
    if (in_array($request->query('tipo'), ["RMOVILIDAD", "BVIAJE", "DJURADA"])) {
      $proyecto = DB::table('Geco_proyecto AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->select([
          'b.tipo_proyecto'
        ])
        ->where('a.id', '=', $request->query('geco_proyecto_id'))
        ->first();

      $partidas = DB::table('Geco_comprobante AS a')
        ->join('Geco_comprobante_partida AS b', 'a.id', '=', 'b.geco_comprobante_id')
        ->join('Partida_proyecto AS c', 'c.partida_id', '=', 'b.partida_id')
        ->join('Partida AS d', 'd.id', '=', 'c.partida_id')
        ->join('Geco_proyecto_presupuesto AS e', 'e.partida_id', '=', 'c.partida_id')
        ->select([
          'd.id AS value',
          DB::raw("CONCAT(d.codigo, ' - ', d.partida) AS label"),
          DB::raw("(e.monto - (IFNULL(e.monto_rendido_enviado, 0) + IFNULL(e.monto_rendido, 0))) AS max")
        ])
        ->where('a.codigo', '=', $request->query('tipo'))
        ->where('b.periodo', '=', 2018)
        ->where('c.postulacion', '=', 1)
        ->where('c.tipo_proyecto', '=', $proyecto->tipo_proyecto)
        ->where('e.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
        ->get();

      $integrantes = DB::table('Proyecto_integrante AS a')
        ->join('Geco_proyecto AS b', 'b.proyecto_id', '=', 'a.proyecto_id')
        ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
        ->select([
          'a.investigador_id AS value',
          DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS label")
        ])
        ->where('b.id', '=', $request->query('geco_proyecto_id'))
        ->get();

      return ['partidas' => $partidas, 'integrantes' => $integrantes];
    } else {
      $proyecto = DB::table('Geco_proyecto AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->select([
          'b.tipo_proyecto'
        ])
        ->where('a.id', '=', $request->query('geco_proyecto_id'))
        ->first();

      $partidas = DB::table('Geco_comprobante AS a')
        ->join('Geco_comprobante_partida AS b', 'a.id', '=', 'b.geco_comprobante_id')
        ->join('Partida_proyecto AS c', 'c.partida_id', '=', 'b.partida_id')
        ->join('Partida AS d', 'd.id', '=', 'c.partida_id')
        ->join('Geco_proyecto_presupuesto AS e', 'e.partida_id', '=', 'c.partida_id')
        ->select([
          'd.id AS value',
          DB::raw("CONCAT(d.codigo, ' - ', d.partida) AS label"),
          DB::raw("(e.monto - (IFNULL(e.monto_rendido_enviado, 0) + IFNULL(e.monto_rendido, 0))) AS max")
        ])
        ->where('a.codigo', '=', $request->query('tipo'))
        ->where('b.periodo', '=', 2018)
        ->where('c.postulacion', '=', 1)
        ->where('c.tipo_proyecto', '=', $proyecto->tipo_proyecto)
        ->where('e.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
        ->get();

      return $partidas;
    }
  }

  public function dataComprobante(Request $request) {
    $documento = [];
    $integrantes = [];
    switch ($request->query('tipo')) {
      case "BOLETA":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'razon_social',
          ])
          ->where('id', '=', $request->query('id'))
          ->first();
        break;
      case "FACTURA":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'retencion',
            'razon_social'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();

        $documento->retencion = [
          'value' => $documento->retencion,
          'label' => $documento->retencion == 0 ? 'No afecta' : 'Retención'
        ];
        break;
      case "RMOVILIDAD":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'investigador_id',
            'fecha',
          ])
          ->where('id', '=', $request->query('id'))
          ->first();

        $integrantes = DB::table('Proyecto_integrante AS a')
          ->join('Geco_proyecto AS b', 'b.proyecto_id', '=', 'a.proyecto_id')
          ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
          ->select([
            'a.investigador_id AS value',
            DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS label")
          ])
          ->where('b.id', '=', $request->query('geco_proyecto_id'))
          ->get();
        break;
      case "RHONORARIOS":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'retencion',
            'razon_social'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();

        $documento->retencion = [
          'value' => $documento->retencion,
          'label' => $documento->retencion == 0 ? 'No afecta' : 'Retención'
        ];
        break;
      case "BVIAJE":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'investigador_id',
            'razon_social'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();

        $integrantes = DB::table('Proyecto_integrante AS a')
          ->join('Geco_proyecto AS b', 'b.proyecto_id', '=', 'a.proyecto_id')
          ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
          ->select([
            'a.investigador_id AS value',
            DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS label")
          ])
          ->where('b.id', '=', $request->query('geco_proyecto_id'))
          ->get();

        break;
      case "LCOMPRA":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'numero_doc',
            'fecha',
            'prestador'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();
        break;
      case "OTROS":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'descripcion_compra',
            'monto_exterior',
            'pais_emisor',
            'tipo_moneda',
            'fecha',
          ])
          ->where('id', '=', $request->query('id'))
          ->first();
        break;
      case "RINGRESO":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'razon_social',
            'fecha_presentacion_solicitud_compra'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();
        break;
      case "TICKET":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'razon_social'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();
        break;
      case "RBANCO":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'numero',
            'ruc',
            'fecha',
            'razon_social',
            'concepto'
          ])
          ->where('id', '=', $request->query('id'))
          ->first();
        break;
      case "DJURADA":
        $documento = DB::table('Geco_documento')
          ->select([
            'tipo',
            'viatico',
            'ciudad_origen',
            'fecha',
            'investigador_id',
          ])
          ->where('id', '=', $request->query('id'))
          ->first();

        $integrantes = DB::table('Proyecto_integrante AS a')
          ->join('Geco_proyecto AS b', 'b.proyecto_id', '=', 'a.proyecto_id')
          ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
          ->select([
            'a.investigador_id AS value',
            DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS label")
          ])
          ->where('b.id', '=', $request->query('geco_proyecto_id'))
          ->get();

        break;
    }

    $partidas = DB::table('Geco_documento_item AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->join('Geco_proyecto_presupuesto AS c', 'c.partida_id', '=', 'b.id')
      ->select([
        'b.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
        'a.total',
        DB::raw("(c.monto - (IFNULL(c.monto_rendido_enviado, 0) + IFNULL(c.monto_rendido, 0)) + a.total) AS max")
      ])
      ->where('a.geco_documento_id', '=', $request->input('id'))
      ->where('c.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->get()
      ->map(function ($item) {
        return [
          'partida' => [
            'value' => $item->value,
            'label' => $item->label,
            'max' => $item->max
          ],
          'monto' => $item->total
        ];
      });;

    $listaPartidas = $this->listarPartidas($request);

    return ['documento' => $documento, 'partidas' => $partidas, 'lista' => $listaPartidas, 'integrantes' => $integrantes];
  }

  public function subirComprobante(Request $request) {
    $date = Carbon::now();
    if ($request->hasFile('file')) {
      if ($request->input('geco_documento_id') == "") {
        //  Crear documento
        $id = DB::table('Geco_documento')
          ->insertGetId([
            'geco_proyecto_id' => $request->input('geco_proyecto_id'),
            'investigador_id' => $request->input('investigador_id'),
            'tipo' => $request->input('tipo'),
            'numero' => $request->input('numero'),
            'numero_doc' => $request->input('numero_doc'),
            'prestador' => $request->input('prestador'),
            'descripcion_compra' => $request->input('descripcion_compra'),
            'monto_exterior' => $request->input('monto_exterior'),
            'pais_emisor' => $request->input('pais_emisor'),
            'tipo_moneda' => $request->input('tipo_moneda'),
            'ruc' => $request->input('ruc'),
            'concepto' => $request->input('concepto'),
            'fecha' => $request->input('fecha'),
            'fecha_presentacion_solicitud_compra' => $request->input('fecha_presentacion_solicitud_compra'),
            'retencion' => $request->input('retencion') == "" ? null : $request->input('retencion'),
            'razon_social' => $request->input('razon_social'),
            'estado' => 4,
            'created_at' => $date,
            'updated_at' => $date,
            'observacion' => "[]"
          ]);

        //  Crear partidas asignadas al documento y actualizar presupuesto
        $this->actualizarPartidas($id, $request->input('partidas'));
        $this->calcularNuevoPresupuesto($request->input('geco_proyecto_id'));

        //  Guardar comprobante
        $name = $id . "/comprobante-" . $date->format('Ymd-His') . "." . $request->file('file')->getClientOriginalExtension();
        $this->uploadFile($request->file('file'), "geco-documento", $name);

        DB::table('Geco_documento_file')
          ->updateOrInsert([
            'geco_documento_id' => $id
          ], [
            'key' => $name,
            'created_at' => $date,
            'updated_at' => $date
          ]);

        return ['message' => 'success', 'detail' => 'Comprobante cargado exitosamente'];
      } else {
        //  Actualizar documento
        DB::table('Geco_documento')
          ->where('id', '=', $request->input('geco_documento_id'))
          ->update([
            'numero' => $request->input('numero'),
            'numero_doc' => $request->input('numero_doc'),
            'prestador' => $request->input('prestador'),
            'investigador_id' => $request->input('investigador_id'),
            'ruc' => $request->input('ruc'),
            'concepto' => $request->input('concepto'),
            'fecha' => $request->input('fecha'),
            'fecha_presentacion_solicitud_compra' => $request->input('fecha_presentacion_solicitud_compra'),
            'retencion' => $request->input('retencion') == "" ? null : $request->input('retencion'),
            'razon_social' => $request->input('razon_social'),
            'estado' => 4,
            'updated_at' => $date,
          ]);

        //  Actualizar partidas y presupuesto
        $this->actualizarPartidas($request->input('geco_documento_id'), $request->input('partidas'));
        $this->calcularNuevoPresupuesto($request->input('geco_proyecto_id'));

        //  Guardar comprobante
        $name = $request->input('geco_documento_id') . "/comprobante-" . $date->format('Ymd-His') . "." . $request->file('file')->getClientOriginalExtension();;
        $this->uploadFile($request->file('file'), "geco-documento", $name);

        DB::table('Geco_documento_file')
          ->where('geco_documento_id', '=', $request->input('geco_documento_id'))
          ->update([
            'key' => $name,
            'updated_at' => $date
          ]);

        return ['message' => 'success', 'detail' => 'Comprobante actualizado exitosamente'];
      }
    } else {
      if ($request->input('geco_documento_id') != "") {
        //  Actualizar documento
        $id = DB::table('Geco_documento')
          ->where('id', '=', $request->input('geco_documento_id'))
          ->update([
            'numero' => $request->input('numero'),
            'numero_doc' => $request->input('numero_doc'),
            'prestador' => $request->input('prestador'),
            'investigador_id' => $request->input('investigador_id'),
            'ruc' => $request->input('ruc'),
            'concepto' => $request->input('concepto'),
            'fecha' => $request->input('fecha'),
            'fecha_presentacion_solicitud_compra' => $request->input('fecha_presentacion_solicitud_compra'),
            'retencion' => $request->input('retencion') == "" ? null : $request->input('retencion'),
            'razon_social' => $request->input('razon_social'),
            'estado' => 4,
            'updated_at' => $date,
          ]);

        //  Actualizar partidas y presupuesto
        $this->actualizarPartidas($request->input('geco_documento_id'), $request->input('partidas'));
        $this->calcularNuevoPresupuesto($request->input('geco_proyecto_id'));

        return ['message' => 'success', 'detail' => 'Comprobante actualizado exitosamente'];
      } else {
        return [
          'message' => 'error',
          'detail' => 'Error cargando comprobante'
        ];
      }
    }
  }

  public function anularComprobante(Request $request) {
    DB::table('Geco_documento')
      ->where('id', '=', $request->input('geco_documento_id'))
      ->update([
        'estado' => 5,
        'updated_at' => Carbon::now(),
      ]);

    //  Actualizar presupuesto
    $this->calcularNuevoPresupuesto($request->input('geco_proyecto_id'));

    return ['message' => 'info', 'detail' => 'Comprobante anulado'];
  }

  public function actualizarPartidas($id, $partidas) {
    $monto = 0;
    $date = Carbon::now();
    $partidas = json_decode($partidas);

    DB::table('Geco_documento_item')
      ->where('geco_documento_id', '=', $id)
      ->delete();

    foreach ($partidas as $partida) {
      $monto += $partida->monto;
      DB::table('Geco_documento_item')
        ->insert([
          'geco_documento_id' => $id,
          'partida_id' => $partida->partida->value,
          'sub_total' => $partida->monto,
          'total' => $partida->monto,
          'created_at' => $date,
          'updated_at' => $date,
        ]);
    }

    //  Actualizar el monto del documento
    DB::table('Geco_documento')
      ->where('id', '=', $id)
      ->update([
        'total_sin_igv' => $monto,
        'total_declarado' => $monto,
      ]);
  }

  //  Recalcular rendición y envíos
  public function calcularNuevoPresupuesto($id) {
    //  Poner todo en 0
    DB::table('Geco_proyecto_presupuesto')
      ->where('geco_proyecto_id', '=', $id)
      ->update([
        'monto_rendido_enviado' => 0,
        'monto_excedido' => 0,
        'monto_rendido' => 0
      ]);

    //  Comprobantes aprobados
    $documentos = DB::table('Geco_documento AS a')
      ->join('Geco_documento_item AS b', 'b.geco_documento_id', '=', 'a.id')
      ->join('Geco_proyecto_presupuesto AS c', 'c.partida_id', '=', 'b.partida_id')
      ->select([
        'b.partida_id',
        'c.monto AS maximo',
        DB::raw("SUM(b.total) AS total"),
      ])
      ->where('a.geco_proyecto_id', '=', $id)
      ->where('c.geco_proyecto_id', '=', $id)
      ->where('a.estado', '=', 1)
      ->groupBy('b.partida_id')
      ->get();

    foreach ($documentos as $documento) {
      $max = floatval($documento->maximo);
      $total = floatval($documento->total);
      DB::table('Geco_proyecto_presupuesto')
        ->where('geco_proyecto_id', '=', $id)
        ->where('partida_id', '=', $documento->partida_id)
        ->update([
          'monto_rendido' => min($max, $total),
          'monto_excedido' => $max < $total ? $total - $max : 0
        ]);
    }

    //  Comprobantes enviados y observados
    $documentos = DB::table('Geco_documento AS a')
      ->join('Geco_documento_item AS b', 'b.geco_documento_id', '=', 'a.id')
      ->join('Geco_proyecto_presupuesto AS c', 'c.partida_id', '=', 'b.partida_id')
      ->select([
        'b.partida_id',
        DB::raw("(c.monto - c.monto_rendido) AS maximo"),
        DB::raw("SUM(b.total) AS total"),
      ])
      ->where('a.geco_proyecto_id', '=', $id)
      ->where('c.geco_proyecto_id', '=', $id)
      ->whereIn('a.estado', [3, 4])
      ->groupBy('b.partida_id')
      ->get();

    foreach ($documentos as $documento) {
      $max = floatval($documento->maximo);
      $total = floatval($documento->total);
      DB::table('Geco_proyecto_presupuesto')
        ->where('geco_proyecto_id', '=', $id)
        ->where('partida_id', '=', $documento->partida_id)
        ->update([
          'monto_rendido_enviado' => $total,
          'monto_excedido' => $max < $total ? DB::raw('monto_excedido + ' . ($total - $max)) : DB::raw('monto_excedido')
        ]);
    }
  }

  //  Transferencias
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

  public function partidasTransferencias(Request $request) {
    $partidasA = DB::table('Geco_proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.id AS value',
        'b.tipo',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
        DB::raw("(a.monto - COALESCE(a.monto_rendido, 0) - COALESCE(a.monto_rendido_enviado, 0)) AS max"),
        'a.monto',
        'monto_temporal',
        DB::raw("COALESCE(a.monto_rendido_enviado, 0) AS monto_rendido_enviado"),
        DB::raw("COALESCE(a.monto_rendido, 0) AS monto_rendido"),
      ])
      ->where('a.geco_proyecto_id', '=', $request->query('geco_proyecto_id'))
      ->get();

    $partidasB = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->join('Partida_proyecto AS c', 'c.tipo_proyecto', '=', 'b.tipo_proyecto')
      ->join('Partida AS d', 'd.id', '=', 'c.partida_id')
      ->select([
        'd.id AS value',
        'd.tipo',
        DB::raw("CONCAT(d.codigo, ' - ', d.partida) AS label")
      ])
      ->where('a.id', '=', $request->query('geco_proyecto_id'))
      ->where('c.postulacion', '=', 1)
      ->get();

    return ['partidasA' => $partidasA, 'partidasB' => $partidasB];
  }

  public function addTransferenciaTemporal(Request $request) {
    $temporal = DB::table('Geco_operacion')
      ->select(['id'])
      ->where('geco_proyecto_id', '=', $request->input('geco_proyecto_id'))
      ->where('estado', '=', 4)
      ->first();

    $idTemporal = 0;
    if ($temporal == null) {
      $id = DB::table('Geco_operacion')
        ->insertGetId([
          'geco_proyecto_id' => $request->input('geco_proyecto_id'),
          'estado' => 4,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
      $idTemporal = $id;
    } else {
      $idTemporal = $temporal->id;
    }

    //  Suma
    $idSuma = 0;
    $partidaSuma = DB::table('Geco_proyecto_presupuesto')
      ->select([
        'id',
        'monto_temporal',
        'monto'
      ])
      ->where('partida_id', '=', $request->input('partidaB'))
      ->where('geco_proyecto_id', '=', $request->input('geco_proyecto_id'))
      ->first();

    if ($partidaSuma == null) {
      $idSuma = DB::table('Geco_proyecto_presupuesto')
        ->insertGetId([
          'geco_proyecto_id' => $request->input('geco_proyecto_id'),
          'partida_id' => $request->input('partidaB'),
          'partida_nueva' => 1,
          'monto_temporal' => $request->input('monto'),
          'estado' => 50,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    } else {
      DB::table('Geco_proyecto_presupuesto')
        ->where('id', '=', $partidaSuma->id)
        ->update([
          'monto_temporal' =>  $partidaSuma->monto_temporal == 0 ? DB::raw('monto + ' . $request->input('monto')) : DB::raw('monto_temporal + ' . $request->input('monto')),
          'updated_at' => Carbon::now()
        ]);
      $idSuma = $partidaSuma->id;
    }

    DB::table('Geco_operacion_movimiento')
      ->insert([
        'geco_operacion_id' => $idTemporal,
        'geco_proyecto_presupuesto_id' => $idSuma,
        'operacion' => '+',
        'monto' => $request->input('monto'),
        'monto_original' => $partidaSuma == null ? 0 : ($partidaSuma->monto_temporal == 0 ? $partidaSuma->monto : $partidaSuma->monto_temporal),
        'estado' => 40,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    //  Resta
    $partidaResta = DB::table('Geco_proyecto_presupuesto')
      ->select([
        'id',
        'monto_temporal',
        'monto'
      ])
      ->where('partida_id', '=', $request->input('partidaA'))
      ->where('geco_proyecto_id', '=', $request->input('geco_proyecto_id'))
      ->first();

    DB::table('Geco_proyecto_presupuesto')
      ->where('partida_id', '=', $request->input('partidaA'))
      ->where('geco_proyecto_id', '=', $request->input('geco_proyecto_id'))
      ->update([
        'monto_temporal' =>  $partidaResta->monto_temporal == 0 ? DB::raw('monto - ' . $request->input('monto')) : DB::raw('monto_temporal - ' . $request->input('monto')),
        'updated_at' => Carbon::now()
      ]);

    DB::table('Geco_operacion_movimiento')
      ->insert([
        'geco_operacion_id' => $idTemporal,
        'geco_proyecto_presupuesto_id' => $partidaResta->id,
        'operacion' => '-',
        'monto' => $request->input('monto'),
        'monto_original' => $partidaResta == null ? 0 : ($partidaResta->monto_temporal == 0 ? $partidaResta->monto : $partidaResta->monto_temporal),
        'estado' => 40,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Movimiento de transferencia agregado'];
  }

  public function solicitarTransferencia(Request $request) {
    DB::table('Geco_operacion')
      ->where('geco_proyecto_id', '=', $request->input('geco_proyecto_id'))
      ->where('estado', '=', 4)
      ->update([
        'estado' => 3,
        'justificacion' => $request->input('justificacion'),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'Transferencia solicitada'];
  }
}

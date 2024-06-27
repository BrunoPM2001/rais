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
        ->join('Geco_documento_file AS c', 'c.geco_documento_id', '=', 'a.id')
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
          END AS estado')
        )
        ->where('b.id', '=', $request->query('id'))
        ->get();

      return [
        'cifras' => [
          'partidas' => $partidas,
          'comprobantes' => $comprobantes_aprobados,
          'transferencias' => $transferencias_aprobadas
        ],
        'datos' => $datos,
        'asignacion' => $asignacion,
        'comprobantes' => $comprobantes,
      ];
    }
  }

  public function listarPartidas(Request $request) {
    $proyecto = DB::table('Geco_proyecto AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.tipo_proyecto'
      ])
      ->where('a.id', '=', $request->query('geco_proyecto_id'))
      ->first();

    $partidas = DB::table('Partida_proyecto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
      ])
      ->where('a.tipo_proyecto', '=', $proyecto->tipo_proyecto)
      ->where('a.postulacion', '=', 1)
      ->get();

    return $partidas;
  }

  public function dataComprobante(Request $request) {
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

    $partidas = DB::table('Geco_documento_item AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
        'total'
      ])
      ->where('geco_documento_id', '=', $request->input('id'))
      ->get()
      ->map(function ($item) {
        return [
          'partida' => [
            'value' => $item->value,
            'label' => $item->label
          ],
          'monto' => $item->total
        ];
      });;

    $listaPartidas = $this->listarPartidas($request);

    return ['documento' => $documento, 'partidas' => $partidas, 'lista' => $listaPartidas];
  }

  public function subirComprobante(Request $request) {
    $date = Carbon::now();
    if ($request->hasFile('file')) {
      if ($request->input('geco_documento_id') == "") {
        //  Crear documento
        $id = DB::table('Geco_documento')
          ->insertGetId([
            'geco_proyecto_id' => $request->input('geco_proyecto_id'),
            'tipo' => 'BOLETA',
            'numero' => $request->input('numero'),
            'ruc' => $request->input('ruc'),
            'fecha' => $request->input('fecha'),
            'retencion' => $request->input('retencion') == "" ? null : $request->input('retencion'),
            'razon_social' => $request->input('razon_social'),
            'estado' => 4,
            'created_at' => $date,
            'updated_at' => $date,
            'observacion' => "[]"
          ]);

        $partidas = json_decode($request->input('partidas'));

        //  Crear partidas asignadas al documento
        foreach ($partidas as $partida) {
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

        //  Guardar comprobante
        $name = $id . "/comprobante-" . $date->format('Ymd-His') . "." . $request->file('file')->getClientOriginalExtension();;
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
            'ruc' => $request->input('ruc'),
            'fecha' => $request->input('fecha'),
            'retencion' => $request->input('retencion') == "" ? null : $request->input('retencion'),
            'razon_social' => $request->input('razon_social'),
            'estado' => 4,
            'updated_at' => $date,
          ]);

        //  Actualizar partidas
        $partidas = json_decode($request->input('partidas'));

        DB::table('Geco_documento_item')
          ->where('geco_documento_id', '=', $request->input('geco_documento_id'))
          ->delete();

        foreach ($partidas as $partida) {
          DB::table('Geco_documento_item')
            ->insert([
              'geco_documento_id' => $request->input('geco_documento_id'),
              'partida_id' => $partida->partida->value,
              'sub_total' => $partida->monto,
              'total' => $partida->monto,
              'created_at' => $date,
              'updated_at' => $date,
            ]);
        }

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
            'ruc' => $request->input('ruc'),
            'fecha' => $request->input('fecha'),
            'retencion' => $request->input('retencion') == "" ? null : $request->input('retencion'),
            'razon_social' => $request->input('razon_social'),
            'estado' => 4,
            'updated_at' => $date,
          ]);

        //  Actualizar partidas
        $partidas = json_decode($request->input('partidas'));

        DB::table('Geco_documento_item')
          ->where('geco_documento_id', '=', $request->input('geco_documento_id'))
          ->delete();

        foreach ($partidas as $partida) {
          DB::table('Geco_documento_item')
            ->insert([
              'geco_documento_id' => $request->input('geco_documento_id'),
              'partida_id' => $partida->partida->value,
              'sub_total' => $partida->monto,
              'total' => $partida->monto,
              'created_at' => $date,
              'updated_at' => $date,
            ]);
        }

        return ['message' => 'success', 'detail' => 'Comprobante actualizado exitosamente'];
      } else {
        return [
          'message' => 'error',
          'detail' => 'Error cargando comprobante'
        ];
      }
    }
  }
}

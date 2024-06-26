<?php

namespace App\Http\Controllers\Investigador\Informes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Informe_economicoController extends Controller {
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
}

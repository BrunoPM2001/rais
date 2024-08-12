<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectosUtilController extends Controller {
  public function reportePresupuesto(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('condicion', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'b.investigador_id', '=', 'c.id')
      ->leftJoin('Facultad AS d', 'a.facultad_id', '=', 'd.id')
      ->select([
        'a.fecha_inscripcion',
        'a.periodo',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("COALESCE(d.nombre, 'No figura') AS facultad"),
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS responsable"),
        'c.email3',
        'c.telefono_movil',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $presupuesto = DB::table('Geco_proyecto AS a')
      ->join('Geco_proyecto_presupuesto AS b', 'b.geco_proyecto_id', '=', 'a.id')
      ->join('Partida AS c', 'c.id', '=', 'b.partida_id')
      ->leftJoin('Proyecto_presupuesto AS d', function (JoinClause $join) use ($request) {
        $join->on('d.partida_id', '=', 'b.partida_id')
          ->where('d.proyecto_id', '=', $request->query('id'));
      })
      ->select([
        'b.id',
        'c.tipo',
        'c.partida',
        'd.monto AS monto_original',
        'b.monto AS monto_modificado',
        DB::raw("(b.monto_rendido - b.monto_excedido) AS monto_rendido"),
        DB::raw("(b.monto - b.monto_rendido + b.monto_excedido) AS saldo_rendicion"),
        'b.monto_excedido'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->where('c.tipo', '!=', 'Otros')
      ->orderBy('c.tipo')
      ->get()
      ->groupBy('tipo');

    $pdf = Pdf::loadView('investigador.actividades.presupuesto', ['proyecto' => $proyecto, 'presupuesto' => $presupuesto]);
    return $pdf->stream();

    // return ['proyecto' => $proyecto, 'presupuesto' => $presupuesto];
  }
}

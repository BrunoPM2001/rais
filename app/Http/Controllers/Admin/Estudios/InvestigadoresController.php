<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InvestigadoresController extends Controller {
  public function listado() {

    $puntajeT = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select(
        'a.investigador_id',
        DB::raw('SUM(a.puntaje) AS puntaje')
      )
      ->groupBy('a.investigador_id');

    $investigadores = DB::table('Usuario_investigador AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($puntajeT, 'puntaje', 'puntaje.investigador_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.rrhh_status',
        'puntaje.puntaje',
        'a.tipo',
        'b.nombre AS facultad',
        'a.codigo',
        'a.codigo_orcid',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.fecha_nac',
        'a.doc_tipo',
        'a.doc_numero',
        'a.telefono_movil'
      )
      ->get();

    return ['data' => $investigadores];
  }
}

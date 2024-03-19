<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GruposController extends Controller {

  public function listadoGrupos() {
    $grupos = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.coorddatos')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        'a.grupo_categoria',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, ", ", c.nombres) AS coordinador'),
        DB::raw('COUNT(b.id) AS cantidad_integrantes'),
        'd.nombre AS facultad',
        'a.resolucion_rectoral',
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.tipo', '=', 'grupo')
      ->groupBy('a.id')
      ->get();

    return ['data' => $grupos];
  }

  public function listadoSolicitudes() {
    //  Subquery para obtener el coordinador de cada grupo
    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    //  Query base
    $solicitudes = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        DB::raw('COUNT(b.id) AS cantidad_integrantes'),
        'a.facultad_id',
        'a.estado',
        'a.created_at',
        'a.updated_at',
        'coordinador.nombre'
      )
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->where('a.tipo', '=', 'solicitud')
      ->havingBetween('a.estado', [0, 6])
      ->groupBy('a.id')
      ->get();


    return ['data' => $solicitudes];
  }
}

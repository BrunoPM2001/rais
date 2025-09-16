<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Usuario_investigadorController extends Controller {
  public function getAll() {
    $usuarios = DB::table('Usuario AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.tabla_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select(
        'a.id',
        'c.nombre AS facultad',
        'b.codigo',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.sexo',
        'a.email',
        'b.doc_numero',
        'a.estado'
      )
      ->where('tabla', '=', 'Usuario_investigador')
      ->get();

    return $usuarios;
  }

  public function getOne($id) {
    $usuario = DB::table('Usuario')
      ->select(
        'id',
        'email',
        'estado'
      )
      ->where('id', '=', $id)
      ->first();

    return $usuario;
  }

  public function searchInvestigadorBy(Request $request) {
    $investigadores = DB::table('Usuario_investigador AS a')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo), ' | ', a.doc_numero, ' | ', a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS value"),
        'a.id AS investigador_id',
        'a.codigo',
        'a.doc_numero',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.id')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchConstanciaBy(Request $request) {
    $docente = DB::table('Usuario_investigador AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.investigador_id', '=', 'a.id')
      ->select(
        DB::raw("
                  CONCAT(
                      TRIM(a.codigo), ' | ', 
                      a.doc_numero, ' | ', 
                      a.apellido1, ' ', a.apellido2, ', ', a.nombres, ' | ', 
                      COALESCE(a.tipo, CONCAT(a.tipo_investigador, ' - ', a.tipo_investigador_estado))
                  ) AS value
              "),
        'a.id AS investigador_id',
        'a.id',
        'a.codigo',
        'a.doc_numero',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.tipo',
        'a.tipo_investigador',
        'a.tipo_investigador_estado',
        DB::raw("COUNT(b.id) AS publicaciones")
      )
      ->whereRaw('LOWER(a.tipo_investigador) LIKE ?', ['docente%'])
      ->orWhereRaw('LOWER(a.tipo) LIKE ?', ['docente%'])
      ->orWhereRaw('LOWER(a.tipo_investigador) LIKE ?', ['estudiante%'])
      ->orWhereRaw('LOWER(a.tipo) LIKE ?', ['estudiante%'])
      ->orWhereRaw('LOWER(a.tipo_investigador) LIKE ?', ['externo%'])
      ->orWhereRaw('LOWER(a.tipo) LIKE ?', ['externo%'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.id')
      ->limit(10)
      ->get();

    return $docente;
  }
}

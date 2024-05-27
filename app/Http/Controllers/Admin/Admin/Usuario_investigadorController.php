<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Svg\Tag\Rect;

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

    return ['data' => $usuarios];
  }

  public function getOne($id) {
    $usuario = DB::table('Usuario')
      ->select(
        'id',
        'email',
        'estado'
      )
      ->where('id', '=', $id)
      ->get();

    return $usuario[0];
  }

  public function searchInvestigadorBy(Request $request) {
    $investigadores = DB::table('Usuario_investigador AS a')
      ->select(
        DB::raw("CONCAT(TRIM(codigo), ' | ', doc_numero, ' | ', apellido1, ' ', apellido2, ', ', nombres) AS value"),
        'id AS investigador_id',
        'codigo',
        'doc_numero',
        'apellido1',
        'apellido2',
        'nombres'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }
}

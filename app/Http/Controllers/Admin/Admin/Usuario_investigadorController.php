<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
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

  public function searchInvestigadorBy($input) {
    $investigadores = DB::table('Usuario_investigador AS a')
      ->select(
        'id',
        'codigo',
        'doc_numero',
        'apellido1',
        'apellido2',
        'nombres'
      )
      ->where('codigo', 'LIKE', '%' . $input . '%')
      ->orWhere('doc_numero', 'LIKE', '%' . $input . '%')
      ->orWhere('apellido1', 'LIKE', '%' . $input . '%')
      ->orWhere('apellido2', 'LIKE', '%' . $input . '%')
      ->orWhere('nombres', 'LIKE', '%' . $input . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }
}

<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionEvaluadoresController extends Controller {
  public function listado() {
    $usuarios = DB::table('Usuario AS a')
      ->join('Usuario_evaluador AS b', 'b.id', '=', 'a.tabla_id')
      ->select([
        'b.id',
        'b.apellidos',
        'b.nombres',
        'b.institucion',
        'b.tipo',
        'b.cargo',
        'a.username'
      ])
      ->where('a.tabla', '=', 'Usuario_evaluador')
      ->get();

    return $usuarios;
  }

  public function searchInvestigador(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(codigo, ' | ', doc_numero, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        DB::raw("CONCAT(apellido1, ' ', apellido2) AS apellidos"),
        'nombres',
        DB::raw("'UNMSM' AS institucion"),
      )
      ->where('tipo', 'LIKE', 'DOCENTE%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }
}

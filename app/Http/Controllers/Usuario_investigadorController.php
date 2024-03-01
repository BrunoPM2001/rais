<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
      ->get();

    return $investigadores;
  }

  public function getConstanciaGrupoInvestigacion($investigador_id) {
    $grupo = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
      ->join('Grupo AS c', 'c.id', '=', 'b.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        DB::raw('CONCAT(a.apellido1, " ", a.apellido2, " ", a.nombres) AS nombre'),
        'd.nombre AS facultad',
        'b.cargo',
        'b.condicion',
        'c.grupo_nombre_corto',
        'c.grupo_nombre',
        'c.resolucion_rectoral',
        'c.resolucion_creacion_fecha'
      )
      ->where('a.id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->get()
      ->toArray();

    $pdf = Pdf::loadView('admin.constancias.grupoInvestigacionPDF', ['grupo' => $grupo]);
    return $pdf->stream();
  }

  public function main() {
    return view('admin.admin.usuarios_investigadores');
  }
}

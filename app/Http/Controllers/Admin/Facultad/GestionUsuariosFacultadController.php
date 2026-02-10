<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionUsuariosFacultadController extends Controller {
  public function listado() {
    $usuarios = DB::table('Usuario AS a')
      ->join('Usuario_facultad AS b', 'b.id', '=', 'a.tabla_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'b.id',
        'b.codigo_trabajador',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2) AS apellidos"),
        'b.nombres',
        'c.nombre AS facultad',
        'a.username'
      ])
      ->where('a.tabla', '=', 'Usuario_facultad')
      ->get();

    return $usuarios;
  }

  public function searchInvestigador(Request $request) {
    $investigadores = DB::table('Usuario_investigador AS a')
      ->join('Facultad as b', 'b.id', '=', 'a.facultad_id')
      ->select(
        DB::raw("CONCAT(a.codigo, ' | ', a.doc_numero, ' | ', a.apellido1, ' ', a.apellido2, ' ', a.nombres) AS value"),
        'a.id',
        'a.codigo',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        DB::raw("'UNMSM' AS institucion"),
        'a.facultad_id',
        DB::raw('b.nombre AS facultad'),
        'a.sexo',
      )
      ->where('tipo', 'LIKE', 'DOCENTE%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function crearUsuarioFacultad(Request $request) 
  {
    $existe = DB::table('Usuario_facultad')
        ->where('investigador_id', '=', $request->input('investigador_id'))
        ->exists();

    if ($existe) {
      return ['message' => 'error','detail'  => 'Este usuario ya está registrado'];
    }
    $now = now();
    $id = DB::table('Usuario_facultad')
        ->insertGetId([
            'investigador_id'   => $request->input('investigador_id'),
            'facultad_id'       => $request->input('facultad_id'),
            'codigo_trabajador' => $request->input('codigo'),
            'apellido1'         => $request->input('apellido1'),
            'apellido2'         => $request->input('apellido2'),
            'nombres'           => $request->input('nombres'),
            'sexo'              => $request->input('sexo'),
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
      
    DB::table('Usuario')
      ->insert([
        'username' => $request->input('username'),
        'password' => bcrypt($request->password),
        'tabla' => 'Usuario_facultad',
        'tabla_id' => $id,
        'estado' => 1
      ]);

  return ['message' => 'success', 'detail' => 'Usuario registrado correctamente'];
  }
}
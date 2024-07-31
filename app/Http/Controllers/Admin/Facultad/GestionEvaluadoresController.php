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

  public function crearEvaluador(Request $request) {
    $id = 0;
    $cuenta = 0;

    if ($request->input('investigador_id') != null) {
      $cuenta = DB::table('Usuario_evaluador')
        ->where('usuario_investigador_id', '=', $request->input('investigador_id'))
        ->count();
    }

    if ($cuenta == 0) {
      if ($request->input('investigador_id') == null) {
        $id = DB::table('Usuario_evaluador')
          ->insertGetId([
            'tipo' => 'Externo',
            'apellidos' => $request->input('apellidos'),
            'nombres' => $request->input('nombres'),
            'institucion' => $request->input('institucion'),
          ]);
      } else {
        $id = DB::table('Usuario_evaluador')
          ->insertGetId([
            'tipo' => 'Interno',
            'usuario_investigador_id' => $request->input('investigador_id'),
            'apellidos' => $request->input('apellidos'),
            'nombres' => $request->input('nombres'),
            'institucion' => $request->input('institucion'),
          ]);
      }

      DB::table('Usuario')
        ->insert([
          'username' => $request->input('username'),
          'password' => bcrypt($request->password),
          'tabla' => 'Usuario_evaluador',
          'tabla_id' => $id,
          'estado' => 1
        ]);

      return ['message' => 'success', 'detail' => 'Evaluador registrado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Este investigador ya está registrado como evaluador'];
    }
  }
}

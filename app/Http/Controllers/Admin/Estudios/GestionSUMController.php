<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionSUMController extends Controller {

  public function listadoLocal(Request $request) {

    $listado = DB::table('Repo_sum')
      ->select([
        DB::raw("CONCAT(codigo_alumno, ' ', dni, ' ', apellido_paterno, ' ', apellido_materno, ' ', nombres) AS value"),
        'id',
        'codigo_alumno',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'sexo',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'telefono',
        'telefono_personal',
        'correo_electronico',
        'correo_electronico_personal',
        'domicilio',
        'facultad',
        'programa',
        'año_ciclo_estudio',
        'num_periodo_acad_matric',
        'situacion_academica',
        'permanencia',
        'ultimo_periodo_matriculado',
      ]);
    if (!empty($request->query('search'))) {
      $listado = $listado->having('value', 'LIKE', '%' . $request->query('search') . '%');
    }
    $listado = $listado
      ->orderBy('apellido_paterno')
      ->orderBy('apellido_materno')
      ->orderByDesc('id')
      ->paginate(10, ['*'], 'page', $request->query('page'));

    return $listado;
  }

  public function listadoSum(Request $request) {

    $concat = "(codigo_alumno || ' | ' || dni || ' | ' || apellido_paterno || ' ' || apellido_materno || ', ' || nombres || ' | ' || programa)";

    $listado = DB::connection('sum')->table('ALUMNO')
      ->select(
        DB::raw($concat . " value"),
        'codigo_alumno',
        'dni',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'sexo',
        'facultad',
        'programa',
        'permanencia',
        'ultimo_periodo_matriculado',
        DB::raw("TO_CHAR(FECHA_NACIMIENTO, 'dd-MM-yyyy') fecha_nacimiento"),
        'telefono',
        'telefono_personal',
        'correo_electronico',
        'correo_electronico_personal',
      )
      ->whereRaw($concat . " LIKE '%' || ? || '%'", [mb_strtoupper($request->query('query'))])
      ->limit(10)
      ->get();

    return $listado;
  }
}

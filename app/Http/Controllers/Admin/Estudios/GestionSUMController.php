<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestionSUMController extends Controller {

  public function listadoLocal(Request $request) {

    $concat = "(codigo_alumno || ' | ' || dni || ' | ' || apellido_paterno || ' ' || apellido_materno || ', ' || nombres || ' | ' || programa)";

    $listado = DB::table('Repo_sum')
      ->select([
        DB::raw($concat . " value"),
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

      ])
      ->having("value", " LIKE", "%" . $request->query('query') . "%")
      ->get();

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
        'facultad',
        'programa',
        'permanencia',
        'ultimo_periodo_matriculado'
      )
      ->whereRaw($concat . " LIKE '%' || ? || '%'", [$request->query('query')])
      ->get();

    return $listado;
  }
}

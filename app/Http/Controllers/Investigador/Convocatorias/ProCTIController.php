<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProCTIController extends S3Controller {
  public function getDataToPaso1(Request $request) {
    $data = DB::table('Grupo_integrante AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->join('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->select([
        'b.nombre AS facultad',
        'c.grupo_nombre',
        'a.grupo_id'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.grupo_id', '=', $data->grupo_id)
      ->whereNull('a.concytec_codigo')
      ->get();

    $ocdeLevel1 = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label")
      ])
      ->whereNull('parent_id')
      ->get();

    return ['data' => $data, 'lineas' => $lineas, 'ocde1' => $ocdeLevel1];
  }

  public function getOcde(Request $request) {
    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label")
      ])
      ->where('parent_id', '=', $request->query('parent_id'))
      ->get();

    return $ocde;
  }

  public function registrarPaso1(Request $request) {
    $data = DB::table('Grupo_integrante')
      ->select([
        'facultad_id',
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->first();

    $id = DB::table('Proyecto')
      ->insertGetId([
        'facultad_id' => $data->facultad_id,
        'grupo_id' => $data->grupo_id,
        'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"],
        'ocde_id' => $request->input('ocde_3')["value"],
        'titulo' => $request->input('titulo'),
        'tipo_proyecto' => 'PRO-CTIE',
        'fecha_inscripcion' => Carbon::now(),
        'localizacion' => $request->input('localizacion'),
        'periodo' => 2024,
        'convocatoria' => 1,
        'step' => 2,
        'estado' => 6,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'objetivo_ods',
        'detalle' => $request->input('ods')["value"]
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'tipo_investigacion',
        'detalle' => $request->input('tipo_investigacion')
      ]);

    DB::table('Proyecto_integrante')
      ->insert([
        'proyecto_id' => $id,
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'condicion' => 'Responsable',
        'proyecto_integrante_tipo_id' => 86,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);
  }
}

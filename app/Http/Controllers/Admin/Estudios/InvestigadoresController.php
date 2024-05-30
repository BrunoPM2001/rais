<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestigadoresController extends Controller {
  public function listado() {

    $puntajeT = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select(
        'a.investigador_id',
        DB::raw('SUM(a.puntaje) AS puntaje')
      )
      ->groupBy('a.investigador_id');

    $investigadores = DB::table('Usuario_investigador AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($puntajeT, 'puntaje', 'puntaje.investigador_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.rrhh_status',
        'puntaje.puntaje',
        'a.tipo',
        'b.nombre AS facultad',
        'a.codigo',
        'a.codigo_orcid',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.fecha_nac',
        'a.doc_tipo',
        'a.doc_numero',
        'a.telefono_movil'
      )
      ->get();

    return ['data' => $investigadores];
  }

  public function getOne(Request $request) {
    $investigador = DB::table('Usuario_investigador')
      ->select([
        'tipo_investigador',
        'tipo_investigador_categoria',
        'tipo_investigador_programa',
        'tipo_investigador_estado',
        'tipo',
        'estado',
        'rrhh_status',
        'fecha_icsi',
        'nombres',
        'apellido1',
        'apellido2',
        'sexo',
        'doc_tipo',
        'doc_numero',
        'fecha_nac',
        'pais',
        'telefono_casa',
        'telefono_trabajo',
        'direccion1',
        'direccion2',
        'email1',
        'email2',
        'email3',
        'facebook',
        'twitter',
        'codigo_orcid',
        'researcher_id',
        'scopus_id',
        'cti_vitae',
        'google_scholar',
        'renacyt',
        'renacyt_nivel',
        'palabras_clave',
        'indice_h',
        'indice_h_url',
        'facultad_id',
        'dependencia_id',
        'instituto_id',
        'codigo',
        'docente_categoria',
        'posicion_unmsm',
        'biografia',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $facultades = DB::table('Facultad')
      ->select([
        'id AS value',
        'nombre AS label'
      ])->get();

    $paises = DB::table('Pais')
      ->select([
        'name AS value'
      ])->get();

    $dependencias = DB::table('Dependencia')
      ->select([
        'id AS value',
        'dependencia AS label'
      ])->get();

    $institutos = DB::table('Instituto')
      ->select([
        'id AS value',
        'instituto AS label'
      ])
      ->where('estado', '=', 1)
      ->get();

    return [
      'data' => $investigador,
      'paises' => $paises,
      'facultades' =>  $facultades,
      'dependencias' =>  $dependencias,
      'institutos' =>  $institutos,
    ];
  }

  public function update(Request $request) {
    DB::table('Usuario_investigador')
      ->where('id', '=', $request->input('id'))
      ->update([
        'tipo_investigador' => $request->input('tipo_investigador'),
        'tipo_investigador_categoria' => $request->input('tipo_investigador_categoria'),
        'tipo_investigador_programa' => $request->input('tipo_investigador_programa'),
        'tipo_investigador_estado' => $request->input('tipo_investigador_estado'),
        'tipo' => $request->input('tipo'),
        'estado' => $request->input('estado'),
        'rrhh_status' => $request->input('rrhh_status'),
        'fecha_icsi' => $request->input('fecha_icsi'),
        'nombres' => $request->input('nombres'),
        'apellido1' => $request->input('apellido1'),
        'apellido2' => $request->input('apellido2'),
        'sexo' => $request->input('sexo'),
        'doc_tipo' => $request->input('doc_tipo'),
        'doc_numero' => $request->input('doc_numero'),
        'fecha_nac' => $request->input('fecha_nac'),
        'pais' => $request->input('pais'),
        'telefono_casa' => $request->input('telefono_casa'),
        'telefono_trabajo' => $request->input('telefono_trabajo'),
        'direccion1' => $request->input('direccion1'),
        'direccion2' => $request->input('direccion2'),
        'email1' => $request->input('email1'),
        'email2' => $request->input('email2'),
        'email3' => $request->input('email3'),
        'facebook' => $request->input('facebook'),
        'twitter' => $request->input('twitter'),
        'codigo_orcid' => $request->input('codigo_orcid'),
        'researcher_id' => $request->input('researcher_id'),
        'scopus_id' => $request->input('scopus_id'),
        'cti_vitae' => $request->input('cti_vitae'),
        'google_scholar' => $request->input('google_scholar'),
        'renacyt' => $request->input('renacyt'),
        'renacyt_nivel' => $request->input('renacyt_nivel'),
        'palabras_clave' => $request->input('palabras_clave'),
        'indice_h' => $request->input('indice_h'),
        'indice_h_url' => $request->input('indice_h_url'),
        'codigo' => $request->input('codigo'),
        'docente_categoria' => $request->input('docente_categoria'),
        'posicion_unmsm' => $request->input('posicion_unmsm'),
        'biografia' => $request->input('biografia'),
      ]);

    return [
      'message' => 'info',
      'detail' => 'Investigador actualizado exitosamente'
    ];
  }

  //  Autosuggest
  public function searchDocenteRrhh(Request $request) {
    $investigadores = DB::table('Repo_rrhh AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.doc_numero', '=', 'a.ser_doc_id_act')
      ->select(
        DB::raw("CONCAT(TRIM(a.ser_cod_ant), ' | ', a.ser_doc_id_act, ' | ', a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'ser_ape_pat AS apellido1',
        'ser_ape_mat AS apellido2',
        'ser_nom AS nombres',
        'ser_cod_ant AS codigo',
        'abv_doc_id AS doc_tipo',
        'ser_doc_id_act AS doc_numero',
        'ser_cod_dep_ces AS dependencia_id',
        'ser_cat_act AS docente_categoria',
        'ser_sexo AS sexo',
        'ser_fech_nac AS fecha_nac'
      )
      ->where('des_tip_ser', 'LIKE', 'DOCENTE%')
      ->groupBy('ser_cod_ant')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }
}

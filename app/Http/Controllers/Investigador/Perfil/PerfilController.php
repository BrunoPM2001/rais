<?php

namespace App\Http\Controllers\Investigador\Perfil;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilController extends S3Controller {
  public function getData(Request $request) {
    $data = DB::table('Usuario_investigador AS a')
      ->select([
        DB::raw("CONCAT(a.nombres, ' ', a.apellido1, ' ', a.apellido2) AS nombres"),
        DB::raw("CONCAT(a.doc_tipo, ' - ', a.doc_numero) AS doc"),
        'a.fecha_nac',
        'a.direccion1',
        'a.email1',

        'a.telefono_movil',
        'a.telefono_trabajo',
        'a.telefono_casa',
        'a.codigo',
        'a.dependencia_id',
        'a.facultad_id',
        'a.email3',

        'a.codigo_orcid',
        'a.google_scholar',
        'a.scopus_id',
        'a.researcher_id',
        'a.cti_vitae',
        'a.renacyt',
        'a.renacyt_nivel',

        'a.tipo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(a.docente_categoria, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(a.docente_categoria, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(a.docente_categoria, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(a.docente_categoria, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END AS categoria"),
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        DB::raw("SUBSTRING_INDEX(a.docente_categoria, '-', -1) AS horas"),
        'a.especialidad',
        'a.titulo_profesional',
        'a.grado',

        'a.biografia',
        'a.facebook',
        'a.twitter',
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $dependencias = DB::table('Dependencia')
      ->select([
        'id AS value',
        'dependencia AS label'
      ])
      ->get();

    $facultades = DB::table('Facultad')
      ->select([
        'id AS value',
        'nombre AS label'
      ])
      ->get();

    return ['data' => $data, 'dependencias' => $dependencias, 'facultades' => $facultades];
  }

  public function updateData(Request $request) {
    DB::table('Usuario_investigador')
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->update([
        'direccion1' => $request->input('direccion1'),
        'email1' => $request->input('email1'),
        'telefono_movil' => $request->input('telefono_movil'),
        'telefono_trabajo' => $request->input('telefono_trabajo'),
        'telefono_casa' => $request->input('telefono_casa'),
        'dependencia_id' => $request->input('dependencia_id')["value"],
        'facultad_id' => $request->input('facultad_id')["value"],
        'scopus_id' => $request->input('scopus_id'),
        'researcher_id' => $request->input('researcher_id'),
        'especialidad' => $request->input('especialidad'),
        'titulo_profesional' => $request->input('titulo_profesional'),
        'grado' => $request->input('grado')["value"],
        'biografia' => $request->input('biografia'),
        'facebook' => $request->input('facebook'),
        'twitter' => $request->input('twitter'),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'success', 'detail' => 'Datos actualizados con éxito'];
  }
}

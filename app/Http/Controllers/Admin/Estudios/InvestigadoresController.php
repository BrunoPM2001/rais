<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Exports\Admin\InvestigadoresExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
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
        'a.telefono_movil',
        'a.email3'
      )
      ->get();

    return $investigadores;
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
        DB::raw("CASE (rrhh_status)
          WHEN 1 THEN 'Activo'
          ELSE 'Inactivo'
        END AS rrhh_status"),
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
        'telefono_movil',
        'direccion1',
        'direccion2',
        'email1',
        'email2',
        'email3',
        'facebook',
        'twitter',
        'link',
        'grado',
        'especialidad',
        'titulo_profesional',
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

    $doc_categorias = DB::table('Docente_categoria')
      ->select([
        'categoria_id AS value'
      ])
      ->get();


    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select([
        'a.id',
        'b.grupo_nombre AS nombre',
        'b.grupo_categoria AS categoria',
        'a.condicion',
        DB::raw("CASE(b.estado)
          WHEN -2 THEN 'Disuelto'
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 4 THEN 'Registrado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado"),
        DB::raw("DATE(a.created_at) AS created_at")
      ])
      ->where('a.investigador_id', '=', $request->query('id'))
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    $proyectos = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'b.id',
        'b.periodo',
        'b.tipo_proyecto',
        'b.titulo',
        'b.codigo_proyecto',
        'c.nombre AS condicion',
        DB::raw("CASE(b.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 3 THEN 'En evaluacion'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'Sustentado'
            WHEN 9 THEN 'En ejecución'
            WHEN 10 THEN 'Ejecutado'
            WHEN 11 THEN 'Concluído'
          ELSE 'Sin estado' END AS estado"),
        DB::raw("DATE(a.created_at) AS created_at")
      ])
      ->where('a.investigador_id', '=', $request->query('id'))
      ->get();

    return [
      'data' => $investigador,
      'paises' => $paises,
      'facultades' =>  $facultades,
      'dependencias' =>  $dependencias,
      'institutos' =>  $institutos,
      'docente_categorias' => $doc_categorias,
      //  Datos adicionales
      'grupos' => $grupos,
      'proyectos' => $proyectos
    ];
  }

  public function getSelectsData() {
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

    $doc_categorias = DB::table('Docente_categoria')
      ->select([
        'categoria_id AS value'
      ])
      ->get();

    return [
      'paises' => $paises,
      'facultades' =>  $facultades,
      'dependencias' =>  $dependencias,
      'institutos' =>  $institutos,
      'docente_categorias' => $doc_categorias
    ];
  }

  public function create(Request $request) {
    DB::table('Usuario_investigador')
      ->insert([
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
        'telefono_movil' => $request->input('telefono_movil'),
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
        'facultad_id' => $request->input('facultad_id'),
        'dependencia_id' => $request->input('dependencia_id'),
        'instituto_id' => $request->input('instituto_id'),
        'codigo' => $request->input('codigo'),
        'docente_categoria' => $request->input('docente_categoria'),
        'posicion_unmsm' => $request->input('posicion_unmsm'),
        'biografia' => $request->input('biografia'),
      ]);

    return [
      'message' => 'info',
      'detail' => 'Investigador creado exitosamente'
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
        'telefono_movil' => $request->input('telefono_movil'),
        'direccion1' => $request->input('direccion1'),
        'direccion2' => $request->input('direccion2'),
        'email1' => $request->input('email1'),
        'email2' => $request->input('email2'),
        'email3' => $request->input('email3'),
        'facebook' => $request->input('facebook'),
        'twitter' => $request->input('twitter'),
        'link' => $request->input('link'),
        'grado' => $request->input('grado')["value"],
        'titulo_profesional' => $request->input('titulo_profesional'),
        'especialidad' => $request->input('especialidad'),
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
        'facultad_id' => $request->input('facultad_id'),
        'dependencia_id' => $request->input('dependencia_id'),
        'instituto_id' => $request->input('instituto_id'),
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

  public function licenciasTipo() {
    $licencias = DB::table('Licencia_tipo')
      ->select([
        'id AS value',
        'tipo AS label'
      ])
      ->get();

    return $licencias;
  }

  public function getLicencias(Request $request) {
    $licencias = DB::table('Licencia AS a')
      ->join('Licencia_tipo AS b', 'b.id', '=', 'a.licencia_tipo_id')
      ->select([
        'a.id',
        'b.tipo',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.comentario',
        'a.documento',
      ])
      ->where('a.investigador_id', '=', $request->query('investigador_id'))
      ->get();

    return $licencias;
  }

  public function addLicencia(Request $request) {
    DB::table('Licencia')
      ->insert([
        'investigador_id' => $request->input('investigador_id'),
        'licencia_tipo_id' => $request->input('licencia_tipo_id'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'documento' => $request->input('documento') ?? "",
        'comentario' => $request->input('comentario') ?? ""
      ]);

    return [
      'message' => 'success',
      'detail' => 'Licencia añadida con éxito'
    ];
  }

  public function updateLicencia(Request $request) {
    DB::table('Licencia')
      ->where('id', '=', $request->input('id'))
      ->update([
        'licencia_tipo_id' => $request->input('licencia_tipo_id')["value"],
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'documento' => $request->input('documento') ?? "",
        'comentario' => $request->input('comentario') ?? ""
      ]);

    return [
      'message' => 'info',
      'detail' => 'Licencia actualizada con éxito'
    ];
  }

  public function deleteLicencia(Request $request) {
    DB::table('Licencia')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return [
      'message' => 'info',
      'detail' => 'Licencia eliminada con éxito'
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

  public function searchEstudiante(Request $request) {
    $estudiantes = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno AS codigo',
        'a.dni AS doc_numero',
        'a.apellido_paterno AS apellido1',
        'a.apellido_materno AS apellido2',
        'a.nombres AS nombres',
        'a.domicilio AS direccion1',
        'a.correo_electronico_personal AS email1',
        'a.correo_electronico AS email3',
        'a.fecha_nacimiento AS fecha_nac',
        'a.sexo',
        'a.telefono AS telefono_casa',
        'a.telefono_personal AS telefono_movil',
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula', 'Egresado'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function excelComplete(Request $request) {

    $filters = $request->all();
    set_time_limit(300);

    $export = new InvestigadoresExport($filters);

    return Excel::download($export, 'publicaciones.xlsx');
  }
}

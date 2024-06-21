<?php

namespace App\Http\Controllers\Investigador\Grupo;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends S3Controller {

  public function listadoGrupos(Request $request) {
    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select(
        'b.id',
        'b.grupo_nombre',
        'b.grupo_categoria',
        'a.condicion',
        'a.cargo',
        'b.resolucion_fecha',
        'b.estado',
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo', '=', 'grupo')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    return ['data' => $grupos];
  }

  public function listadoSolicitudes(Request $request) {
    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select(
        'b.id',
        'b.grupo_nombre',
        'b.grupo_categoria',
        'a.condicion',
        'a.cargo',
        'b.resolucion_fecha',
        'b.estado',
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo', '=', 'solicitud')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    return ['data' => $grupos];
  }

  public function detalle(Request $request) {
    $detalle = DB::table('Grupo AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        'a.estado',
        'b.nombre AS facultad',
        'a.telefono',
        'a.anexo',
        'a.oficina',
        'a.direccion',
        'a.email',
        'a.web',
        'a.presentacion',
        'a.objetivos',
        'a.servicios',
      )
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $detalle;
  }

  public function listarMiembros(Request $request) {
    $miembros = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.grupo_integrante_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.investigador_id',
        'a.condicion',
        'a.cargo',
        'b.doc_numero',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombres'),
        'b.codigo_orcid',
        'b.google_scholar',
        'b.cti_vitae',
        'b.tipo',
        'c.nombre AS facultad',
        'a.tesista',
        DB::raw('COUNT(d.id) AS proyectos'),
        'a.fecha_inclusion',
        'a.fecha_exclusion'
      )
      ->where('a.grupo_id', '=', $request->query('grupo_id'));

    //  Tipo de miembro
    $miembros = $request->query('estado') == 1 ? $miembros->whereNot('a.condicion', 'LIKE', 'Ex%') : $miembros->where('a.condicion', 'LIKE', 'Ex%');

    $miembros = $miembros->groupBy('a.id')
      ->get();

    return ['data' => $miembros];
  }

  public function searchEstudiante(Request $request) {
    $estudiantes = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno',
        'a.dni',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.facultad',
        'a.programa',
        'a.permanencia',
        'b.email3'
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function searchEgresado(Request $request) {
    $egresados = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno',
        'a.dni',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.facultad',
        'a.programa',
        'a.permanencia',
        'a.ultimo_periodo_matriculado'
      )
      ->whereIn('a.permanencia', ['Egresado'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $egresados;
  }

  public function incluirMiembroData(Request $request) {
    switch ($request->query('tipo')) {
      case "estudiante" || "egresado":
        $investigador = DB::table('Usuario_investigador AS a')
          ->leftJoin('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
          ->leftJoin('Grupo AS c', function ($join) {
            $join->on('c.id', '=', 'b.grupo_id')
              ->where('b.condicion', 'NOT LIKE', 'Ex%');
          })
          ->select(
            'a.id',
            DB::raw('IFNULL(SUM(b.condicion NOT LIKE "Ex%"), 0) AS grupos'),
            DB::raw('GROUP_CONCAT(DISTINCT IF(b.condicion NOT LIKE "Ex%", c.grupo_nombre, NULL)) AS grupo_nombre')
          )
          ->where('a.codigo', '=', $request->query('codigo'))
          ->groupBy('a.codigo')
          ->first();

        if ($investigador == null) {
          return [
            'message' => 'warning',
            'detail' => 'No está registrado como investigador'
          ];
        } else if ($investigador->grupos > 0) {
          return [
            'message' => 'error',
            'detail' => 'Esta persona ya pertenece a un grupo de investigación: ' . $investigador->grupo_nombre
          ];
        } else {
          return [
            'message' => 'success',
            'detail' => 'No pertenece a ningún grupo y tiene los datos de investigador completos'
          ];
        }
        break;
      default:
        return [
          'message' => 'error',
          'detail' => 'Error al solicitar información'
        ];
        break;
    }
  }

  public function agregarMiembro(Request $request) {
    $id = $request->input('grupo_id');
    $date = Carbon::now();
    $name = $id . "-formato_adhesion-" . $date->format('Ymd-His');

    if ($request->hasFile('file')) {
      $nameFile = $id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "grupo-integrante-doc", $nameFile);

      switch ($request->input('tipo_registro')) {
        case 'externo':
          $investigador_id = DB::table('Usuario_investigador')
            ->insertGetId([
              'codigo_orcid' => $request->input('codigo_orcid'),
              'apellido1' => $request->input('apellido1'),
              'apellido2' => $request->input('apellido2'),
              'nombres' => $request->input('nombres'),
              'sexo' => $request->input('sexo'),
              'institucion' => $request->input('institucion'),
              'pais' => $request->input('pais'),
              'direccion1' => $request->input('direccion1'),
              'doc_tipo' => $request->input('doc_tipo'),
              'doc_numero' => $request->input('doc_numero'),
              'telefono_movil' => $request->input('telefono_movil'),
              'titulo_profesional' => $request->input('titulo_profesional'),
              'grado' => $request->input('grado'),
              'especialidad' => $request->input('especialidad'),
              'researcher_id' => $request->input('researcher_id'),
              'scopus_id' => $request->input('scopus_id'),
              'link' => $request->input('link'),
              'posicion_unmsm' => $request->input('posicion_unmsm'),
              'biografia' => $request->input('biografia'),
            ]);

          DB::table('Grupo_integrante')
            ->insertGetId([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $investigador_id,
              'tipo' => 'Externo',
              'condicion' => 'Adherente',
              'observacion' => $request->input('observacion'),
              'fecha_inclusion' => $date,
              'estado' => '1',
              'created_at' => $date,
              'updated_at' => $date
            ]);

          DB::table('Grupo_integrante_doc')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $investigador_id,
              'nombre' => 'Formato de adhesión',
              'key' => $request->input(),
              'fecha' => $date,
              'estado' => 1
            ]);

          return [
            'message' => 'success',
            'detail' => 'Miembro registrado exitosamente'
          ];
          break;
        case 'estudiante' || 'egresado':
          DB::table('Grupo_integrante')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $request->input('investigador_id'),
              'condicion' => $request->input('condicion'),
              'fecha_inclusion' => $date,
              'estado' => '1',
              'created_at' => $date,
              'updated_at' => $date
            ]);

          return [
            'message' => 'success',
            'detail' => 'Miembro registrado exitosamente'
          ];
          break;
      }
    } else {
      return [
        'message' => 'error',
        'detail' => 'Error cargando formato de adhesión'
      ];
    }
  }

  public function excluirMiembro(Request $request) {
    DB::table('Grupo_integrante')
      ->where('id', '=', $request->input('id'))
      ->update([
        'condicion' => "Ex " . $request->input('condicion'),
        'fecha_exclusion' => $request->input('fecha_exclusion'),
        'resolucion_exclusion' => $request->input('resolucion_exclusion'),
        'resolucion_exclusion_fecha' => $request->input('resolucion_exclusion_fecha'),
        'resolucion_oficina_exclusion' => $request->input('resolucion_oficina_exclusion'),
        'observacion_excluir' => $request->input('observacion_excluir'),
        'estado' => "-2"
      ]);

    return [
      'message' => 'info',
      'detail' => 'Miembro excluído exitosamente'
    ];
  }

  public function visualizarMiembro(Request $request) {
    $informacion = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Dependencia AS c', 'c.id', '=', 'b.dependencia_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.codigo',
        'b.doc_numero',
        'b.docente_categoria',
        'b.docente_categoria',
        'c.dependencia',
        'd.nombre AS facultad',
        'b.codigo_orcid',
        'b.researcher_id',
        'b.scopus_id',
        'a.fecha_inclusion',
        'a.resolucion_oficina',
        'a.resolucion',
        'a.resolucion_fecha',
      ])
      ->where('a.id', '=', $request->query('grupo_integrante_id'))
      ->first();

    $proyectos = DB::table('Grupo_integrante AS a')
      ->join('Proyecto_integrante AS b', 'b.grupo_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->select([
        'c.codigo_proyecto',
        'c.titulo',
        'c.tipo_proyecto',
        'c.periodo'
      ])
      ->where('a.id', '=', $request->query('grupo_integrante_id'))
      ->where('c.estado', '=', 1)
      ->groupBy('c.id')
      ->get();

    return ['informacion' => $informacion, 'proyectos' => $proyectos];
  }
}

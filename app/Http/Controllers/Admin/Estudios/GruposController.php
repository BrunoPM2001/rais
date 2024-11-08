<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GruposController extends S3Controller {

  public function listadoGrupos() {
    //  Subquery para obtener el coordinador de cada grupo
    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    $grupos = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        'a.grupo_categoria',
        DB::raw('COUNT(b.id) AS cantidad_integrantes'),
        'd.nombre AS facultad',
        'coordinador.nombre AS coordinador',
        'a.resolucion_rectoral',
        'a.created_at',
        'a.updated_at',
        DB::raw("CASE(a.estado)
          WHEN -2 THEN 'Disuelto'
          WHEN 4 THEN 'Registrado'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado")
      )
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->where('a.tipo', '=', 'grupo')
      ->groupBy('a.id')
      ->get();

    return $grupos;
  }

  public function listadoSolicitudes() {
    //  Subquery para obtener el coordinador de cada grupo
    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    //  Query base
    $solicitudes = DB::table('Grupo AS a')
      ->leftJoin('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        DB::raw('COUNT(b.id) AS cantidad_integrantes'),
        'd.nombre AS facultad',
        'coordinador.nombre AS coordinador',
        'a.estado',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          ELSE 'Estado desconocido'
        END AS estado"),
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.tipo', '=', 'solicitud')
      ->havingBetween('a.estado', [0, 6])
      ->groupBy('a.id')
      ->get();

    return $solicitudes;
  }

  public function detalle($grupo_id) {

    $s3 = $this->s3Client;

    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    $detalle = DB::table('Grupo AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->select(
        'a.id',
        'a.tipo',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        'a.resolucion_rectoral_creacion',
        DB::raw("IFNULL(a.resolucion_creacion_fecha, '') AS resolucion_creacion_fecha"),
        'a.resolucion_rectoral',
        DB::raw("IFNULL(a.resolucion_fecha, '') AS resolucion_fecha"),
        'a.observaciones',
        'a.observaciones_admin',
        'coordinador.nombre AS coordinador',
        'a.estado',
        'b.nombre AS facultad',
        'a.telefono',
        'a.anexo',
        'a.oficina',
        'a.direccion',
        'a.email',
        'a.web',
        'a.grupo_categoria',
        'a.presentacion',
        'a.objetivos',
        'a.servicios',
        DB::raw("CONCAT('/minio/grupo-infraestructura-sgestion/', a.infraestructura_sgestion) AS url"),
        'a.infraestructura_ambientes'
      )
      ->where('a.id', '=', $grupo_id)
      ->first();

    $detalle->url = $detalle->url ?? "#";

    return $detalle;
  }

  public function updateDetalle(Request $request) {
    if ($request->input('tipo') == 'grupo') {
      $count = DB::table('Grupo')
        ->where('id', '=', $request->input('grupo_id'))
        ->update([
          'grupo_nombre' => $request->input('grupo_nombre'),
          'grupo_nombre_corto' => $request->input('grupo_nombre_corto'),
          'resolucion_rectoral_creacion' => $request->input('resolucion_rectoral_creacion'),
          'resolucion_creacion_fecha' => $request->input('resolucion_creacion_fecha'),
          'resolucion_rectoral' => $request->input('resolucion_rectoral'),
          'resolucion_fecha' => $request->input('resolucion_fecha'),
          'observaciones' => $request->input('observaciones'),
          'observaciones_admin' => $request->input('observaciones_admin'),
          'estado' => $request->input('estado')["value"],
          'grupo_categoria' => $request->input('grupo_categoria')["value"],
          'telefono' => $request->input('telefono'),
          'anexo' => $request->input('anexo'),
          'oficina' => $request->input('oficina'),
          'direccion' => $request->input('direccion'),
          'email' => $request->input('email'),
          'web' => $request->input('web'),
          'updated_at' => Carbon::now()
        ]);
    } else {
      $count = DB::table('Grupo')
        ->where('id', '=', $request->input('grupo_id'))
        ->update([
          'grupo_nombre' => $request->input('grupo_nombre'),
          'grupo_nombre_corto' => $request->input('grupo_nombre_corto'),
          'resolucion_rectoral_creacion' => $request->input('resolucion_rectoral_creacion'),
          'resolucion_creacion_fecha' => $request->input('resolucion_creacion_fecha'),
          'resolucion_rectoral' => $request->input('resolucion_rectoral'),
          'resolucion_fecha' => $request->input('resolucion_fecha'),
          'observaciones' => $request->input('observaciones'),
          'observaciones_admin' => $request->input('observaciones_admin'),
          'estado' => $request->input('estado')["value"],
          'telefono' => $request->input('telefono'),
          'anexo' => $request->input('anexo'),
          'oficina' => $request->input('oficina'),
          'direccion' => $request->input('direccion'),
          'email' => $request->input('email'),
          'web' => $request->input('web'),
          'updated_at' => Carbon::now()
        ]);
    }

    if ($count > 0) {
      return ['message' => 'success', 'detail' => 'Datos del grupo actualizados correctamente'];
    } else {
      return ['message' => 'warning', 'detail' => 'No se pudo actualizar la información!'];
    }
  }

  public function aprobarSolicitud(Request $request) {
    $count = DB::table('Grupo')
      ->where('id', '=', $request->input('grupo_id'))
      ->where('tipo', '=', 'solicitud')
      ->update([
        'grupo_categoria' => $request->input('grupo_categoria')["value"],
        'tipo' => 'grupo',
        'estado' => 4,
        'updated_at' => Carbon::now()
      ]);

    if ($count > 0) {
      return ['message' => 'success', 'detail' => 'Solicitud aprobada correctamente'];
    } else {
      return ['message' => 'warning', 'detail' => 'No se pudo aprobar la solicitud'];
    }
  }

  public function disolverGrupo(Request $request) {
    $count = DB::table('Grupo')
      ->where('id', '=', $request->input('grupo_id'))
      ->where('estado', '!=', '-2')
      ->update([
        'motivo_disolucion' => $request->input('motivo_disolucion'),
        'fecha_disolucion' => Carbon::now(),
        'estado' => '-2',
        'updated_at' => Carbon::now()
      ]);

    if ($count > 0) {
      DB::table('Grupo_integrante')
        ->where('grupo_id', '=', $request->input('grupo_id'))
        ->whereNot('condicion', 'LIKE', 'Ex%')
        ->update([
          'condicion' => DB::raw("CONCAT('Ex ', condicion)"),
          'cargo' => DB::raw("IFNULL(null,CONCAT('Ex ', cargo))"),
          'observacion_excluir' => 'Grupo disuelto',
          'fecha_exclusion' => Carbon::now(),
          'estado' => '-2',
          'updated_at' => Carbon::now()
        ]);

      return ['message' => 'info', 'detail' => 'Grupo disuelto exitosamente'];
    } else {
      return ['message' => 'error', 'detail' => 'No se pudo realizar la disolución'];
    }
  }

  public function miembros($grupo_id, $estado) {
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
      ->where('a.grupo_id', '=', $grupo_id);

    //  Tipo de miembro
    $miembros = $estado == 1 ? $miembros->whereNot('a.condicion', 'LIKE', 'Ex%') : $miembros->where('a.condicion', 'LIKE', 'Ex%');

    $miembros = $miembros->groupBy('a.id')
      ->orderByDesc('a.cargo')
      ->orderByDesc('a.condicion')
      ->orderBy('nombres')
      ->get();

    return ['data' => $miembros];
  }

  public function docs(Request $request) {
    $docs = DB::table('Grupo_integrante_doc AS a')
      ->select(
        'a.id',
        'a.nombre',
        DB::raw("CONCAT('/minio/grupo-integrante-doc/', a.key, '.pdf') AS url"),
        'a.fecha'
      )
      ->where('grupo_id', '=', $request->query('id'))
      ->get();

    return $docs;
  }

  public function eliminarDoc(Request $request) {
    DB::table('Grupo_integrante_doc')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Documento eliminado correctamente'];
  }

  public function lineas($grupo_id) {
    $lineas = DB::table('Grupo_linea AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->select(
        'a.id',
        'c.codigo',
        'c.nombre',
      )
      ->whereNull('a.concytec_codigo')
      ->where('a.grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $lineas];
  }

  public function proyectos($grupo_id) {
    $miembros = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Usuario_investigador AS c', 'c.id', 'b.investigador_id')
      ->select([
        'c.id AS id',
        DB::raw("null AS investigador_id"),
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        DB::raw("CONCAT(c.id, '_i') AS id_unico")
      ])
      ->where('a.id', '=', $grupo_id)
      ->whereNot('b.condicion', 'LIKE', 'Ex%')
      ->get();

    $proyectos_nuevos = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Proyecto_integrante AS c', 'c.investigador_id', '=', 'b.investigador_id')
      ->join('Proyecto AS d', 'd.id', '=', 'c.proyecto_id')
      ->select([
        DB::raw("null AS id"),
        'd.id AS proyecto_id',
        'd.titulo',
        'd.periodo',
        'd.tipo_proyecto',
        'b.investigador_id',
        DB::raw("CONCAT(d.id, '_ph_', b.investigador_id) AS id_unico")
      ])
      ->where('a.id', '=', $grupo_id);

    $proyectos = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Proyecto_integrante_H AS c', 'c.investigador_id', '=', 'b.investigador_id')
      ->join('Proyecto_H AS d', 'd.id', '=', 'c.proyecto_id')
      ->select([
        DB::raw("null AS id"),
        'd.id AS proyecto_id',
        'd.titulo',
        'd.periodo',
        'd.tipo AS tipo_proyecto',
        'b.investigador_id',
        DB::raw("CONCAT(d.id, '_p_', b.investigador_id) AS id_unico")
      ])
      ->where('a.id', '=', $grupo_id)
      ->union($proyectos_nuevos)
      ->get();

    return $miembros->merge($proyectos);
  }

  public function publicaciones($grupo_id) {
    $publicaciones = DB::table('Grupo_integrante AS a')
      ->join('Publicacion_autor AS b', 'b.investigador_id', '=', 'a.investigador_id')
      ->join('Publicacion AS c', 'c.id', '=', 'b.publicacion_id')
      ->join('Publicacion_categoria AS d', 'd.id', '=', 'c.categoria_id')
      ->select(
        'c.id',
        'c.titulo',
        'c.fecha_publicacion',
        'd.tipo'
      )
      ->where('a.grupo_id', '=', $grupo_id)
      ->groupBy('c.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function laboratorios($grupo_id) {
    $laboratorios = DB::table('Grupo AS a')
      ->join('Grupo_infraestructura AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Laboratorio AS c', 'c.id', '=', 'b.laboratorio_id')
      ->select(
        'c.codigo',
        'c.laboratorio',
        'c.ubicacion',
        'c.responsable'
      )
      ->where('a.id', '=', $grupo_id)
      ->where('b.categoria', '=', 'laboratorio')
      ->get();

    return ['data' => $laboratorios];
  }

  //  Incluir miembro

  public function searchDocenteRrhh(Request $request) {
    $investigadores = DB::table('Repo_rrhh AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.doc_numero', '=', 'a.ser_doc_id_act')
      ->leftJoin('Licencia AS c', 'c.investigador_id', '=', 'b.id')
      ->leftJoin('Licencia_tipo AS d', 'c.licencia_tipo_id', '=', 'd.id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(TRIM(a.ser_cod_ant), ' | ', a.ser_doc_id_act, ' | ', a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'ser_ape_pat',
        'ser_ape_mat',
        'ser_nom',
        'ser_cod_ant',
        'ser_doc_id_act',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END AS categoria"),
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ser_cat_act, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ser_cat_act, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ser_cat_act, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        DB::raw("SUBSTRING_INDEX(ser_cat_act, '-', -1) AS horas"),
        'des_dep_cesantes',
        'e.nombre AS facultad',
        'e.id AS facultad_id'
      )
      ->where('des_tip_ser', 'LIKE', 'DOCENTE%')
      ->where(function ($query) {
        $query->where('c.fecha_fin', '<', date('Y-m-d'))
          ->orWhere('d.id', '=', 9)
          ->orWhereNull('d.tipo');
      })
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
        'a.codigo_alumno',
        'a.dni',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.facultad',
        DB::raw("CASE
          WHEN a.programa LIKE 'E.P.%' THEN 'Estudiante pregrado'
          ELSE 'Estudiante posgrado'
        END AS tipo"),
        'a.permanencia',
        'a.programa',
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
        DB::raw("CASE
          WHEN a.programa LIKE 'E.P.%' THEN 'Estudiante pregrado'
          ELSE 'Estudiante posgrado'
        END AS tipo"),
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
      case "titular":
        if ($request->query('investigador_id') == null) {
          return [
            'message' => 'warning',
            'detail' => 'No está registrado como investigador'
          ];
        } else {

          $investigador = DB::table('Usuario_investigador AS a')
            ->leftJoin('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
            ->leftJoin('Dependencia AS c', 'c.id', '=', 'a.dependencia_id')
            ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
            ->leftJoin('Instituto AS e', 'e.id', '=', 'a.instituto_id')
            ->leftJoin('Grupo AS f', function ($join) {
              $join->on('f.id', '=', 'b.grupo_id')
                ->where('b.condicion', 'NOT LIKE', 'Ex%');
            })
            ->select(
              'a.codigo_orcid',
              'c.dependencia',
              'c.id AS dependencia_id',
              'd.nombre AS facultad',
              'd.id AS facultad_id',
              'e.instituto',
              'e.id AS instituto_id',
              DB::raw('IFNULL(SUM(b.condicion NOT LIKE "Ex%"), 0) AS grupos'),
              DB::raw('GROUP_CONCAT(DISTINCT IF(b.condicion NOT LIKE "Ex%", f.grupo_nombre, NULL)) AS grupo_nombre')
            )
            ->where('a.id', '=', $request->query('investigador_id'))
            ->groupBy('a.id')
            ->first();

          if ($investigador->grupos > 0) {
            return [
              'message' => 'error',
              'detail' => 'Esta persona ya pertenece a un grupo de investigación: ' . $investigador->grupo_nombre
            ];
          } else if (in_array(null, [$investigador->codigo_orcid, $investigador->dependencia, $investigador->facultad, $investigador->instituto])) {
            return [
              'message' => 'warning',
              'detail' => 'Registro de investigador incompleto (necesita tener orcid, dependencia, facultad e instituto)'
            ];
          } else {
            return [
              'message' => 'success',
              'detail' => 'No pertenece a ningún grupo y tiene los datos de investigador completos',
              'codigo_orcid' => $investigador->codigo_orcid,
              'dependencia' => $investigador->dependencia,
              'facultad' => $investigador->facultad,
              'instituto' => $investigador->instituto,
              'dependencia_id' => $investigador->dependencia_id,
              'facultad_id' => $investigador->facultad_id,
              'instituto_id' => $investigador->instituto_id,
            ];
          }
        }
        break;
      case "estudiante":
      case "egresado":
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
            'message' => 'success',
            'detail' => 'No pertenece a ningún grupo'
          ];
        } else if ($investigador->grupos > 0) {
          return [
            'message' => 'error',
            'detail' => 'Esta persona ya pertenece a un grupo de investigación: ' . $investigador->grupo_nombre
          ];
        } else {
          return [
            'message' => 'success',
            'detail' => 'No pertenece a ningún grupo'
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
              'tipo' => 'Externo',
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
            ->insert([
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
              'key' => $name,
              'fecha' => $date,
              'estado' => 1
            ]);

          return [
            'message' => 'success',
            'detail' => 'Miembro registrado exitosamente'
          ];
          break;
        case 'estudiante':
        case 'egresado':

          $id_investigador = $request->input('investigador_id');

          if ($id_investigador == "null") {

            $sumData = DB::table('Repo_sum')
              ->select([
                'id_facultad',
                'codigo_alumno',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'dni',
                'sexo',
                'correo_electronico',
              ])
              ->where('id', '=', $request->input('sum_id'))
              ->first();

            $id_investigador = DB::table('Usuario_investigador')
              ->insertGetId([
                'facultad_id' => $sumData->id_facultad,
                'codigo' => $sumData->codigo_alumno,
                'nombres' => $sumData->nombres,
                'apellido1' => $sumData->apellido_paterno,
                'apellido2' => $sumData->apellido_materno,
                'doc_tipo' => 'DNI',
                'tipo' => $request->input('tipo'),
                'doc_numero' => $sumData->dni,
                'sexo' => $sumData->sexo,
                'email3' => $sumData->correo_electronico,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo_investigador' => 'Estudiante'
              ]);
          }

          DB::table('Grupo_integrante')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $id_investigador,
              'condicion' => $request->input('condicion'),
              'fecha_inclusion' => $date,
              'estado' => '1',
              'created_at' => $date,
              'updated_at' => $date
            ]);

          DB::table('Grupo_integrante_doc')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $id_investigador,
              'nombre' => 'Formato de adhesión',
              'key' => $name,
              'fecha' => $date,
              'estado' => 1
            ]);

          return [
            'message' => 'success',
            'detail' => 'Miembro registrado exitosamente'
          ];
          break;
      }
    } else {
      if ($request->input('tipo_registro') == 'titular') {
        $investigador = DB::table('Usuario_investigador AS a')
          ->select(
            'a.codigo',
            'a.dependencia_id',
            'a.facultad_id',
            'a.instituto_id',
          )
          ->where('a.id', '=', $request->input('investigador_id'))
          ->first();

        DB::table('Grupo_integrante')
          ->insert([
            'grupo_id' => $request->input('grupo_id'),
            'facultad_id' => $investigador->facultad_id,
            'dependencia_id' => $investigador->dependencia_id,
            'instituto_id' => $investigador->instituto_id,
            'investigador_id' => $request->input('investigador_id'),
            'codigo' => $investigador->codigo,
            'tipo' => $request->input('tipo'),
            'condicion' => $request->input('condicion'),
            'fecha_inclusion' => $request->input('fecha_inclusion'),
            'resolucion_oficina' => $request->input('resolucion_oficina'),
            'resolucion' => $request->input('resolucion'),
            'resolucion_fecha' => $request->input('resolucion_fecha'),
            'estado' => '1',
            'created_at' => $date,
            'updated_at' => $date
          ]);

        return [
          'message' => 'success',
          'detail' => 'Miembro registrado exitosamente'
        ];
      } else {
        return [
          'message' => 'error',
          'detail' => 'Error cargando formato de adhesión'
        ];
      }
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
        'c.dependencia',
        'd.nombre AS facultad',
        'b.codigo_orcid',
        'b.researcher_id',
        'b.scopus_id',
        'b.biografia',
        'a.resolucion_oficina',
        'a.fecha_inclusion',
        'a.resolucion',
        'a.resolucion_fecha',
        'a.observacion',
        //  Para adherente
        'b.tipo_investigador_estado',
        'b.tipo',
        'b.telefono_movil',
        'b.telefono_casa',
        'b.telefono_trabajo'
      ])
      ->where('a.id', '=', $request->query('grupo_integrante_id'))
      ->first();

    $proyectos = DB::table('Grupo_integrante AS a')
      ->join('Proyecto_integrante AS b', 'b.grupo_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->leftJoin('Proyecto_integrante_tipo AS d', 'd.id', '=', 'b.proyecto_integrante_tipo_id')
      ->select([
        'c.codigo_proyecto',
        'c.tipo_proyecto',
        'c.titulo',
        'd.nombre AS condicion',
        'c.periodo',
        'c.estado'
      ])
      ->where('a.id', '=', $request->query('grupo_integrante_id'))
      ->where('c.estado', '=', 1)
      ->groupBy('c.id')
      ->get();

    return ['informacion' => $informacion, 'proyectos' => $proyectos];
  }

  public function cambiarCondicion(Request $request) {
    DB::table('Grupo_integrante')
      ->where('id', '=', $request->input('grupo_integrante_id'))
      ->update([
        'condicion' => $request->input('condicion')["value"]
      ]);

    return [
      'message' => 'info',
      'detail' => 'Condición actualizada exitosamente'
    ];
  }

  public function cambiarCargo(Request $request) {
    DB::table('Grupo_integrante')
      ->where('id', '=', $request->input('grupo_integrante_id'))
      ->update([
        'cargo' => $request->input('cargo')["value"] == "" ? null : $request->input('cargo')["value"]
      ]);

    return [
      'message' => 'info',
      'detail' => 'Cargo actualizado exitosamente'
    ];
  }

  public function editarMiembroData(Request $request) {
    $id = $request->query('id');

    $tipo = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select('a.condicion', 'b.tipo')
      ->where('a.id', '=', $id)
      ->first();

    switch ($tipo->condicion) {
      case "Titular":
        $miembro = DB::table('Grupo_integrante AS a')
          ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
          ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
          ->leftJoin('Dependencia AS d', 'd.id', '=', 'b.dependencia_id')
          ->leftJoin('View_puntaje_investigador AS f', 'f.investigador_id', '=', 'a.investigador_id')
          ->select([
            'b.id AS investigador_id',
            DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
            'b.codigo',
            'b.doc_numero',
            DB::raw("CASE
          WHEN SUBSTRING_INDEX(b.docente_categoria, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(b.docente_categoria, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(b.docente_categoria, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(b.docente_categoria, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
          END AS categoria"),
            DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(b.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(b.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(b.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
          END AS clase"),
            DB::raw("SUBSTRING_INDEX(b.docente_categoria, '-', -1) AS horas"),
            'c.nombre AS facultad',
            'd.dependencia',
            'f.puntaje_total',
            'f.puntaje_7_años',
            'b.cti_vitae',
            'b.dep_academico',
            'b.especialidad',
            'b.titulo_profesional',
            'b.grado',
            'b.instituto_id AS instituto',
            'b.codigo_orcid',
            'b.email3',
            'b.telefono_casa',
            'b.telefono_trabajo',
            'b.telefono_movil',
            'b.google_scholar',
            'a.fecha_inclusion',
            'a.resolucion',
            'a.resolucion_fecha',
            'a.observacion'
          ])
          ->where('a.id', '=', $id)
          ->first();

        $institutos = DB::table('Instituto')
          ->select([
            'id AS value',
            'instituto AS label'
          ])
          ->where('estado', '=', 1)
          ->get();

        return ['detalle' => $miembro, 'institutos' => $institutos];
        break;
      case "Adherente":
        if ($tipo->tipo == "Externo") {
          $miembro = DB::table('Grupo_integrante AS a')
            ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
            ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
            ->leftJoin('Dependencia AS d', 'd.id', '=', 'b.dependencia_id')
            ->leftJoin('Instituto AS e', 'e.id', '=', 'b.instituto_id')
            ->select([
              'b.codigo_orcid',
              'b.apellido1',
              'b.apellido2',
              'b.nombres',
              'b.sexo',
              'b.institucion',
              'b.pais',
              'b.email1',
              'b.doc_tipo',
              'b.doc_numero',
              'b.telefono_movil',
              'b.titulo_profesional',
              'b.grado',
              'b.especialidad',
              'b.researcher_id',
              'b.scopus_id',
              'b.link',
              'b.posicion_unmsm',
              'b.biografia',


              'b.codigo',
              DB::raw("CASE
            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
            ELSE 'Sin clase'
            END AS clase"),
              DB::raw("SUBSTRING_INDEX(docente_categoria, '-', -1) AS horas"),
              'c.nombre AS facultad',
              'd.dependencia',
              'b.cti_vitae',
              'b.dep_academico',
              'e.instituto',
              'b.email3',
              'b.telefono_casa',
              'b.telefono_trabajo',

              'b.google_scholar',
              'a.fecha_inclusion',
              'a.resolucion',
              'a.resolucion_fecha',
              'a.observacion'
            ])
            ->where('a.id', '=', $id)
            ->first();

          return $miembro;
        } else {

          $miembro = DB::table('Grupo_integrante AS a')
            ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
            ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
            ->select([
              'b.id AS investigador_id',
              DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
              'b.codigo',
              'c.nombre AS facultad',
              'b.doc_numero',
              'b.email3',
              'b.telefono_movil',
              'b.telefono_casa',
              'b.telefono_trabajo',
            ])
            ->where('a.id', '=', $id)
            ->first();

          return $miembro;
        }
        break;
      default:
        return [];
        break;
    }
  }

  public function editarMiembro(Request $request) {
    switch ($request->input('tipo')) {
      case "titular":
        DB::table('Usuario_investigador')
          ->where('id', '=', $request->input('investigador_id'))
          ->update([
            'cti_vitae' => $request->input('cti_vitae'),
            'dep_academico' => $request->input('dep_academico'),
            'especialidad' => $request->input('especialidad'),
            'titulo_profesional' => $request->input('titulo_profesional'),
            'grado' => $request->input('grado'),
            'instituto_id' => $request->input('instituto'),
            'codigo_orcid' => $request->input('codigo_orcid'),
            'email3' => $request->input('email3'),
            'telefono_casa' => $request->input('telefono_casa'),
            'telefono_trabajo' => $request->input('telefono_trabajo'),
            'telefono_movil' => $request->input('telefono_movil'),
            'google_scholar' => $request->input('google_scholar'),
            'updated_at' => Carbon::now()
          ]);

        DB::table('Grupo_integrante')
          ->where('id', '=', $request->input('id'))
          ->update([
            'fecha_inclusion' => $request->input('fecha_inclusion'),
            'resolucion' => $request->input('resolucion'),
            'resolucion_fecha' => $request->input('resolucion_fecha'),
            'observacion' => $request->input('observacion'),
            'updated_at' => Carbon::now()
          ]);

        return ['message' => 'info', 'detail' => 'Titular actualizado correctamente'];
      case "interno":
        DB::table('Usuario_investigador')
          ->where('id', '=', $request->input('investigador_id'))
          ->update([
            'doc_numero' => $request->input('doc_numero'),
            'email3' => $request->input('email3'),
            'telefono_movil' => $request->input('telefono_movil'),
            'telefono_casa' => $request->input('telefono_casa'),
            'telefono_trabajo' => $request->input('telefono_trabajo'),
            'updated_at' => Carbon::now()
          ]);

        return ['message' => 'info', 'detail' => 'Adherente actualizado correctamente'];
    }
  }

  //  ARCHIVO EXTERNO SE GUARDA EN GRUPO
  //  TODO - implementar reporte de calificación e imprimir
  public function reporte(Request $request) {
    $grupo = DB::table('Grupo')
      ->select([
        'grupo_nombre',
        'grupo_nombre_corto',
        'telefono',
        'anexo',
        'oficina',
        'direccion',
        'web',
        'email',
        'presentacion',
        'objetivos',
        'servicios',
        'infraestructura_ambientes',
        DB::raw("CASE 
            WHEN infraestructura_sgestion IS NULL THEN 'No'
            ELSE 'Sí'
          END AS anexo"),
        DB::raw("CASE (estado)
          WHEN -2 THEN 'Disuelto'
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 4 THEN 'Registrado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado")
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $integrantes = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'b.doc_numero',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        DB::raw("CASE 
          WHEN a.cargo IS NOT NULL THEN CONCAT(a.condicion, '(', a.cargo, ')')
          ELSE a.condicion
          END AS condicion"),
        'b.tipo',
        'c.nombre AS facultad'
      ])
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.nombre',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $laboratorios = DB::table('Grupo_infraestructura AS a')
      ->join('Laboratorio AS b', 'b.id', '=', 'a.laboratorio_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.laboratorio',
        'b.responsable',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.grupo.reporte_grupo', [
      'grupo' => $grupo,
      'integrantes' => $integrantes,
      'lineas' => $lineas,
      'laboratorios' => $laboratorios,
    ]);

    return $pdf->stream();
  }
}

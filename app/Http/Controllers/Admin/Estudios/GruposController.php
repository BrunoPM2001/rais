<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
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
        'a.estado'
      )
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->where('a.tipo', '=', 'grupo')
      ->groupBy('a.id')
      ->get();

    return ['data' => $grupos];
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
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.tipo', '=', 'solicitud')
      ->havingBetween('a.estado', [0, 6])
      ->groupBy('a.id')
      ->get();

    return ['data' => $solicitudes];
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
        'a.infraestructura_ambientes',
        'a.infraestructura_sgestion'
      )
      ->where('a.id', '=', $grupo_id)
      ->first();


    if ($detalle->infraestructura_sgestion != null) {
      $cmd = $s3->getCommand('GetObject', [
        'Bucket' => 'grupo-infraestructura-sgestion',
        'Key' => $detalle->infraestructura_sgestion
      ]);
      //  Generar url temporal
      $url = (string) $s3->createPresignedRequest($cmd, '+10 minutes')->getUri();
    }
    $detalle->infraestructura_sgestion = $url;

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
      ->get();

    return ['data' => $miembros];
  }

  public function docs($grupo_id) {
    $docs = DB::table('Grupo_integrante_doc')
      ->select(
        'id',
        'nombre',
        'archivo_tipo',
        'fecha'
      )
      ->where('grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $docs];
  }

  public function lineas($grupo_id) {
    $lineas = DB::table('Grupo_linea AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->select(
        'a.id',
        'c.codigo',
        'c.nombre'
      )
      ->whereNull('a.concytec_codigo')
      ->where('a.grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $lineas];
  }

  public function proyectos($grupo_id) {
    //  TODO - Averiguar los criterios para colocar un proyecto como válido
    $proyectos = DB::table('Proyecto')
      ->select(
        'id',
        'titulo',
        'periodo',
        'tipo_proyecto'
      )
      ->where('grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $proyectos];
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
    switch ($request->input('tipo_registro')) {
      case 'titular':
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
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);

        return [
          'message' => 'success',
          'detail' => 'Miembro registrado exitosamente'
        ];
        break;
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
          ->insert([
            'grupo_id' => $request->input('grupo_id'),
            'investigador_id' => $investigador_id,
            'tipo' => 'Externo',
            'condicion' => 'Adherente',
            'observacion' => $request->input('observacion'),
            'fecha_inclusion' => Carbon::now(),
            'estado' => '1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);

        return [
          'message' => 'success',
          'detail' => 'Miembro registrado exitosamente'
        ];
        break;
      case 'estudiante' || 'egresado':
        $day = Carbon::now()->day;
        DB::table('Grupo_integrante')
          ->insert([
            'grupo_id' => $request->input('grupo_id'),
            'investigador_id' => $request->input('investigador_id'),
            'condicion' => $request->input('condicion'),
            'fecha_inclusion' => Carbon::now(),
            'estado' => '1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);

        return [
          'message' => 'success',
          'detail' => 'Miembro registrado exitosamente',
          'extra' => $day
        ];
        break;
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
  //  ARCHIVO EXTERNO SE GUARDA EN GRUPO
  //  TODO - implementar reporte de calificación e imprimir
}

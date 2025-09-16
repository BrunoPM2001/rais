<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoFEXController extends S3Controller {

  public function listado(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Proyecto_integrante_tipo AS c', 'b.proyecto_integrante_tipo_id', '=', 'c.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'a.tipo_proyecto',
        'c.nombre AS condicion',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 3 THEN 'En evaluacion'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecución'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluído'
        ELSE 'Sin estado' END AS estado"),
        'a.periodo',
        DB::raw("'no' AS antiguo")
      )
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_proyecto', ['PFEX'])
      ->whereNotIn('a.estado', [-1])
      ->orderByDesc('a.periodo')
      ->get();

    $proyectos_antiguos = DB::table('Proyecto_H AS a')
      ->leftJoin('Proyecto_integrante_H AS b', 'b.proyecto_id', '=', 'a.id')
      ->select([
        'a.id',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.tipo AS tipo_proyecto',
        'b.condicion',
        DB::raw("CASE(a.status)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 3 THEN 'En evaluacion'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecución'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluído'
        ELSE 'Sin estado' END AS estado"),
        'a.periodo',
        DB::raw("'si' AS antiguo")
      ])
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.tipo', '=', 'SIN-CON')
      ->whereNotIn('a.status', [-1])
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos->merge($proyectos_antiguos);
  }

  public function lineasUnmsm() {
    $lineas = DB::table('Linea_investigacion')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', nombre) AS label")
      ])
      ->where('estado', '=', 1)
      ->orderBy('codigo')
      ->get();

    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label"),
        'parent_id'
      ])
      ->get();

    $paises = DB::table('Pais')
      ->select([
        'code AS value',
        'name AS label'
      ])->get();

    return [
      'lineas' => $lineas,
      'ocde' => $ocde,
      'paises' => $paises,
    ];
  }

  public function datosPaso1(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        'titulo',
        'periodo',
        'linea_investigacion_id',
        'ocde_id',
        'aporte_unmsm',
        'aporte_no_unmsm',
        'financiamiento_fuente_externa',
        'entidad_asociada',
        'resolucion_rectoral',
        'estado',
        'observaciones_admin',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $extras = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', ['moneda_tipo', 'fuente_financiadora', 'otra_fuente', 'web_fuente', 'participacion_unmsm', 'pais'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $lineas = $this->lineasUnmsm();

    return [
      'proyecto' => $proyecto,
      'extras' => $extras
    ] + $lineas;
  }

  public function datosPaso2(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        DB::raw("COALESCE(fecha_inicio, '') AS fecha_inicio"),
        DB::raw("COALESCE(fecha_fin, '') AS fecha_fin"),
        DB::raw("COALESCE(palabras_clave, '') AS palabras_clave"),
        'estado',
        'observaciones_admin',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $extras = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', ['resumen', 'objetivos', 'duracion_anio', 'duracion_mes', 'duracion_dia'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    return [
      'proyecto' => $proyecto,
      'extras' => $extras
    ];
  }

  public function datosPaso3(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        'estado',
        'observaciones_admin'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $documentos = DB::table('Proyecto_fex_doc AS a')
      ->select([
        'id',
        'doc_tipo',
        'nombre',
        'comentario',
        'fecha',
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    return [
      'proyecto' => $proyecto,
      'documentos' => $documentos
    ];
  }

  public function datosPaso4(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        'estado',
        'observaciones_admin'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->join('Proyecto_integrante_tipo AS d', 'd.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'a.id',
        DB::raw("CASE
          WHEN a.responsabilidad IN ('', 'null') OR a.responsabilidad IS NULL THEN d.nombre
          ELSE a.responsabilidad
        END AS tipo_integrante"),
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        'b.doc_numero',
        DB::raw("IF(a.condicion = 'Responsable', 'Sí', 'No') AS responsable"),
        DB::raw("CASE
          WHEN b.tipo = 'EXTERNO' THEN 'Externo'
          ELSE c.nombre
        END AS facultad")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return [
      'proyecto' => $proyecto,
      'integrantes' => $integrantes
    ];
  }

  public function datosPaso5(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        'estado',
        'observaciones_admin',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    return $proyecto;
  }

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();
    $id = 0;
    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($request->input('id')) {
      $id = $request->input('id');

      if ($this->validar($id, $investigador_id)) {
        return response()->json(['error' => 'Unauthorized'], 401);
      }

      $responsable = DB::table('Usuario_investigador')
        ->select([
          'facultad_id'
        ])
        ->where('id', '=', $investigador_id)
        ->first();

      DB::table('Proyecto')
        ->where('id', '=', $id)
        ->update([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"] ?? null,
          'facultad_id' => $responsable->facultad_id,
          'ocde_id' => $request->input('ocde_3')["value"] ?? null,
          'titulo' => $request->input('titulo'),
          'periodo' => $request->input('periodo'),
          'resolucion_rectoral' => $request->input('resolucion_rectoral'),
          'aporte_unmsm' => $request->input('aporte_unmsm'),
          'aporte_no_unmsm' => $request->input('aporte_no_unmsm'),
          'financiamiento_fuente_externa' => $request->input('financiamiento_fuente_externa'),
          'entidad_asociada' => $request->input('entidad_asociada'),
          'monto_asignado' =>
          $request->input('aporte_unmsm')
            + $request->input('aporte_no_unmsm')
            + $request->input('financiamiento_fuente_externa')
            + $request->input('entidad_asociada'),
          'step' => 2,
          'updated_at' => $date,
        ]);
    } else {
      $responsable = DB::table('Usuario_investigador')
        ->select([
          'facultad_id'
        ])
        ->where('id', '=', $investigador_id)
        ->first();

      $id = DB::table('Proyecto')
        ->insertGetId([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"] ?? null,
          'facultad_id' => $responsable->facultad_id,
          'ocde_id' => $request->input('ocde_3')["value"] ?? null,
          'titulo' => $request->input('titulo'),
          'periodo' => $request->input('periodo'),
          'fecha_inscripcion' => $date,
          'tipo_proyecto' => 'PFEX',
          'resolucion_rectoral' => $request->input('resolucion_rectoral'),
          'aporte_unmsm' => $request->input('aporte_unmsm'),
          'aporte_no_unmsm' => $request->input('aporte_no_unmsm'),
          'financiamiento_fuente_externa' => $request->input('financiamiento_fuente_externa'),
          'entidad_asociada' => $request->input('entidad_asociada'),
          'monto_asignado' =>
          $request->input('aporte_unmsm')
            + $request->input('aporte_no_unmsm')
            + $request->input('financiamiento_fuente_externa')
            + $request->input('entidad_asociada'),
          'estado' => 6,
          'step' => 2,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $investigador_id,
          'proyecto_integrante_tipo_id' => 44,
          'condicion' => 'Responsable',
          'created_at' => $date,
          'updated_at' => $date,
        ]);
    }

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'moneda_tipo'
      ], [
        'detalle' => $request->input('moneda_tipo')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'fuente_financiadora'
      ], [
        'detalle' => $request->input('fuente_financiadora')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'otra_fuente'
      ], [
        'detalle' => $request->input('otra_fuente') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'web_fuente'
      ], [
        'detalle' => $request->input('web_fuente') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'participacion_unmsm'
      ], [
        'detalle' => $request->input('participacion_unmsm')["value"] ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $id,
        'codigo' => 'pais'
      ], [
        'detalle' => $request->input('pais')["value"] ?? ""
      ]);

    return ['message' => 'success', 'id' => $id];
  }

  public function registrarPaso2(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $date = Carbon::now();
    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'updated_at' => $date
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'resumen'
      ], [
        'detalle' => $request->input('resumen')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'objetivos'
      ], [
        'detalle' => $request->input('objetivos')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_anio'
      ], [
        'detalle' => $request->input('años') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_mes'
      ], [
        'detalle' => $request->input('meses') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_dia'
      ], [
        'detalle' => $request->input('dias') ?? ""
      ]);
  }

  //  Paso 3
  public function registrarPaso3(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $date = Carbon::now();
    $date_name = $date->format('Ymd-His');

    if ($request->hasFile('file')) {

      $nameFile = $request->input('id') . "/" . $request->input('doc_tipo') . "_" . $date_name . "." . $request->file('file')->getClientOriginalExtension();

      $this->uploadFile($request->file('file'), "proyecto-fex-doc", $nameFile);

      DB::table('Proyecto_fex_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'doc_tipo' => $request->input('doc_tipo'),
          'nombre' => $request->input('nombre'),
          'comentario' => $request->input('comentario'),
          'bucket' => 'proyecto-fex-doc',
          'key' => $nameFile,
          'fecha' => $date
        ]);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function updateDoc(Request $request) {
    DB::table('Proyecto_fex_doc')
      ->where([
        'id' => $request->input('id')
      ])
      ->update([
        'nombre' => $request->input('nombre'),
        'comentario' => $request->input('comentario')
      ]);

    return ['message' => 'info', 'detail' => 'Datos actualizados correctamente'];
  }

  public function deleteDoc(Request $request) {
    DB::table('Proyecto_fex_doc')
      ->where([
        'id' => $request->query('id')
      ])
      ->delete();

    return ['message' => 'info', 'detail' => 'Archivo eliminado correctamente'];
  }

  public function searchDocente(Request $request) {
    $investigadores = DB::table('Repo_rrhh AS a')
      ->join('Usuario_investigador AS b', 'b.doc_numero', '=', 'a.ser_doc_id_act')
      ->leftJoin('Licencia AS c', 'c.investigador_id', '=', 'b.id')
      ->leftJoin('Licencia_tipo AS d', 'c.licencia_tipo_id', '=', 'd.id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(TRIM(a.ser_cod_ant), ' | ', a.ser_doc_id_act, ' | ', a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS value"),
        'a.id',
        'b.id AS investigador_id',
        DB::raw("CONCAT(a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS nombres"),
        'ser_cod_ant AS codigo',
        'ser_doc_id_act AS doc_numero',
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

  public function agregarDocente(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('proyecto_id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $cuenta = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('investigador_id', '=', $request->input('investigador_id'))
      ->count();

    if ($cuenta > 0) {
      return ['message' => 'error', 'detail' => 'Esta persona ya figura como integrante del proyecto'];
    }

    DB::table('Proyecto_integrante')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'investigador_id' => $request->input('investigador_id'),
        'proyecto_integrante_tipo_id' => $request->input('condicion'),
        'responsabilidad' => $request->input('responsabilidad'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Integrante añadido correctamente'];
  }

  public function searchEstudiante(Request $request) {
    $estudiantes = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        DB::raw("CONCAT(a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres) AS nombres"),
        'a.codigo_alumno',
        'a.dni',
        'a.facultad',
        'a.permanencia',
        'a.programa',
        'a.ultimo_periodo_matriculado',
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula', 'Egresado'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.codigo_alumno')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function agregarEstudiante(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('proyecto_id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    if ($request->input('investigador_id') == null) {
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
          'doc_numero' => $sumData->dni,
          'sexo' => $sumData->sexo,
          'email3' => $sumData->correo_electronico,
          'tipo' => $request->input('tipo'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
          'tipo_investigador' => 'Estudiante'
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $id_investigador,
          'proyecto_integrante_tipo_id' => $request->input('condicion'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    } else {
      $cuenta = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('investigador_id', '=', $request->input('investigador_id'))
        ->count();

      if ($cuenta > 0) {
        return ['message' => 'error', 'detail' => 'Esta persona ya figura como integrante del proyecto'];
      }

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $request->input('investigador_id'),
          'proyecto_integrante_tipo_id' => $request->input('condicion'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    return ['message' => 'success', 'detail' => 'Integrante añadido correctamente'];
  }

  public function searchExterno(Request $request) {
    $investigadores = DB::table('Usuario_investigador AS a')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', apellido1, ' ', apellido2, ', ', nombres) AS value"),
        'id AS investigador_id',
        'doc_numero',
        'apellido1',
        'apellido2',
        'nombres'
      )
      ->where('tipo', '=', 'EXTERNO')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function agregarExterno(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('proyecto_id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    if ($request->input('tipo') == "Nuevo") {

      $investigador_id = DB::table('Usuario_investigador')
        ->insertGetId([
          'codigo_orcid' => $request->input('codigo_orcid'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'nombres' => $request->input('nombres'),
          'sexo' => $request->input('sexo'),
          'institucion' => $request->input('institucion'),
          'tipo' => 'Externo',
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
          'cti_vitae' => $request->input('cti_vitae'),
          'google_scholar' => $request->input('google_scholar'),
          'link' => $request->input('link'),
          'posicion_unmsm' => $request->input('posicion_unmsm'),
          'biografia' => $request->input('biografia'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $investigador_id,
          'proyecto_integrante_tipo_id' => $request->input('condicion'),
          'responsabilidad' => $request->input('responsabilidad'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    } else {

      $cuenta = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('investigador_id', '=', $request->input('investigador_id'))
        ->count();

      if ($cuenta > 0) {
        return ['message' => 'error', 'detail' => 'Esta persona ya figura como integrante del proyecto'];
      }

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $request->input('investigador_id'),
          'proyecto_integrante_tipo_id' => $request->input('condicion'),
          'responsabilidad' => $request->input('responsabilidad'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    return ['message' => 'success', 'detail' => 'Integrante añadido correctamente'];
  }

  public function eliminarMiembro(Request $request) {
    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Integrante eliminado'];
  }

  public function enviar(Request $request) {

    $investigador_id = $request->attributes->get('token_decoded')->investigador_id;

    if ($this->validar($request->input('id'), $investigador_id)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'estado' => 5
      ]);

    return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
  }

  public function validar($proyecto_id, $investigador_id) {
    $cuenta = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $proyecto_id)
      ->where('investigador_id', '=', $investigador_id)
      ->count();

    return $cuenta > 0 ? false : true;
  }
}

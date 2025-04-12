<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\Admin\FromDataExport;
use Maatwebsite\Excel\Facades\Excel;

class ProyectosFEXController extends S3Controller {
  public function listado() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $moneda = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'moneda_tipo');

    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($moneda, 'moneda', 'moneda.proyecto_id', '=', 'a.id')
      ->leftJoin('Proyecto_descripcion AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.id')
          ->where('e.codigo', '=', 'participacion_unmsm');
      })
      ->leftJoin('Proyecto_descripcion AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'a.id')
          ->where('f.codigo', '=', 'fuente_financiadora');
      })
      ->leftJoin('Proyecto_descripcion AS g', function (JoinClause $join) {
        $join->on('g.proyecto_id', '=', 'a.id')
          ->where('g.codigo', '=', 'otra_fuente');
      })
      ->leftJoin('Proyecto_descripcion AS h', function (JoinClause $join) {
        $join->on('h.proyecto_id', '=', 'a.id')
          ->where('h.codigo', '=', 'pais');
      })
      ->leftJoin('Pais AS p', 'p.code', '=', 'h.detalle')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'res.responsable',
        'b.nombre AS facultad',
        'moneda.detalle AS moneda',
        'a.aporte_no_unmsm',
        'a.aporte_unmsm',
        'a.financiamiento_fuente_externa',
        'a.monto_asignado',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.resolucion_rectoral',
        'a.resolucion_fecha',
        'a.entidad_asociada',
        'p.name as pais',
        'e.detalle AS participacion_unmsm',
        DB::raw("CASE
          WHEN f.detalle = 'OTROS' THEN g.detalle
          ELSE f.detalle
        END AS fuente_fin"),
        'a.periodo',
        DB::raw('DATE(a.created_at) AS registrado'),
        DB::raw('DATE(a.updated_at) AS actualizado'),
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
      )
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->groupBy('a.id')
      ->get();

    return $proyectos;
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
        'resolucion_rectoral'
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

    return $documentos;
  }

  public function datosPaso4(Request $request) {
    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.doc_numero',
        'd.nombre AS facultad'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return $integrantes;
  }

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();
    $id = 0;

    if ($request->input('id')) {
      $id = $request->input('id');
      DB::table('Proyecto')
        ->where('id', '=', $id)
        ->update([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"] ?? null,
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
      $id = DB::table('Proyecto')
        ->insertGetId([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"] ?? null,
          'ocde_id' => $request->input('ocde_3')["value"] ?? null,
          'titulo' => $request->input('titulo'),
          'periodo' => $request->input('periodo'),
          'fecha_inscripcion' => $date,
          'tipo_proyecto' => 'PFEX',
          'periodo' => $date->format("Y"),
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

  public function getInstitutos() {
    $institutos = DB::table('Instituto')
      ->select([
        'id AS value',
        'instituto AS label'
      ])
      ->where('estado', '=', 1)
      ->get();

    return $institutos;
  }

  public function searchDocenteRrhh(Request $request) {
    $investigadores = DB::table('Repo_rrhh AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.doc_numero', '=', 'a.ser_doc_id_act')
      ->leftJoin('Licencia AS c', 'c.investigador_id', '=', 'b.id')
      ->leftJoin('Licencia_tipo AS d', 'c.licencia_tipo_id', '=', 'd.id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(TRIM(a.ser_cod_ant), ' | ', a.ser_doc_id_act, ' | ', a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS value"),
        'a.id',
        'a.ser_ape_pat AS apellido1',
        'a.ser_ape_mat AS apellido2',
        'a.ser_nom AS nombre',
        'b.id AS investigador_id',
        DB::raw("CONCAT(a.ser_ape_pat, ' ', a.ser_ape_mat, ', ', a.ser_nom) AS nombres"),
        'a.ser_cod_ant AS codigo',
        'a.ser_doc_id_act AS doc_numero',
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
        'a.des_dep_cesantes AS dependencia',
        'a.ser_cod_dep_ces AS dependencia_id',
        'e.nombre AS facultad',
        'e.id AS facultad_id',
        'b.cti_vitae',
        'b.especialidad',
        'b.titulo_profesional',
        'b.grado',
        'b.instituto_id',
        'b.codigo_orcid',
        'b.email3',
        'b.telefono_casa',
        'b.telefono_trabajo',
        'b.telefono_movil',
        DB::raw("SUBSTRING_INDEX(ser_cat_act, '-', -1) AS horas"),
      )
      ->where('a.des_tip_ser', 'LIKE', 'DOCENTE%')
      ->where('b.tipo', 'LIKE', '%DOCENTE%')
      ->where(function ($query) {
        $query->where('c.fecha_fin', '<', date('Y-m-d'))
          ->orWhere('d.id', '=', 9)
          ->orWhereNull('d.tipo');
      })
      ->groupBy('a.ser_cod_ant')
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
        'a.apellido_paterno AS apellido1',
        'a.apellido_materno AS apellido2',
        'a.nombres',
        'a.codigo_alumno AS codigo',
        'a.facultad',
        'a.id_facultad AS facultad_id',
        'a.programa',
        'a.permanencia',
        'a.ultimo_periodo_matriculado',

        'a.dni AS doc_numero',
        DB::raw("COALESCE(b.email3, a.correo_electronico) AS email3"),
        'b.telefono_casa',
        'b.telefono_trabajo',
        'b.telefono_movil',
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.codigo_alumno')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function agregarDocente(Request $request) {
    $investigador_id = $request->input('investigador_id');

    if (!$investigador_id) {
      $investigador_id = DB::table('Usuario_investigador')
        ->insertGetId([
          'codigo' => $request->input('codigo'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'nombres' => $request->input('nombre'),
          'doc_numero' => $request->input('doc_numero'),
          'dependencia_id' => $request->input('dependencia_id'),
          'facultad_id' => $request->input('facultad_id'),
          'cti_vitae' => $request->input('cti_vitae'),
          'especialidad' => $request->input('especialidad'),
          'titulo_profesional' => $request->input('titulo_profesional'),
          'grado' => $request->input('grado')["value"],
          'instituto_id' => $request->input('instituto')["value"],
          'codigo_orcid' => $request->input('codigo_orcid'),
          'email3' => $request->input('email3'),
          'telefono_casa' => $request->input('telefono_casa'),
          'telefono_trabajo' => $request->input('telefono_trabajo'),
          'telefono_movil' => $request->input('telefono_movil'),
        ]);
    } else {
      DB::table('Usuario_investigador')
        ->where('id', '=', $investigador_id)
        ->update([
          'codigo' => $request->input('codigo'),
          'cti_vitae' => $request->input('cti_vitae'),
          'especialidad' => $request->input('especialidad'),
          'titulo_profesional' => $request->input('titulo_profesional'),
          'grado' => $request->input('grado')["value"],
          'instituto_id' => $request->input('instituto')["value"],
          'codigo_orcid' => $request->input('codigo_orcid'),
          'email3' => $request->input('email3'),
          'telefono_casa' => $request->input('telefono_casa'),
          'telefono_trabajo' => $request->input('telefono_trabajo'),
          'telefono_movil' => $request->input('telefono_movil'),
        ]);

      $count = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('investigador_id', '=', $investigador_id)
        ->count();

      if ($count > 0) {
        return ['message' => 'warning', 'detail' => 'No puede agregar al mismo docente 2 veces'];
      }
    }


    DB::table('Proyecto_integrante')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'investigador_id' => $investigador_id,
        'proyecto_integrante_tipo_id' => $request->input('condicion')["value"],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Integrante añadido exitosamente'];
  }

  public function getEditDocente(Request $request) {
    $docente = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.investigador_id',
        'a.proyecto_integrante_tipo_id',
        'b.codigo',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_numero',
        'b.cti_vitae',
        'b.especialidad',
        'b.titulo_profesional',
        'b.grado',
        'b.instituto_id',
        'b.codigo_orcid',
        'b.email3',
        'b.telefono_casa',
        'b.telefono_trabajo',
        'b.telefono_movil',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $institutos = DB::table('Instituto')
      ->select([
        'id AS value',
        'instituto AS label'
      ])
      ->where('estado', '=', 1)
      ->get();

    return ['docente' => $docente, 'institutos' => $institutos];
  }

  public function editarDocente(Request $request) {
    $investigador_id = $request->input('investigador_id');

    DB::table('Usuario_investigador')
      ->where('id', '=', $investigador_id)
      ->update([
        'codigo' => $request->input('codigo'),
        'cti_vitae' => $request->input('cti_vitae'),
        'especialidad' => $request->input('especialidad'),
        'titulo_profesional' => $request->input('titulo_profesional'),
        'grado' => $request->input('grado')["value"],
        'instituto_id' => $request->input('instituto')["value"],
        'codigo_orcid' => $request->input('codigo_orcid'),
        'email3' => $request->input('email3'),
        'telefono_casa' => $request->input('telefono_casa'),
        'telefono_trabajo' => $request->input('telefono_trabajo'),
        'telefono_movil' => $request->input('telefono_movil'),
      ]);


    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->input('id'))
      ->update([
        'proyecto_integrante_tipo_id' => $request->input('condicion')["value"],
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'Información editada exitosamente'];
  }

  public function agregarEstudiante(Request $request) {
    $investigador_id = $request->input('investigador_id');

    if (!$investigador_id) {
      $investigador_id = DB::table('Usuario_investigador')
        ->insertGetId([
          'codigo' => $request->input('codigo'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'nombres' => $request->input('nombres'),
          'doc_numero' => $request->input('doc_numero'),
          'facultad_id' => $request->input('facultad_id'),
          'email3' => $request->input('email3'),
          'telefono_casa' => $request->input('telefono_casa'),
          'telefono_trabajo' => $request->input('telefono_trabajo'),
          'telefono_movil' => $request->input('telefono_movil'),
        ]);
    } else {
      DB::table('Usuario_investigador')
        ->where('id', '=', $investigador_id)
        ->update([
          'doc_numero' => $request->input('doc_numero'),
          'codigo' => $request->input('codigo'),
          'email3' => $request->input('email3'),
          'telefono_casa' => $request->input('telefono_casa'),
          'telefono_trabajo' => $request->input('telefono_trabajo'),
          'telefono_movil' => $request->input('telefono_movil'),
        ]);
    }

    DB::table('Proyecto_integrante')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'investigador_id' => $investigador_id,
        'proyecto_integrante_tipo_id' => 47,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Integrante añadido exitosamente'];
  }

  public function getEditEstudiante(Request $request) {
    $estudiante = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.investigador_id',
        'b.codigo',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_numero',
        'b.email3',
        'b.telefono_casa',
        'b.telefono_trabajo',
        'b.telefono_movil',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $estudiante;
  }

  public function editarEstudiante(Request $request) {
    $investigador_id = $request->input('investigador_id');
    DB::table('Usuario_investigador')
      ->where('id', '=', $investigador_id)
      ->update([
        'doc_numero' => $request->input('doc_numero'),
        'codigo' => $request->input('codigo'),
        'email3' => $request->input('email3'),
        'telefono_casa' => $request->input('telefono_casa'),
        'telefono_trabajo' => $request->input('telefono_trabajo'),
        'telefono_movil' => $request->input('telefono_movil'),
      ]);

    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->input('id'))
      ->update([
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'info', 'detail' => 'Información editada exitosamente'];
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
          'link' => $request->input('link'),
          'posicion_unmsm' => $request->input('posicion_unmsm'),
          'biografia' => $request->input('biografia'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'investigador_id' => $investigador_id,
          'proyecto_integrante_tipo_id' => 90,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    } else {

      $cuenta = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('investigador_id', '=', $request->input('investigador_id'))
        ->count();

      if ($cuenta > 0) {
        return ['message' => 'error', 'detail' => 'Esta persona ya figura como integrante del proyecto'];
      }

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'investigador_id' => $request->input('investigador_id'),
          'proyecto_integrante_tipo_id' => 90,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    return ['message' => 'success', 'detail' => 'Integrante añadido correctamente'];
  }

  public function getEditExterno(Request $request) {
    $externo = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.investigador_id',
        'b.codigo_orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.sexo',
        'b.institucion',
        'b.tipo',
        'b.pais',
        'b.direccion1',
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
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $paises = DB::table('Pais')
      ->select([
        'name AS value'
      ])->get();

    return ['externo' => $externo, 'paises' => $paises];
  }

  public function editarExterno(Request $request) {
    DB::table('Usuario_investigador')
      ->where('id', '=', $request->input('investigador_id'))
      ->update([
        'codigo_orcid' => $request->input('codigo_orcid'),
        'apellido1' => $request->input('apellido1'),
        'apellido2' => $request->input('apellido2'),
        'nombres' => $request->input('nombres'),
        'sexo' => $request->input('sexo')["value"],
        'institucion' => $request->input('institucion'),
        'pais' => $request->input('pais')["value"],
        'direccion1' => $request->input('direccion1'),
        'doc_tipo' => $request->input('doc_tipo')["value"],
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
        'updated_at' => Carbon::now(),
      ]);

    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->input('id'))
      ->update([
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Información editada correctamente'];
  }

  public function eliminarMiembro(Request $request) {
    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Integrante eliminado exitosamente'];
  }

  public function detalle(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->select([
        'a.codigo_proyecto',
        'a.estado',
        'a.resolucion_rectoral',
        'a.resolucion_fecha',
        'a.comentario',
        'a.observaciones_admin'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $proyecto;
  }

  public function pasos(Request $request) {
    $paso1 = $this->datosPaso1($request);
    $paso2 = $this->datosPaso2($request);
    $paso3 = $this->datosPaso3($request);
    $paso4 = $this->datosPaso4($request);

    return [
      'paso1' => $paso1,
      'paso2' => $paso2,
      'paso3' => $paso3,
      'paso4' => $paso4,
    ];
  }

  public function updateDetalle(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'estado' => $request->input('estado'),
        'resolucion_rectoral' => $request->input('resolucion_rectoral'),
        'resolucion_fecha' => $request->input('resolucion_fecha'),
        'comentario' => $request->input('comentario'),
        'observaciones_admin' => $request->input('observaciones_admin'),
      ]);

    return ['message' => 'info', 'detail' => 'Información del proyecto actualizada'];
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_descripcion AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.codigo', '=', 'pais');
      })
      ->leftJoin('Pais AS e', 'e.code', '=', 'd.detalle')
      ->select([
        'a.codigo_proyecto',
        'a.titulo',
        'a.periodo',
        'b.nombre AS linea_investigacion',
        'c.linea AS linea_ocde',
        DB::raw("a.aporte_unmsm + a.entidad_asociada + a.aporte_no_unmsm + a.financiamiento_fuente_externa AS monto"),
        'a.aporte_unmsm',
        'a.aporte_no_unmsm',
        'a.financiamiento_fuente_externa',
        'a.entidad_asociada',
        'e.name AS pais',
        'a.resolucion_rectoral',
        'a.palabras_clave',
        'a.fecha_inicio',
        'a.fecha_fin',
        DB::raw("CASE(a.estado)
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
        'a.updated_at'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $extras = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $documentos = DB::table('Proyecto_fex_doc AS a')
      ->select([
        'doc_tipo',
        'nombre',
        'comentario',
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        DB::raw("CASE (c.nombre)
          WHEN 'Otros' THEN a.responsabilidad
          ELSE c.nombre
        END AS tipo"),
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.doc_numero',
        DB::raw("CASE(b.tipo)
          WHEN 'Externo' THEN 'No'
        ELSE 'Sí' END AS representa")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.pfex', [
      'proyecto' => $proyecto,
      'extras' => $extras,
      'documentos' => $documentos,
      'integrantes' => $integrantes,
    ]);

    return $pdf->stream();
  }

  public function excel(Request $request) {

    $data = $request->all();

    $export = new FromDataExport($data);

    return Excel::download($export, 'proyectos.xlsx');
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    $participacion_ummsm = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'participacion_ummsm');

    $fuente_financiadora = DB::table('Proyecto_descripcion')
      ->select(
        'proyecto_id',
        'detalle'
      )
      ->where('codigo', '=', 'fuente_financiadora');

    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($moneda, 'moneda', 'moneda.proyecto_id', '=', 'a.id')
      ->leftJoinSub($participacion_ummsm, 'p_unmsm', 'p_unmsm.proyecto_id', '=', 'a.id')
      ->leftJoinSub($fuente_financiadora, 'fuente', 'fuente.proyecto_id', '=', 'a.id')
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
        'p_unmsm.detalle AS participacion_unmsm',
        'fuente.detalle AS fuente_fin',
        'a.periodo',
        DB::raw('DATE(a.created_at) AS registrado'),
        DB::raw('DATE(a.updated_at) AS actualizado'),
        'a.estado'
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
      ->join('Facultad AS d', 'd.id', '=', 'b.facultad_id')
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
        'b.id AS investigador_id',
        DB::raw("CONCAT(a.ser_ape_pat, ' ', a.ser_ape_mat, ', ', a.ser_nom) AS nombres"),
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
        'b.nombre AS linea_investigacion',
        'c.linea AS linea_ocde',
        'a.aporte_unmsm',
        'a.aporte_no_unmsm',
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
      ->join('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        'c.nombre AS tipo',
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
}

<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoDetalleController extends Controller {
  public function detalleProyecto(Request $request) {

    $responsable = DB::table('Proyecto_integrante AS pix')
      ->where('pix.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('pix.condicion', '=', 'Responsable')
      ->where('pix.proyecto_id', '=', $request->query('proyecto_id'))
      ->count();

    if ($request->query('antiguo') == "no") {

      $esIntegrante = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $request->query('proyecto_id'))
        ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->count();

      if ($esIntegrante > 0) {
        $detalles = DB::table('Proyecto AS a')
          ->leftJoin('Proyecto_descripcion AS b', function ($join) {
            $join->on('b.proyecto_id', '=', 'a.id')
              ->where('b.codigo', '=', 'tipo_investigacion');
          })
          ->leftJoin('Proyecto_presupuesto AS c', 'c.proyecto_id', '=', 'a.id')
          ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
          ->leftJoin('Linea_investigacion AS e', 'e.id', '=', 'a.linea_investigacion_id')
          ->leftJoin('Grupo AS f', 'f.id', '=', 'a.grupo_id')
          ->leftJoin('File AS g', function (JoinClause $join) {
            $join->on('g.tabla_id', '=', 'a.id')
              ->where('g.tabla', '=', 'Proyecto')
              ->where('g.recurso', '=', 'DJ_FIRMADA')
              ->where('g.estado', '=', 20);
          })
          ->select([
            'a.id',
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
            'a.tipo_proyecto',
            'a.codigo_proyecto',
            'a.titulo',
            'a.periodo',
            'b.detalle AS tipo_investigacion',
            DB::raw("CASE
              WHEN a.tipo_proyecto = 'PFEX' THEN FORMAT(a.aporte_unmsm + a.entidad_asociada + a.aporte_no_unmsm + a.financiamiento_fuente_externa, 2, 'en_US')
              ELSE SUM(c.monto)
            END AS monto"),
            'd.nombre AS facultad',
            'e.nombre AS linea_investigacion',
            'a.fecha_inscripcion',
            'a.resolucion_rectoral',
            'a.observaciones_admin',
            'f.grupo_nombre',
            'a.dj_aceptada',
            DB::raw("CONCAT('/minio/', g.bucket, '/', g.key) AS url")
          ])
          ->where('a.id', '=', $request->query('proyecto_id'))
          ->first();

        $participantes = DB::table('Proyecto_integrante AS a')
          ->leftJoin('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
          ->select([
            'b.nombre AS condicion',
            'c.codigo',
            DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres")
          ])
          ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
          ->get();

        return [
          'detalles' => $detalles,
          'responsable' => $responsable,
          'participantes' => $participantes
        ];
      } else {
        return response()->json(['error' => 'Unauthorized'], 401);
      }
    } else if ($request->query('antiguo') == "si") {

      $esIntegrante = DB::table('Proyecto_integrante_H')
        ->where('proyecto_id', '=', $request->query('proyecto_id'))
        ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->count();

      if ($esIntegrante > 0) {
        $detalles = DB::table('Proyecto_H')
          ->select([
            'id',
            'tipo AS tipo_proyecto',
            'codigo AS codigo_proyecto',
            'periodo',
            'titulo',
            'status AS estado'
          ])
          ->where('id', '=', $request->query('proyecto_id'))
          ->first();

        $participantes = DB::table('Proyecto_integrante_H AS a')
          ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
          ->select([
            'a.condicion',
            'b.codigo',
            DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres")
          ])
          ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
          ->get();

        return [
          'detalles' => $detalles,
          'responsable' => $responsable,
          'participantes' => $participantes
        ];
      } else {
        return response()->json(['error' => 'Unauthorized'], 401);
      }
    }
  }

  public function reportePresupuesto(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('condicion', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'b.investigador_id', '=', 'c.id')
      ->leftJoin('Facultad AS d', 'a.facultad_id', '=', 'd.id')
      ->select([
        'a.fecha_inscripcion',
        'a.periodo',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("COALESCE(d.nombre, 'No figura') AS facultad"),
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS responsable"),
        'c.email3',
        'c.telefono_movil',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluación'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecucion'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluido'
          ELSE 'Sin estado'
        END AS estado")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $presupuesto = DB::table('Geco_proyecto AS a')
      ->join('Geco_proyecto_presupuesto AS b', 'b.geco_proyecto_id', '=', 'a.id')
      ->join('Partida AS c', 'c.id', '=', 'b.partida_id')
      ->leftJoin('Proyecto_presupuesto AS d', function (JoinClause $join) use ($request) {
        $join->on('d.partida_id', '=', 'b.partida_id')
          ->where('d.proyecto_id', '=', $request->query('id'));
      })
      ->select([
        'b.id',
        'c.tipo',
        'c.partida',
        DB::raw("COALESCE(d.monto, 0) AS monto_original"),
        DB::raw("COALESCE(b.monto, 0) AS monto_modificado"),
        DB::raw("(b.monto_rendido - b.monto_excedido) AS monto_rendido"),
        DB::raw("(b.monto - b.monto_rendido + b.monto_excedido) AS saldo_rendicion"),
        'b.monto_excedido'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->where('c.tipo', '!=', 'Otros')
      ->orderBy('c.tipo')
      ->get()
      ->groupBy('tipo');

    $pdf = Pdf::loadView('investigador.actividades.presupuesto', ['proyecto' => $proyecto, 'presupuesto' => $presupuesto]);
    return $pdf->stream();
  }

  public function reporteConFin(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Grupo AS b', function (JoinClause $join) {
        $join->on('a.grupo_id', '=', 'b.id')
          ->join('Facultad AS b1', 'b1.id', '=', 'b.facultad_id')
          ->join('Area AS b2', 'b2.id', '=', 'b1.area_id');
      })
      ->leftJoin('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
      ->select([
        //  Grupo
        'b.grupo_nombre',
        'b1.nombre AS facultad',
        'b2.nombre AS area',
        'c.linea AS ocde',
        //  Proyecto
        'a.codigo_proyecto',
        'a.titulo',
        'd.nombre AS linea',
        'a.localizacion',
        'a.palabras_clave',
        'a.tipo_proyecto',
        'a.updated_at',
        'a.periodo',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluación'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecucion'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluido'
          ELSE 'Sin estado'
        END AS estado")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $calendario = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select([
        'b.nombre AS condicion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS nombres"),
        'c.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.actividades.reporte', ['proyecto' => $proyecto, 'detalles' => $detalles, 'calendario' => $calendario, 'integrantes' => $integrantes]);
    return $pdf->stream();
  }

  public function reporteSinFin(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Grupo AS b', function (JoinClause $join) {
        $join->on('a.grupo_id', '=', 'b.id')
          ->join('Facultad AS b1', 'b1.id', '=', 'b.facultad_id')
          ->join('Area AS b2', 'b2.id', '=', 'b1.area_id');
      })
      ->leftJoin('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
      ->select([
        //  Grupo
        'b.grupo_nombre',
        'b1.nombre AS facultad',
        'b2.nombre AS area',
        'c.linea AS ocde',
        //  Proyecto
        'a.titulo',
        'd.nombre AS linea',
        'a.localizacion',
        'a.palabras_clave',
        'a.updated_at',
        'a.periodo',
        DB::raw("CASE(a.estado)
        WHEN -1 THEN 'Eliminado'
        WHEN 0 THEN 'No aprobado'
        WHEN 1 THEN 'Aprobado'
        WHEN 3 THEN 'En evaluación'
        WHEN 5 THEN 'Enviado'
        WHEN 6 THEN 'En proceso'
        WHEN 7 THEN 'Anulado'
        WHEN 8 THEN 'Sustentado'
        WHEN 9 THEN 'En ejecucion'
        WHEN 10 THEN 'Ejecutado'
        WHEN 11 THEN 'Concluido'
        ELSE 'Sin estado'
      END AS estado")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $calendario = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select([
        'b.nombre AS condicion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS nombres"),
        'c.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Dependencia AS d', 'd.id', '=', 'b.dependencia_id')
      ->select([
        'b.codigo',
        'd.dependencia',
        'c.nombre AS facultad',
        'b.cti_vitae',
        'b.codigo_orcid',
        'b.scopus_id',
        'b.google_scholar'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->where('a.condicion', '=', 'Responsable')
      ->first();

    $pdf = Pdf::loadView('investigador.actividades.reporte_no_monetario', ['proyecto' => $proyecto, 'detalles' => $detalles, 'calendario' => $calendario, 'integrantes' => $integrantes, 'responsable' => $responsable]);
    return $pdf->stream();
  }

  public function reporteFex(Request $request) {
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
        DB::raw("FORMAT(a.aporte_unmsm + a.entidad_asociada + a.aporte_no_unmsm + a.financiamiento_fuente_externa, 2, 'en_US') AS monto"),
        DB::raw("FORMAT(a.aporte_unmsm, 2, 'en_US') AS aporte_unmsm"),
        DB::raw("FORMAT(a.aporte_no_unmsm, 2, 'en_US') AS aporte_no_unmsm"),
        DB::raw("FORMAT(a.financiamiento_fuente_externa, 2, 'en_US') AS financiamiento_fuente_externa"),
        DB::raw("FORMAT(a.entidad_asociada, 2, 'en_US') AS entidad_asociada"),
        'e.name AS pais',
        'a.resolucion_rectoral',
        'a.palabras_clave',
        'a.fecha_inicio',
        'a.fecha_fin',
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
        'a.observaciones_admin',
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
        DB::raw("CASE
          WHEN a.responsabilidad IN ('', 'null') OR a.responsabilidad IS NULL THEN c.nombre
          ELSE a.responsabilidad
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
}

<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtpbachillerController extends Controller {
  public function detalle(Request $request) {
    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_descripcion AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.codigo', '=', 'objetivo_ods');
      })
      ->join('Ods AS e', 'e.id', '=', 'd.detalle')
      ->join('Ocde AS f', 'f.id', '=', 'a.ocde_id')
      ->leftJoin('File AS g', function (JoinClause $join) {
        $join->on('g.tabla_id', '=', 'a.id')
          ->where('g.tabla', '=', 'Proyecto')
          ->where('g.recurso', '=', 'RESOLUCION_DECANAL')
          ->where('g.estado', '=', 20);
      })
      ->leftJoin('Facultad_programa AS h', 'h.id', '=', 'a.programa_id')
      ->leftJoin('Geco_proyecto AS i', 'i.proyecto_id', '=', 'a.id')
      ->select(
        'a.tipo_proyecto',
        'a.estado',
        'a.titulo',
        'a.codigo_proyecto',
        'a.resolucion_rectoral',
        DB::raw("IFNULL(a.resolucion_fecha, '') AS resolucion_fecha"),
        'b.nombre AS facultad',
        'h.programa',
        'c.nombre AS linea',
        'e.descripcion AS ods',
        'f.linea AS ocde',
        'a.orden_merito',
        'a.localizacion',
        'a.comentario',
        'a.observaciones_admin',
        DB::raw("CONCAT('/minio/proyecto-doc/', g.key) AS url1"),
        'a.dj_aceptada',
        DB::raw("CONCAT('/minio/declaracion-jurada/dj_PTPBACHILLER_', a.id, '.pdf') AS url2"),
        'i.id AS geco_proyecto_id'
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    return $detalle;
  }

  public function miembros(Request $request) {
    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_doc AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.proyecto_id')
          ->where('e.categoria', '=', 'carta')
          ->where('e.nombre', '=', 'Carta de compromiso del asesor')
          ->where('e.estado', '=', 1);
      })
      ->leftJoin('File AS f', function (JoinClause $join) {
        $join->on('f.tabla_id', '=', 'a.id')
          ->where('f.tabla', '=', 'Proyecto_integrante')
          ->where('f.recurso', '=', 'CARTA_COMPROMISO')
          ->where('f.estado', '=', 20);
      })
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'd.nombre AS facultad',
        DB::raw("COALESCE(CONCAT('/minio/', f.bucket, '/', f.key),CONCAT('/minio/proyecto-doc/', e.archivo)) AS url")
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $integrantes;
  }

  public function descripcion(Request $request) {
    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'planteamiento_problema',
        'justificacion',
        'estado_arte',
        'objetivos',
        'metodologia_trabajo',
        'referencias_bibliograficas',
        'presupuesto_justificacion',
        'presupuesto_otros_fondo_fuente',
        'presupuesto_otros_fondo_monto',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    return [
      'descripcion' => $descripcion,
    ];
  }

  public function actividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad AS a')
      ->select([
        'a.id',
        'a.actividad',
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
  }

  public function responsable(Request $request) {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Dependencia AS d', 'd.id', '=', 'c.dependencia_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
      ->leftJoin('Grupo_integrante AS f', function (JoinClause $join) {
        $join->on('f.investigador_id', '=', 'c.id')
          ->whereNot('f.condicion', 'LIKE', 'Ex%');
      })
      ->leftJoin('Grupo AS g', 'g.id', '=', 'f.grupo_id')
      ->leftJoin('Area AS h', 'h.id', '=', 'e.area_id')
      ->select([
        'c.nombres',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2) AS apellidos"),
        'c.doc_numero',
        'c.telefono_movil',
        'c.telefono_trabajo',
        'c.especialidad',
        'c.titulo_profesional',
        'c.grado',
        'c.tipo',
        DB::raw("CONCAT((CASE
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END), ' | ', (CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END)) AS docente_categoria"),
        'c.codigo',
        'd.dependencia',
        'e.nombre AS facultad',
        'c.email3',
        'g.grupo_nombre',
        'h.nombre AS area'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    return $responsable;
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_descripcion AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.codigo', '=', 'objetivo_ods');
      })
      ->join('Ods AS e', 'e.id', '=', 'd.detalle')
      ->join('Ocde AS f', 'f.id', '=', 'a.ocde_id')
      ->leftJoin('Facultad_programa AS g', 'g.id', '=', 'a.programa_id')
      ->select(
        'a.periodo',
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
        'a.updated_at',
        'a.titulo',
        'b.nombre AS facultad',
        'g.programa',
        'e.descripcion AS ods',
        'c.nombre AS linea',
        'f.linea AS ocde',
        'a.localizacion',
        'a.resolucion_decanal',
        'a.observaciones_admin',
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select([
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'c.doc_numero',
        'c.fecha_nac',
        'c.codigo',
        DB::raw("CONCAT((CASE
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END), ' | ', (CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END)) AS docente_categoria"),
        'c.codigo_orcid',
        'c.google_scholar',
        'c.regina',
        'c.dina',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    $estudiante = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Tesista');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('File AS e', function (JoinClause $join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.recurso', '=', 'CARTA_COMPROMISO')
          ->where('e.estado', '=', 20);
      })
      ->select([
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'd.nombre AS facultad',
        'c.tipo',
        'c.doc_numero',
        'c.email1',
        'e.key AS carta'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'planteamiento_problema',
        'justificacion',
        'estado_arte',
        'objetivos',
        'metodologia_trabajo',
        'referencias_bibliograficas',
        'presupuesto_justificacion',
        'presupuesto_otros_fondo_monto',
        'presupuesto_otros_fondo_fuente',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select(
        'b.codigo',
        'b.tipo',
        'b.partida',
        'a.monto',
      )
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.ptpbachiller', [
      'proyecto' => $proyecto,
      'responsable' => $responsable,
      'estudiante' => $estudiante,
      'descripcion' => $descripcion,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);

    return $pdf->stream();
  }
}

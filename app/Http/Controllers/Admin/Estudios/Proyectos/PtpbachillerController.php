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
      ->join('Proyecto_descripcion AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('codigo', '=', 'tipo_investigacion');
      })
      ->join('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->join('Area AS e', 'e.id', '=', 'd.area_id')
      ->join('Linea_investigacion AS f', 'f.id', '=', 'a.linea_investigacion_id')
      ->join('Ocde AS g', 'g.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_doc AS h', function (JoinClause $join) {
        $join->on('h.proyecto_id', '=', 'a.id')
          ->where('h.tipo', '=', 3)
          ->where('h.estado', '=', 1)
          ->where('h.categoria', '=', 'tesis')
          ->where('h.nombre', '=', 'Tesis Doctoral');
      })
      ->leftJoin('Proyecto_doc AS i', function (JoinClause $join) {
        $join->on('i.proyecto_id', '=', 'a.id')
          ->where('i.tipo', '=', 4)
          ->where('i.estado', '=', 1)
          ->where('i.categoria', '=', 'tesis')
          ->where('i.nombre', '=', 'Tesis Maestría');
      })
      ->select([
        'a.titulo',
        'c.grupo_nombre',
        'e.nombre AS area',
        'd.nombre AS facultad',
        'f.nombre AS linea',
        'b.detalle AS tipo_investigacion',
        'a.localizacion',
        'g.linea AS ocde',
        DB::raw("CASE
          WHEN h.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS url1"),
        DB::raw("CASE
          WHEN i.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS url2"),
      ])
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->join('Dependencia AS d', 'd.id', '=', 'b.dependencia_id')
      ->select([
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.codigo',
        'd.dependencia',
        'c.nombre AS facultad',
        'b.cti_vitae',
        'b.codigo_orcid',
        'b.scopus_id',
        'b.google_scholar',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->where('a.condicion', '=', 'Responsable')
      ->first();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'c.nombre AS condicion',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS integrante"),
        'b.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
        'duracion'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.psinfipu', [
      'proyecto' => $proyecto,
      'responsable' => $responsable,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
    ]);
    return $pdf->stream();
  }
}

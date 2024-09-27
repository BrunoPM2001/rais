<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinvposController extends Controller {
  public function detalle(Request $request) {

    $detalle = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Responsable');
      })
      ->leftJoin('Proyecto_integrante_dedicado AS c', 'c.investigador_id', '=', 'b.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select(
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.estado',
        'd.nombre AS facultad',
        'a.comentario',
        'a.observaciones_admin'
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    return $detalle;
  }

  public function miembros(Request $request) {
    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Proyecto_integrante_dedicado AS d', 'd.investigador_id', '=', 'b.id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        'd.cargo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.doc_numero',
        'b.codigo',
        'b.email3',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $integrantes;
  }

  public function documentos(Request $request) {
    $documentos = DB::table('Proyecto_doc')
      ->select([
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('estado', '=', 1)
      ->get();

    return $documentos;
  }

  public function descripcion(Request $request) {
    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', [
        'objetivo',
        'justificacion',
        'metas',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });


    return $descripcion;
  }

  public function actividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
        'duracion',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Proyecto_doc AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.id')
          ->where('e.tipo', '=', 17)
          ->where('e.estado', '=', 1)
          ->where('e.categoria', '=', 'resolucion')
          ->where('e.nombre', '=', 'Resolución de Designación Oficial');
      })
      ->leftJoin('Proyecto_doc AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'a.id')
          ->where('f.tipo', '=', 18)
          ->where('f.estado', '=', 1)
          ->where('f.categoria', '=', 'erd-cofinanciamiento')
          ->where('f.nombre', '=', 'ERD de cofinanciamiento');
      })
      ->select([
        'a.titulo',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'c.doc_numero',
        'c.email3',
        'd.nombre AS facultad',
        'c.codigo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        DB::raw("CASE
          WHEN e.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS anexo"),
        DB::raw("CASE
          WHEN f.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS rd"),
        'a.updated_at',
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
      ])
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.condicion',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_numero',
        'b.codigo',
        'b.email3',
        'c.nombre AS facultad',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

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

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.id AS partida_id',
        'b.codigo',
        'b.partida',
        'b.tipo',
        'a.monto'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $pdf = Pdf::loadView('investigador.actividades.taller', [
      'proyecto' => $proyecto,
      'miembros' => $miembros,
      'detalles' => $detalles,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);
    return $pdf->stream();
  }
}

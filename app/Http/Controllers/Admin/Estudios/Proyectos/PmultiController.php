<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PmultiController extends S3Controller {

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_descripcion AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('codigo', '=', 'tipo_investigacion');
      })
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
      ->leftJoin('Linea_investigacion AS f', 'f.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Ocde AS g', 'g.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_descripcion AS j', function (JoinClause $join) {
        $join->on('j.proyecto_id', '=', 'a.id')
          ->where('j.codigo', '=', 'area_tematica');
      })
      ->leftJoin('Proyecto_descripcion AS k', function (JoinClause $join) {
        $join->on('k.proyecto_id', '=', 'a.id')
          ->where('k.codigo', '=', 'objetivo_ods');
      })
      ->leftJoin('Ods AS l', 'l.id', '=', 'k.detalle')
      ->select([
        'c.grupo_nombre',
        'd.nombre AS facultad',
        'e.nombre AS area',
        'j.detalle AS area_tematica',
        'g.linea AS ocde',
        'a.palabras_clave',
        'a.titulo',
        'f.nombre AS linea',
        'l.descripcion AS ods',
        'a.localizacion',
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

    $docs = DB::table('Proyecto_doc')
      ->select([
        'comentario',
        'archivo',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 25)
      ->where('nombre', '=', 'Documento de colaboración externa')
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoin('File AS e', function (JoinClause $join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.bucket', '=', 'carta-compromiso')
          ->where('e.recurso', '=', 'CARTA_COMPROMISO')
          ->where('e.estado', '=', 20);
      })
      ->leftJoin('Grupo_integrante AS f', function (JoinClause $join) {
        $join->on('f.investigador_id', '=', 'b.id')
          ->whereNot('f.condicion', 'LIKE', 'Ex %');
      })
      ->leftJoin('Grupo AS g', 'g.id', '=', 'f.grupo_id')
      ->select([
        'a.id',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'c.nombre AS tipo_integrante',
        'd.nombre AS facultad',
        'g.grupo_nombre',
        DB::raw("CASE
          WHEN e.key IS NOT NULL THEN 'Sí'
          ELSE 'No' END AS compromiso")
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->groupBy('b.id')
      ->get();

    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.actividad',
        'a.justificacion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.partida',
        'a.justificacion',
        'b.tipo',
        'a.monto',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.pmulti', [
      'proyecto' => $proyecto,
      'docs' => $docs,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);
    return $pdf->stream();
  }
}

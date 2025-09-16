<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsinfipuController extends Controller {
  public function detalle(Request $request) {

    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->join('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->join('Area AS e', 'e.id', '=', 'd.area_id')
      ->join('Ocde AS f', 'f.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_doc AS g', function (JoinClause $join) {
        $join->on('g.proyecto_id', '=', 'a.id')
          ->where('g.categoria', '=', 'tesis')
          ->where('g.nombre', '=', 'Tesis Doctoral')
          ->where('g.estado', '=', 1);
      })
      ->leftJoin('Proyecto_doc AS h', function (JoinClause $join) {
        $join->on('h.proyecto_id', '=', 'a.id')
          ->where('h.categoria', '=', 'tesis')
          ->where('h.nombre', '=', 'Tesis Maestría')
          ->where('h.estado', '=', 1);
      })
      ->leftJoin('Proyecto_descripcion AS i', function (JoinClause $join) {
        $join->on('i.proyecto_id', '=', 'a.id')
          ->where('i.codigo', '=', 'investigacion_base');
      })
      ->leftJoin('Proyecto AS j', 'j.id', '=', 'i.proyecto_id')
      ->select(
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.estado',
        'c.grupo_nombre',
        'd.nombre AS area',
        'e.nombre AS facultad',
        'b.nombre AS linea',
        'a.comentario',
        'a.observaciones_admin',
        'f.linea AS ocde',
        'a.localizacion',
        DB::raw("CONCAT('/minio/proyecto-doc/', g.archivo) AS url1"),
        DB::raw("CONCAT('/minio/proyecto-doc/', h.archivo) AS url2"),
        DB::raw("CONCAT('/admin/estudios/proyectos_grupos/detalle/', LOWER(j.tipo_proyecto), '?id=', SUBSTRING_INDEX(i.detalle, '-', 1)) AS url3"),
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    return $detalle;
  }

  public function miembros(Request $request) {
    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
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
        'tipo_investigacion',
        'publicacion_editorial',
        'publicacion_url',
        'publicacion_tipo',
        'investigacion_base',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $archivo1 = DB::table('Proyecto_doc')
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('categoria', '=', 'tesis')
      ->where('nombre', '=', 'Tesis Doctoral')
      ->where('estado', '=', 1)
      ->count();

    $archivo2 = DB::table('Proyecto_doc')
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('categoria', '=', 'tesis')
      ->where('nombre', '=', 'Tesis Maestría')
      ->where('estado', '=', 1)
      ->count();

    return [
      'descripcion' => $descripcion,
      'archivo1' => $archivo1,
      'archivo2' => $archivo2,
    ];
  }

  public function actividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.proyecto_integrante_id',
        'a.actividad',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
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

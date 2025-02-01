<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsinfinvController extends Controller {
  public function detalle(Request $request) {

    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->join('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->join('Area AS e', 'e.id', '=', 'd.area_id')
      ->join('Ocde AS f', 'f.id', '=', 'a.ocde_id')
      ->leftJoin('File AS g', function (JoinClause $join) {
        $join->on('g.tabla_id', '=', 'a.id')
          ->where('g.tabla', '=', 'Proyecto')
          ->where('g.bucket', '=', 'proyecto-doc')
          ->where('g.recurso', '=', 'METODOLOGIA_TRABAJO')
          ->where('g.estado', '=', 20);
      })
      ->leftJoin('File AS h', function (JoinClause $join) {
        $join->on('h.tabla_id', '=', 'a.id')
          ->where('h.tabla', '=', 'Proyecto')
          ->where('h.bucket', '=', 'proyecto-doc')
          ->where('h.recurso', '=', 'PROPIEDAD_INTELECTUAL')
          ->where('h.estado', '=', 20);
      })
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
        DB::raw("CONCAT('/minio/', g.bucket, '/', g.key) AS url1"),
        DB::raw("CONCAT('/minio/', h.bucket, '/', h.key) AS url2")
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
        'a.tipo_tesis',
        'a.titulo_tesis',
        'a.excluido'
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
        'antecedentes',
        'objetivos_generales',
        'objetivos_especificos',
        'justificacion',
        'hipotesis',
        'metodologia_trabajo',
        'referencias_bibliograficas',
        'resumen_esperado',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $palabras_clave = DB::table('Proyecto')
      ->select([
        'palabras_clave'
      ])
      ->where('id', '=', $request->query('proyecto_id'))
      ->first();

    $archivo1 = DB::table('File AS a')
      ->select([
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('tabla', '=', 'Proyecto')
      ->where('tabla_id', '=', $request->query('proyecto_id'))
      ->where('bucket', '=', 'proyecto-doc')
      ->where('recurso', '=', 'METODOLOGIA_TRABAJO')
      ->where('estado', '=', 20)
      ->first();

    $archivo2 = DB::table('File AS a')
      ->select([
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('tabla', '=', 'Proyecto')
      ->where('tabla_id', '=', $request->query('proyecto_id'))
      ->where('bucket', '=', 'proyecto-doc')
      ->where('recurso', '=', 'PROPIEDAD_INTELECTUAL')
      ->where('estado', '=', 20)
      ->first();

    return [
      'descripcion' => $descripcion,
      'palabras_clave' => $palabras_clave->palabras_clave
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
      ->select([
        'a.titulo',
        'c.grupo_nombre',
        'e.nombre AS area',
        'd.nombre AS facultad',
        'f.nombre AS linea',
        'b.detalle AS tipo_investigacion',
        'a.localizacion',
        'g.linea AS ocde',
        'a.palabras_clave'
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

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.psinfinv', [
      'proyecto' => $proyecto,
      'responsable' => $responsable,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
    ]);
    return $pdf->stream();
  }
}

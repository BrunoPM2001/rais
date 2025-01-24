<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PicvController extends Controller {

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
      ->select(
        'id',
        'codigo',
        'detalle'
      )
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $detalles = [];
    foreach ($descripcion as $data) {
      if (isset($data->codigo)) {
        $detalles[$data->codigo] = $data->detalle;
      }
    }

    return $detalles;
  }

  public function actividades(Request $request) {

    $actividades = DB::table('Proyecto_actividad')
      ->select(
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin'
      )
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
  }

  public function documentos(Request $request) {

    $documentos = DB::table('Proyecto_doc')
      ->select([
        'archivo',
        'comentario'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('categoria', '=', 'carta')
      ->where('nombre', '=', 'Carta de compromiso del asesor')
      ->where('estado', '=', 1)
      ->first();

    if ($documentos) {
      $archivo = $documentos->archivo;
      $documentos->url = "/minio/proyecto-doc/" . $archivo;
    } else {
      // Inicializar como un objeto vacío para agregar propiedades
      $documentos = (object) [
        'url' => null,
        'comentario' => null // Si deseas agregar un comentario por defecto
      ];
    }


    return $documentos;
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

    $pdf = Pdf::loadView('investigador.convocatorias.picv', [
      'proyecto' => $proyecto,
      'responsable' => $responsable,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
    ]);
    return $pdf->stream();
  }
}

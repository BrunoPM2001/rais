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
      ->leftJoin('Proyecto_doc AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.proyecto_id')
          ->where('d.nombre', '=', 'Carta de compromiso del asesor')
          ->where('d.estado', '=', 1);
      })
      ->leftJoin('File AS e', function (JoinClause $join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.recurso', '=', 'CARTA_COMPROMISO')
          ->where('e.estado', '=', 1);
      })
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        DB::raw("COALESCE(CONCAT('/minio/', e.bucket, '/', e.key), CONCAT('/minio/proyecto-doc/', d.archivo)) AS url")
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

    //  Proyecto
    $proyecto = DB::table('Proyecto_integrante AS pint')
      ->join('Proyecto AS p', 'p.id', '=', 'pint.proyecto_id')
      ->join('Grupo AS g', 'g.id', '=', 'p.grupo_id')
      ->join('Facultad AS f', 'f.id', '=', 'g.facultad_id')
      ->join('Area AS a', 'a.id', '=', 'f.area_id')
      ->join('Linea_investigacion AS l', 'l.id', '=', 'p.linea_investigacion_id')
      ->join('Linea_investigacion_ods AS lo', 'lo.linea_investigacion_id', '=', 'l.id')
      ->join('Ods AS o', 'o.id', '=', 'lo.ods_id')
      ->join('Ocde AS oc', 'oc.id', '=', 'lo.ods_id')
      ->select([
        'p.titulo',
        'g.grupo_nombre',
        'f.nombre AS facultad_nombre',
        'a.nombre AS area_nombre',
        'p.codigo_proyecto',
        'p.titulo',
        'l.nombre AS linea_nombre',
        'o.objetivo',
        'oc.linea',
        'p.localizacion',
        DB::raw("CASE(p.estado)
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
        'p.updated_at'
      ])
      ->where('pint.condicion', '=', 'Responsable')
      ->where('pint.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    //  Descripcion
    $descripcion = DB::table('Proyecto AS p')
      ->join('Proyecto_descripcion AS pd', 'p.id', '=', 'pd.proyecto_id')
      ->select([
        'p.id',
        'pd.codigo',
        'pd.detalle',
      ])
      ->where('p.id', '=', $request->query('proyecto_id'))
      ->get();

    //  Actividades
    $actividades = DB::table('Proyecto AS p')
      ->join('Proyecto_actividad AS pa', 'p.id', '=', 'pa.proyecto_id')
      ->select([
        'pa.actividad',
        'pa.fecha_inicio',
        'pa.fecha_fin'
      ])
      ->where('pa.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    //  Presupuesto
    $presupuesto = DB::table('Proyecto AS p')
      ->join('Proyecto_presupuesto AS pp', 'pp.proyecto_id', '=', 'p.id')
      ->join('Partida AS pt', 'pt.id', '=', 'pp.partida_id')
      ->select([
        'pt.partida',
        'pp.monto',
        'pt.tipo'
      ])
      ->where('p.id', '=', $request->query('proyecto_id'))
      ->get();

    //  Integrantes
    $integrantes = DB::table('Proyecto_integrante AS pint')
      ->join('Usuario_investigador AS i', 'i.id', '=', 'pint.investigador_id')
      ->join('Proyecto_integrante_tipo AS pt', 'pt.id', '=', 'pint.proyecto_integrante_tipo_id')
      ->join('Facultad AS f', 'f.id', '=', 'i.facultad_id')
      ->leftJoin('Grupo_integrante AS gi', 'gi.investigador_id', '=', 'i.id')
      ->select([
        'pt.nombre AS tipo_integrante',
        DB::raw("CONCAT(i.apellido1, ' ', i.apellido2, ' ', i.nombres) AS integrante"),
        'f.nombre AS facultad',
        'gi.tipo',
        'gi.condicion'
      ])
      ->where('pint.proyecto_id', '=', $request->query('proyecto_id'))
      ->where(function ($query) {
        $query->where('gi.condicion', 'NOT LIKE', 'Ex%')
          ->orWhereNull('gi.condicion');
      })
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.picv', [
      'proyecto' => $proyecto,
      'descripcion' => $descripcion,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
      'integrantes' => $integrantes
    ]);


    return $pdf->stream();
  }
}

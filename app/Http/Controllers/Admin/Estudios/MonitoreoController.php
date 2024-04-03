<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MonitoreoController extends Controller {

  public function listadoProyectos($periodo, $tipo_proyecto, $estado_meta) {
    //  Validar si el periodo coincide con la tabla de Meta_periodo
    $periodos = DB::table('Meta_periodo')
      ->select(
        'id'
      )
      ->where('periodo', '=', $periodo)
      ->get();

    if (sizeof($periodos) > 0) {
      //  Validar si el tipo de proyecto coincide con la tabla de Meta_tipo_proyecto
      $tipos_proyecto = DB::table('Meta_tipo_proyecto')
        ->where('meta_periodo_id', '=', $periodos[0]->id)
        ->where('tipo_proyecto', '=', $tipo_proyecto)
        ->get();

      if (sizeof($tipos_proyecto) > 0) {
        //  Retornar en caso coincida todo
        $proyectos = DB::table('Proyecto AS a')
          ->leftJoin('Monitoreo_proyecto AS b', 'b.proyecto_id', '=', 'a.id')
          ->select(
            'a.id',
            'a.codigo_proyecto',
            'a.titulo',
            'a.estado',
            'b.estado AS estado_meta',
            'a.tipo_proyecto',
            'a.periodo'
          )
          ->where('a.estado', '=', 1)
          ->where('a.periodo', '=', $periodo)
          ->where('a.tipo_proyecto', '=', $tipo_proyecto);

        $proyectos = $estado_meta == 'null' ? $proyectos->get() : $proyectos->where('b.estado', '=', $estado_meta)->get();

        return ['data' => $proyectos];
      } else {
        return ['error' => 'Tipo de proyecto inválido'];
      }
    } else {
      return ['error' => 'Periodo inválido'];
    }
  }

  //  TODO - Verificar para qué es la tabla de Monitoreo_proyecto_publicacion
  public function detalleProyecto($proyecto_id) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Monitoreo_proyecto AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->select(
        'b.estado AS estado_meta',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.estado AS estado_proyecto',
        'a.titulo',
        'a.periodo',
        'c.nombre AS facultad',
        'b.descripcion'
      )
      ->where('a.id', '=', $proyecto_id)
      ->get();

    return ['data' => $proyecto];
  }

  public function metasCumplidas($proyecto_id) {
    //  TODO - Rehacer de manera adecuada esta query...
    $metas = DB::table('Meta_publicacion as t1')
      ->select('tipo_publicacion')
      ->selectRaw('
        CASE
            WHEN t1.tipo_publicacion = "tesis-asesoria" THEN (
                SELECT COUNT(*)
                FROM proyecto_integrante pi
                LEFT JOIN proyecto_integrante_tipo pit ON pi.proyecto_integrante_tipo_id = pit.id
                WHERE pi.proyecto_id = ' . $proyecto_id . '
                AND pit.nombre = "Tesista"
            )
            ELSE IF(
                tipo_publicacion = "tesis",
                (
                    SELECT COUNT(*)
                    FROM proyecto_integrante pi,
                    proyecto_integrante_tipo pt
                    WHERE pi.proyecto_id = ' . $proyecto_id . '
                    AND pt.id = pi.proyecto_integrante_tipo_id
                    AND pt.nombre = "Tesista"
                ),
                cantidad
            )
        END AS cantidad_requerida
    ')
      ->selectRaw('
        (
            select
            count(*)
            from
            publicacion_proyecto as pp,
            publicacion as pub
            WHERE
            pub.id = pp.publicacion_id AND
            pp.proyecto_id = ' . $proyecto_id . ' and
            pub.tipo_publicacion = t1.tipo_publicacion and
            pub.estado = 1
        ) AS cantidad_completada
    ')
      ->leftJoin('Meta_tipo_proyecto as t2', 't2.id', '=', 't1.meta_tipo_proyecto_id')
      ->leftJoin('Meta_periodo as t3', 't3.id', '=', 't2.meta_periodo_id')
      ->where('t3.periodo', '=', 2021)
      ->where('t2.tipo_proyecto', '=', 'PCONFIGI')
      ->orderByDesc('t1.created_at')
      ->get();

    return ['data' => $metas];
  }

  public function publicaciones($proyecto_id) {
    $publicaciones = DB::table('Publicacion_proyecto AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select(
        'b.id',
        'b.titulo',
        'b.tipo_publicacion',
        DB::raw('YEAR(b.fecha_publicacion) AS periodo'),
        'b.estado'
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->get();

    return ['data' => $publicaciones];
  }

  public function listadoPeriodos() {
    $metas = DB::table('Meta_periodo')
      ->select(
        'id',
        'periodo',
        'descripcion',
        'estado'
      )
      ->get();

    return ['data' => $metas];
  }

  public function listadoTipoProyectos($meta_periodo_id) {
    $metas = DB::table('Meta_tipo_proyecto')
      ->select(
        'id',
        'tipo_proyecto',
        'estado'
      )
      ->where('meta_periodo_id', '=', $meta_periodo_id)
      ->get();

    return ['data' => $metas];
  }

  public function listadoPublicaciones($meta_tipo_proyecto_id) {
    $metas = DB::table('Meta_publicacion')
      ->select(
        'id',
        'tipo_publicacion',
        'cantidad',
        'estado'
      )
      ->where('meta_tipo_proyecto_id', '=', $meta_tipo_proyecto_id)
      ->get();

    return ['data' => $metas];
  }
}

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
    $proyecto = DB::table('Proyecto')
      ->select(
        'titulo',
        'tipo_proyecto',
        'periodo',
      )
      ->get();
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

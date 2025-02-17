<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoreoController extends Controller {

  public function listadoProyectos() {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'b.proyecto_integrante_tipo_id')
      ->join('Meta_tipo_proyecto AS e', function (JoinClause $join) {
        $join->on('e.tipo_proyecto', '=', 'a.tipo_proyecto')
          ->where('e.estado', '=', 1);
      })
      ->join('Meta_periodo AS f', function (JoinClause $join) {
        $join->on('f.id', '=', 'e.meta_periodo_id')
          ->on('f.periodo', '=', 'a.periodo')
          ->where('f.estado', '=', 1);
      })
      ->join('Meta_publicacion AS g', 'g.meta_tipo_proyecto_id', '=', 'e.id')
      ->leftJoin('Monitoreo_proyecto AS h', 'h.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
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
        'a.tipo_proyecto',
        'a.periodo',
        DB::raw("CASE(h.estado)
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
          ELSE 'Por presentar' END AS estado_meta")
      )
      ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador'])
      ->whereIn('a.estado', [1, 9, 10, 11])
      ->groupBy('a.id')
      ->get();

    return $proyectos;
  }

  public function detalles(Request $request) {
    $datos = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
        $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
          ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
      })
      ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'a.facultad_id')
      ->leftJoin('Monitoreo_proyecto AS f', 'f.proyecto_id', '=', 'a.id')
      ->select([
        'a.titulo',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS responsable"),
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
        'a.periodo',
        'e.nombre AS facultad',
        DB::raw("CASE(f.estado)
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
          ELSE 'Por presentar' END AS estado_meta"),
        'f.descripcion',
        'f.observacion'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $metas = DB::table('Meta_publicacion AS a')
      ->join('Meta_tipo_proyecto AS b', 'b.id', '=', 'a.meta_tipo_proyecto_id')
      ->join('Meta_periodo AS c', 'c.id', '=', 'b.meta_periodo_id')
      ->leftJoin('Publicacion AS d', 'd.tipo_publicacion', '=', 'a.tipo_publicacion')
      ->leftJoin('Publicacion_proyecto AS e', function ($join) use ($request) {
        $join->on('e.publicacion_id', '=', 'd.id')
          ->where('e.proyecto_id', '=', $request->query('id'));
      })
      ->select([
        'a.tipo_publicacion',
        'a.cantidad AS requerido',
        DB::raw('COUNT(e.id) AS completado')
      ])
      ->where('c.periodo', '=', $datos->periodo)
      ->where('b.tipo_proyecto', '=', $datos->tipo_proyecto)
      ->where('a.estado', '=', 1)
      ->groupBy('a.tipo_publicacion', 'a.cantidad')
      ->get();

    $publicaciones = DB::table('Publicacion_proyecto AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select([
        'a.id',
        'b.id AS publicacion_id',
        'b.titulo',
        'b.tipo_publicacion',
        DB::raw("YEAR(b.fecha_publicacion) AS periodo"),
        DB::raw("CASE(b.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return [
      'datos' => $datos,
      'metas' => $metas,
      'publicaciones' => $publicaciones
    ];
  }

  public function guardar(Request $request) {
    if ($request->input('estado') == 2) {
      DB::table('Monitoreo_proyecto')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->update([
          'estado' => $request->input('estado'),
          'observacion' => $request->input('observacion'),
          'updated_at' => Carbon::now()
        ]);
    } else if ($request->input('estado') == 1) {
      DB::table('Monitoreo_proyecto')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->update([
          'estado' => $request->input('estado'),
          'fecha_aprobacion' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
    } else {
      DB::table('Monitoreo_proyecto')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->update([
          'estado' => $request->input('estado'),
          'updated_at' => Carbon::now()
        ]);
    }

    return ['message' => 'info', 'detail' => 'Actulizado correctamente'];
  }

  public function publicacionesDisponibles(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
        $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
          ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
      })
      ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->select([
        'a.periodo',
        'd.id AS investigador_id'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_proyecto AS c', function (JoinClause $join) use ($request) {
        $join->on('c.publicacion_id', '=', 'a.id')
          ->where('c.proyecto_id', '=', $request->query('id'));
      })
      ->select(
        'a.id',
        'a.titulo',
        DB::raw('YEAR(a.fecha_publicacion) AS periodo'),
      )
      ->where('a.estado', '=', 1)
      ->whereNull('c.id')
      ->where('b.investigador_id', '=', $proyecto->investigador_id)
      ->where('a.tipo_publicacion', '=', $request->query('tipo_publicacion'))
      ->having('periodo', '>=', $proyecto->periodo)
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return $publicaciones;
  }

  public function agregarPublicacion(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
        $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
          ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
      })
      ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'd.id AS investigador_id'
      ])
      ->where('a.id', '=', $request->input('proyecto_id'))
      ->first();

    DB::table('Publicacion_proyecto')
      ->insert([
        'investigador_id' => $proyecto->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'proyecto_id' => $request->input('proyecto_id'),
        'tipo' => 'INTERNO',
        'codigo_proyecto' => $proyecto->codigo_proyecto,
        'nombre_proyecto' => $proyecto->titulo,
        'entidad_financiadora' => 'UNMSM',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Publicación añadida'];
  }

  public function eliminarPublicacion(Request $request) {
    DB::table('Publicacion_proyecto')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Publicación eliminada correctamente'];
  }

  //  Metas
  public function listadoMetas() {
    $periodos = DB::table('Meta_periodo')
      ->select(
        'id',
        'periodo',
        'descripcion',
        DB::raw("CASE
          WHEN estado = 1 THEN 'Válido'
          WHEN estado = 0 THEN 'Inválido'
        END AS estado")
      )
      ->get();

    $tipos = DB::table('Meta_tipo_proyecto')
      ->select(
        'id',
        'meta_periodo_id',
        'tipo_proyecto',
        DB::raw("CASE
          WHEN estado = 1 THEN 'Válido'
          WHEN estado = 0 THEN 'Inválido'
        END AS estado")
      )
      ->get();

    $publicaciones = DB::table('Meta_publicacion')
      ->select(
        'id',
        'meta_tipo_proyecto_id',
        'tipo_publicacion',
        'cantidad',
        DB::raw("CASE
          WHEN estado = 1 THEN 'Válido'
          WHEN estado = 0 THEN 'Inválido'
        END AS estado")
      )
      ->get();

    return [
      'periodos' => $periodos,
      'tipos' => $tipos,
      'publicaciones' => $publicaciones,
    ];
  }

  public function agregarPeriodo(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_periodo')
      ->insert([
        'periodo' => $request->input('periodo'),
        'descripcion' => $request->input('descripcion'),
        'estado' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

    return ['message' => 'success', 'detail' => 'Periodo agregado correctamente'];
  }

  public function agregarProyecto(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_tipo_proyecto')
      ->insert([
        'meta_periodo_id' => $request->input('meta_periodo_id'),
        'tipo_proyecto' => $request->input('tipo_proyecto'),
        'estado' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

    return ['message' => 'success', 'detail' => 'Tipo de proyecto agregado correctamente'];
  }

  public function agregarMeta(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_publicacion')
      ->insert([
        'meta_tipo_proyecto_id' => $request->input('meta_tipo_proyecto_id'),
        'tipo_publicacion' => $request->input('tipo_publicacion'),
        'cantidad' => $request->input('cantidad'),
        'estado' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

    return ['message' => 'success', 'detail' => 'Meta agregada correctamente'];
  }

  public function editarMeta(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_publicacion')
      ->where('id', '=', $request->input('id'))
      ->update([
        'cantidad' => $request->input('cantidad'),
        'updated_at' => $now,
      ]);

    return ['message' => 'info', 'detail' => 'Meta editada correctamente'];
  }

  public function eliminarMeta(Request $request) {
    DB::table('Meta_publicacion')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Meta eliminada correctamente'];
  }
}

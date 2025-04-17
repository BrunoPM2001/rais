<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PconfigiController extends Controller {

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

  public function detalle(Request $request) {
    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->select(
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.estado',
        'a.resolucion_rectoral',
        DB::raw("IFNULL(a.resolucion_fecha, '') AS resolucion_fecha"),
        'a.comentario',
        'a.observaciones_admin',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.palabras_clave',
        'b.nombre AS linea',
        'c.linea AS ocde',
        'a.localizacion',
        DB::raw("CASE (a.dj_aceptada)
          WHEN 1 THEN CONCAT('/minio/declaracion-jurada/dj_PCONFIGI_', a.id, '.pdf')
          ELSE NULL
        END AS dj")
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    return $detalle;
  }

  public function miembros(Request $request) {
    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select(
        'a.id',
        'c.codigo',
        'b.nombre AS tipo_integrante',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, " ", c.nombres) AS nombre'),
        'c.tipo AS tipo_investigador',
        'a.tipo_tesis',
        'a.titulo_tesis'
      )
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('b.id')
      ->get();

    return $miembros;
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Grupo AS b', function (JoinClause $join) {
        $join->on('a.grupo_id', '=', 'b.id')
          ->join('Facultad AS b1', 'b1.id', '=', 'b.facultad_id')
          ->join('Area AS b2', 'b2.id', '=', 'b1.area_id');
      })
      ->leftJoin('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
      ->select([
        //  Grupo
        'b.grupo_nombre',
        'b1.nombre AS facultad',
        'b2.nombre AS area',
        'c.linea AS ocde',
        //  Proyecto
        'a.codigo_proyecto',
        'a.titulo',
        'd.nombre AS linea',
        'a.localizacion',
        'a.palabras_clave',
        'a.tipo_proyecto',
        'a.updated_at',
        'a.periodo',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluación'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecucion'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluido'
          ELSE 'Sin estado'
        END AS estado")
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

    $calendario = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.partida',
        'a.justificacion',
        'a.monto',
        'b.tipo'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select([
        'b.nombre AS condicion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS nombres"),
        'c.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.pconfigi', [
      'proyecto' => $proyecto,
      'detalles' => $detalles,
      'calendario' => $calendario,
      'presupuesto' => $presupuesto,
      'integrantes' => $integrantes
    ]);
    return $pdf->stream();
  }
}

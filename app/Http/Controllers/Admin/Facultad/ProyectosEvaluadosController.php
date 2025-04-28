<?php

namespace App\Http\Controllers\Admin\Facultad;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectosEvaluadosController extends Controller {
  public function opciones() {
    $opciones = DB::table('Proyecto_evaluacion AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'a.id',
        'b.tipo_proyecto',
        'b.periodo'
      ])
      ->groupBy(['tipo_proyecto', 'periodo'])
      ->orderByDesc('a.id')
      ->get();

    return $opciones;
  }

  public function listado(Request $request) {
    $proyectos = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Evaluacion_opcion AS e', function (JoinClause $join) {
        $join->on('e.tipo', '=', 'c.tipo_proyecto')
          ->on('e.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'c.id')
          ->on('f.evaluador_id', '=', 'b.id')
          ->whereNotNull('f.evaluacion_opcion_id');
      })
      ->select([
        'a.id',
        'a.evaluador_id',
        'a.proyecto_id',
        DB::raw("CONCAT(b.apellidos, ' ', b.nombres) AS evaluador"),
        'c.tipo_proyecto',
        'c.titulo',
        'd.nombre AS facultad',
        'c.periodo',
        DB::raw("COUNT(DISTINCT e.id) AS criterios"),
        DB::raw("COUNT(DISTINCT f.id) AS criterios_evaluados"),
        DB::raw("CASE
          WHEN f.cerrado = 1 THEN 'Sí'
          ELSE 'No'
        END as evaluado"),
        DB::raw("CASE
          WHEN a.ficha is not null THEN 'Sí'
          ELSE 'No'
        END as ficha"),
        DB::raw("CONCAT('/minio/proyecto-evaluacion/', a.ficha) AS url")
      ])
      ->where('e.nivel', '=', 1)
      ->where('c.periodo', '=', $request->query('periodo'))
      ->where('c.tipo_proyecto', '=', $request->query('tipo_proyecto'))
      ->groupBy('a.id')
      ->get();

    return $proyectos;
  }

  public function verFicha(Request $request) {
    $total = 0;

    $criterios = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Evaluacion_opcion AS d', function ($join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function ($join) {
        $join->on('e.proyecto_id', '=', 'c.id')
          ->on('e.evaluador_id', '=', 'b.id')
          ->on('e.evaluacion_opcion_id', '=', 'd.id');
      })
      ->select([
        'd.opcion',
        'd.puntaje_max',
        'd.nivel',
        'd.editable',
        DB::raw("COALESCE(e.puntaje, 0.00) AS puntaje"),
        'e.comentario',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->orderBy('d.orden')
      ->get();

    foreach ($criterios as $item) {
      if ($item->nivel == 1) {
        $total = $total + $item->puntaje;
      }
    }

    $extra = DB::table('Proyecto_evaluacion AS a')
      ->join('Usuario_evaluador AS b', 'a.evaluador_id', '=', 'b.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->join('Proyecto_integrante AS pix', 'pix.proyecto_id', '=', 'a.proyecto_id')
      ->join('Usuario_investigador AS ix', 'ix.id', '=', 'pix.investigador_id')
      ->select(
        'a.comentario',
        'a.id',
        'a.proyecto_id',
        'b.id as evaluador_id',
        'c.titulo',
        'c.tipo_proyecto',
        DB::raw("CONCAT(b.apellidos, ' ', b.nombres) AS evaluador"),
        DB::raw("CONCAT(ix.apellido1, ' ', ix.apellido2 ,' ', ix.nombres) AS responsable")
      )
      ->where('a.id', '=', $request->query('id'))
      ->whereIn('pix.condicion', ['Responsable', 'Coordinador', 'Asesor'])
      ->first();



    $pdf = Pdf::loadView('evaluador.ficha', ['evaluacion' => $criterios, 'extra' => $extra, 'total' => $total]);
    return $pdf->stream();
  }
}

<?php

namespace App\Http\Controllers\Investigador\Actividades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProyectoDetalleController extends Controller {
  public function detalleProyecto(Request $request) {
    $esIntegrante = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esIntegrante > 0) {
      $detalles = DB::table('Proyecto AS a')
        ->leftJoin('Proyecto_descripcion AS b', function ($join) {
          $join->on('b.proyecto_id', '=', 'a.id')
            ->where('b.codigo', '=', 'tipo_investigacion');
        })
        ->leftJoin('Proyecto_presupuesto AS c', 'c.proyecto_id', '=', 'a.id')
        ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
        ->leftJoin('Linea_investigacion AS e', 'e.id', '=', 'a.linea_investigacion_id')
        ->leftJoin('Grupo AS f', 'f.id', '=', 'a.grupo_id')
        ->select([
          'a.id',
          'a.estado',
          'a.tipo_proyecto',
          'a.codigo_proyecto',
          'a.titulo',
          'a.periodo',
          'b.detalle AS tipo_investigacion',
          DB::raw("SUM(c.monto) AS monto"),
          'd.nombre AS facultad',
          'e.nombre AS linea_investigacion',
          'a.fecha_inscripcion',
          'a.resolucion_rectoral',
          'f.grupo_nombre'
        ])
        ->where('a.id', '=', $request->query('proyecto_id'))
        ->first();

      $participantes = DB::table('Proyecto_integrante AS a')
        ->leftJoin('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
        ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
        ->select([
          'b.nombre AS condicion',
          'c.codigo',
          DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres")
        ])
        ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
        ->get();

      return [
        'detalles' => $detalles,
        'participantes' => $participantes
      ];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }
}

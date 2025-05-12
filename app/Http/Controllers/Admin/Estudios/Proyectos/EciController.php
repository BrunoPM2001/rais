<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EciController extends Controller {
  public function detalle(Request $request) {
    $responsable = DB::table('Grupo_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->select(
        'a.grupo_id',
        'c.grupo_nombre',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('cargo', '=', 'Coordinador');

    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->leftJoinSub($responsable, 'c', 'c.grupo_id', '=', 'a.grupo_id')
      ->select(
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.estado',
        'a.resolucion_rectoral',
        DB::raw("IFNULL(a.resolucion_fecha, 'No tiene fecha') AS resolucion_fecha"),
        'a.comentario',
        'a.observaciones_admin',
        'b.nombre AS linea',
        DB::raw("COALESCE(c.responsable, 'No hay coordinador') AS responsable"),
        DB::raw("COALESCE(c.grupo_nombre, 'Grupo sin coordinador') AS grupo_nombre")
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    return $detalle;
  }

  public function especificaciones(Request $request) {
    $especificaciones = DB::table('Proyecto_descripcion')
      ->select(
        'detalle'
      )
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('codigo', '=', 'desc_equipo')
      ->first();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        'archivo'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('categoria', ['especificaciones-tecnicas', 'cotizacion-equipo'])
      ->get()
      ->map(function ($item) {
        $item->archivo = "/minio/proyecto-doc/" . $item->archivo;
        return $item;
      });

    return [
      'equipo' => [
        'nombre' => 'nombre',
        'descripcion' => $especificaciones->detalle ?? ""
      ],
      'archivos' => $archivos
    ];
  }

  public function impacto(Request $request) {
    $detalles = DB::table('Proyecto_descripcion')
      ->select(
        'codigo',
        'detalle'
      )
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', ['impacto_propuesta', 'plan_manejo'])
      ->get();

    $impacto = [];
    foreach ($detalles as $data) {
      $impacto[$data->codigo] = $data->detalle;
    }

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'nombre',
        'comentario',
        'archivo'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('categoria', ['impacto', 'sustento'])
      ->get()
      ->map(function ($item) {
        $item->archivo = "/minio/proyecto-doc/" . $item->archivo;
        return $item;
      });

    return [
      'impacto' => $impacto,
      'archivos' => $archivos
    ];
  }

  public function reporte(Request $request) {
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

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.eci', [
      'detalles' => $detalles,
    ]);
    return $pdf->stream();
  }
}

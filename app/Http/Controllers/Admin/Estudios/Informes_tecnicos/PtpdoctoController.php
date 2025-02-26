<?php

namespace App\Http\Controllers\Admin\Estudios\Informes_tecnicos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtpdoctoController extends Controller {
  public function reporte(Request $request) {
    $detalles = DB::table('Informe_tecnico AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id AS proyecto_id',
        'b.codigo_proyecto',
        'b.tipo_proyecto',
        DB::raw("COALESCE(a.fecha_registro_csi, a.fecha_envio) AS fecha_estado"),
        'a.*',
      ])
      ->where('a.id', '=', $request->query('informe_tecnico_id'))
      ->first();

    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Proyecto_descripcion AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.codigo', '=', 'publicacion_tipo');
      })
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.resolucion_rectoral',
        'a.periodo',
        'c.grupo_nombre',
        'a.localizacion',
        'b.nombre AS facultad',
        'd.detalle AS tipo_publicacion',
      ])
      ->where('a.id', '=', $detalles->proyecto_id)
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ' ', b.nombres) AS nombres"),
        'c.nombre AS condicion',
      ])
      ->where('a.proyecto_id', '=', $detalles->proyecto_id)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->condicion => $item->nombres];
      });

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $detalles->proyecto_id)
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });

    if ($request->query('tipo_informe') != "Informe académico final") {
      $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.ptpdocto1', [
        'proyecto' => $proyecto,
        'miembros' => $miembros,
        'archivos' => $archivos,
        'detalles' => $detalles,
        'informe' => $request->query('tipo_informe')
      ]);
    } else {
      $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.ptpdocto2', [
        'proyecto' => $proyecto,
        'miembros' => $miembros,
        'archivos' => $archivos,
        'detalles' => $detalles,
        'informe' => $request->query('tipo_informe')
      ]);
    }

    return $pdf->stream();
  }
}

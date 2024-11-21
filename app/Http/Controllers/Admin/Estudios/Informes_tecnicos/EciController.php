<?php

namespace App\Http\Controllers\Admin\Estudios\Informes_tecnicos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EciController extends Controller {
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
      ->leftJoin('Proyecto_integrante AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.condicion', '=', 'Responsable');
      })
      ->leftJoin('Usuario_investigador AS e', 'e.id', '=', 'd.investigador_id')
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'b.nombre AS facultad',
        'c.grupo_nombre',
        DB::raw("CONCAT(e.apellido1, ' ', e.apellido2, ', ', e.nombres) AS responsable"),
        'a.resolucion_rectoral',
      ])
      ->where('a.id', '=', $detalles->proyecto_id)
      ->first();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $detalles->proyecto_id)
      ->where('nombre', '=', 'Anexos proyecto ECI')
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });

    $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.eci', [
      'proyecto' => $proyecto,
      'archivos' => $archivos,
      'detalles' => $detalles,
      'informe' => $request->query('tipo_informe')
    ]);

    return $pdf->stream();
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios\Informes_tecnicos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinvposController extends Controller {
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
      ->where('nombre', '=', 'Anexo Proyecto PINVPOS')
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });
    
    $miembros = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'a.codigo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ' ', b.nombres) AS nombres"),
        'c.nombre AS condicion',
        'a.tipo_investigador AS tipo',
        'a.proyecto_integrante_tipo_id'
      ])
      ->where('a.proyecto_id', '=', $detalles->proyecto_id)
      ->orderByRaw("FIELD(a.proyecto_integrante_tipo_id, 28, 29)")
      ->orderBy('b.apellido1', 'asc')
      ->get();

    $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.pinvpos', [
      'proyecto' => $proyecto,
      'archivos' => $archivos,
      'detalles' => $detalles,
      'miembros' => $miembros,
      'informe' => $request->query('tipo_informe')
    ]);

    return $pdf->stream();
  }
}

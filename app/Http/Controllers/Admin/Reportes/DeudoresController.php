<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class DeudoresController extends Controller {
  public function getFacultades() {
    $facultades = DB::table('Facultad')
        ->select(
            'id as value',
            'nombre as label'
        )
        ->whereBetween('id', [1, 20])
        ->orderBy('id', 'asc')
        ->get();

    return response()->json($facultades);
  }

  private function getDeudores($periodo = null, $facultad = null){
  $deudasA = DB::table('Proyecto_integrante_deuda AS a')
    ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
    ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
    ->leftJoin('Proyecto AS d', 'd.id', '=', 'b.proyecto_id')
    ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
    ->leftJoin('Licencia AS f', 'f.investigador_id', '=', 'c.id')
    ->leftJoin('Licencia_tipo AS g', 'g.id', '=', 'f.licencia_tipo_id')
    ->leftJoin('Facultad AS h', 'h.id', '=', 'd.facultad_id')
    ->leftJoin('Proyecto_integrante_tipo AS i', 'i.id', '=', 'b.proyecto_integrante_tipo_id')
    ->select([
      'a.id',
      'c.codigo',
      DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
      'e.nombre AS facultad_investigador',
      'c.tipo',
      'g.tipo AS licencia_tipo',
      'i.nombre AS condicion',
      'd.tipo_proyecto',
      'd.codigo_proyecto',
      'h.nombre AS facultad_proyecto',
      'd.periodo',
      'a.detalle',
      'a.categoria',
    ])

    ->whereBetween('a.tipo', [1, 3])

    ->when($periodo, function ($query) use ($periodo) {
      if ($periodo === '2016_anteriores') {
        $query->where('d.periodo', '<=', 2016);
      } else {
        $query->where('d.periodo', $periodo);
      }
    })
    ->when($facultad, function ($query) use ($facultad) {
      $query->where('c.facultad_id', $facultad);
    })

    ->where(function ($query) {
      $query->whereNull('g.id')
        ->orWhere(function ($q) {
          $q->where('g.id', '!=', 7)
            ->where(function ($sub) {
              $sub->whereNotIn('g.id', [6, 4])
                ->orWhere(function ($s) {
                  $s->whereIn('g.id', [6, 4])
                    ->whereDate('f.fecha_fin', '<', now());
                });
            });
        });
    })
    ->groupBy('a.id');

  $deudasB = DB::table('Proyecto_integrante_deuda AS a')
    ->join('Proyecto_integrante_H AS b', 'b.id', '=', 'a.proyecto_integrante_h_id')
    ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
    ->leftJoin('Proyecto_H AS d', 'd.id', '=', 'b.proyecto_id')
    ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
    ->leftJoin('Licencia AS f', 'f.investigador_id', '=', 'c.id')
    ->leftJoin('Licencia_tipo AS g', 'g.id', '=', 'f.licencia_tipo_id')
    ->leftJoin('Facultad AS h', 'h.id', '=', 'd.facultad_id')
    ->select([
      'a.id',
      'c.codigo',
      DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
      'e.nombre AS facultad_investigador',
      'c.tipo',
      'g.tipo AS licencia_tipo',
      'b.condicion',
      'd.tipo AS tipo_proyecto',
      'd.codigo AS codigo_proyecto',
      'h.nombre AS facultad_proyecto',
      'd.periodo',
      'a.detalle',
      'a.categoria',
    ])

    ->whereBetween('a.tipo', [1, 3])

    ->when($periodo, function ($query) use ($periodo) {
      if ($periodo === '2016_anteriores') {
        $query->where('d.periodo', '<=', 2016);
      } else {
        $query->where('d.periodo', $periodo);
      }
    })
    ->when($facultad, function ($query) use ($facultad) {
      $query->where('c.facultad_id', $facultad);
    })

    ->where(function ($query) {
      $query->whereNull('g.id')
        ->orWhere(function ($q) {
          $q->where('g.id', '!=', 7)
            ->where(function ($sub) {
              $sub->whereNotIn('g.id', [6, 4])
                ->orWhere(function ($s) {
                  $s->whereIn('g.id', [6, 4])
                    ->whereDate('f.fecha_fin', '<', now());
                });
            });
        });
    })
    ->groupBy('a.id')
    ->union($deudasA);

  return DB::query()
    ->fromSub($deudasB, 't')
    ->orderBy('nombres', 'asc')
    ->get();
  }

  public function reporte(Request $request) {
    $periodo = $request->query('periodo');
    $facultad = $request->query('facultad');
    $data = $this->getDeudores($periodo, $facultad);
    $url = "https://rais.vrip.unmsm.edu.pe";
    $admin = (object) ['nombres' => $request->attributes->get('token_decoded')->nombres ?? 'Administrador'];
    $qr = base64_encode(QrCode::format('png')
      ->size(100)
      ->generate($url)
    );
    $facultadNombre = null;
    if ($facultad && $data->count() > 0) {
      $facultadNombre = $data->first()->facultad_investigador;
    }

    $pdf = Pdf::loadView('admin.reportes.deudoresPDF', [
      'lista' => $data,
      'fecha' => now()->format('d/m/Y'),
      'periodo' => $periodo,
      'facultad' => $facultadNombre,
      'admin' => $admin,
      'qr' => $qr
    ]);

    return $pdf->stream('reporte_deudores.pdf');
  }
}
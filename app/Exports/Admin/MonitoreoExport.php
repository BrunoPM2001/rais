<?php

namespace App\Exports\Admin;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Query\JoinClause;

class MonitoreoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {

  protected $filters;

  public function __construct($filters) {

    $this->filters = $filters;
  }

  public function headings(): array {

    return [
      'Id',
      'Codigo Proyecto',
      'Título',
      'Estado',
      'Estado Meta',
      'Tipo Proyecto',
      'Periodo'

    ];
  }

  public function query() {
    $query = DB::table('Proyecto AS a')
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
      ->whereIn('a.estado', [1, 9, 10, 11]);


    // Aplicar filtros dinámicos
    if (!empty($this->filters['id'])) {
      $query->where('a.id', $this->filters['id']);
    }
    if (!empty($this->filters['codigo_proyecto'])) {
      $query->where('a.codigo_proyecto', $this->filters['codigo_proyecto']);
    }
    if (!empty($this->filters['titulo'])) {
      $query->where('a.titulo', 'LIKE', "%{$this->filters['titulo']}%");
    }
    if (!empty($this->filters['estado'])) {
      $estadoFiltro = $this->filters['estado'];
      $estadoMap = [
        'Eliminado' => -1,
        'No aprobado' => 0,
        'Aprobado' => 1,
        'Observado' => 2,
        'En evaluacion' => 3,
        'Enviado' => 5,
        'En proceso' => 6,
        'Anulado' => 7,
        'Sustentado' => 8,
        'En ejecución' => 9,
        'Ejecutado' => 10,
        'Concluído' => 11,
      ];
      if (isset($estadoMap[$estadoFiltro])) {
        $query->where('a.estado', $estadoMap[$estadoFiltro]);
      }
    }
    if (!empty($this->filters['estado_meta'])) {
      $query->whereRaw("CASE(h.estado)
        WHEN 0 THEN 'No aprobado'
        WHEN 1 THEN 'Aprobado'
        WHEN 2 THEN 'Observado'
        WHEN 5 THEN 'Enviado'
        WHEN 6 THEN 'En proceso'
    ELSE 'Por presentar' END = ?", [$this->filters['estado_meta']]);
    }
    if (!empty($this->filters['tipo_proyecto'])) {
      $query->where('a.tipo_proyecto', $this->filters['tipo_proyecto']);
    }
    if (!empty($this->filters['periodo'])) {
      $query->where('a.periodo', $this->filters['periodo']);
    }

    return $query->groupBy('a.id')->orderBy('a.id');
  }


  public function styles(Worksheet $sheet) {
    $range = $sheet->calculateWorksheetDimension();

    $sheet->getStyle($range)->applyFromArray([
      'borders' => [
        'allBorders' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);

    return $sheet;
  }
}

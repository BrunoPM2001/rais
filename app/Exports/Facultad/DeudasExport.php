<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeudasExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {
  protected $facultad_id;

  public function __construct($facultad_id) {
    $this->facultad_id = $facultad_id;
  }

  public function headings(): array {
    return [
      'N° de doc',
      'Código',
      'Nombres',
      'Año',
      'Tipo de proyecto',
      'Código de proyecto',
      'Condición',
      'Tipo',
      'Categoría',
      'Detalle.',
      // 'Licencia',
      // 'Resolución',
      'Correo institucional',
    ];
  }

  public function query() {
    return DB::table('view_deudores AS a')
      ->join('Usuario_investigador AS b', 'a.investigador_id', '=', 'b.id')
      ->select(
        'b.doc_numero',
        'b.codigo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        'a.periodo',
        'a.ptipo',
        'a.pcodigo',
        'a.condicion',
        'a.tipo',
        'a.categoria',
        'a.detalle',
        'b.email3',
      )
      ->where('b.facultad_id', $this->facultad_id)
      ->orderBy('b.id');
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

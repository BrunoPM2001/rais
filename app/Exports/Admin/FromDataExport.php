<?php

namespace App\Exports\Admin;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FromDataExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles {

  protected $data;

  public function __construct($data) {

    $this->data = $data;
  }

  public function collection() {
    return collect($this->data);
  }

  public function headings(): array {
    return array_keys($this->data[0] ?? []);
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

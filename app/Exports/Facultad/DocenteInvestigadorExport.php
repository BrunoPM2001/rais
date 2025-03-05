<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocenteInvestigadorExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {
  protected $facultad_id;

  public function __construct($facultad_id) {
    $this->facultad_id = $facultad_id;
  }

  public function headings(): array {
    return [
      'Estado',
      'Tipo de evaluación',
      'Fecha de constancia',
      'Fecha de fin',
      'Tipo de docente',
      'Código orcid',
      'Ap. paterno',
      'Ap. materno',
      'Nombres',
      'Tipo de doc.',
      'N° de doc',
      'Tel. móvil',
      'Correo institucional',
    ];
  }

  public function query() {
    return DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'a.investigador_id', '=', 'b.id')
      ->select([
        'a.estado',
        'a.tipo_eval',
        DB::raw("DATE(a.fecha_constancia) AS fecha_constancia"),
        DB::raw("DATE(a.fecha_fin) AS fecha_fin"),
        'a.tipo_docente',
        'a.orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_tipo',
        'b.doc_numero',
        'b.telefono_movil',
        'b.email3',
      ])
      ->where('b.facultad_id', '=', $this->facultad_id)
      ->whereRaw('a.created_at = (SELECT MAX(_t1.created_at) FROM Eval_docente_investigador as _t1 WHERE _t1.investigador_id = a.investigador_id)')
      ->orderBy('b.apellido1')
      ->orderBy('b.apellido2')
      ->orderBy('b.nombres');
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

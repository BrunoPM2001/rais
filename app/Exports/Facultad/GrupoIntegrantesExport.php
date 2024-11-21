<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GrupoIntegrantesExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {
  protected $facultad_id;

  public function __construct($facultad_id) {
    $this->facultad_id = $facultad_id;
  }

  public function headings(): array {
    return [
      'Id',
      'Nombre de grupo',
      'Tipo de integrante',
      'Condición',
      'Integrante',
      'Código',
      'N° de doc.',
      'Facultad',
      'Área',
      'Área Id',
      'Renacyt',
      'Renacyt nivel',
      'Correo institucional',
      'Nombre corto',
      'Estado',
      'Categoría de grupo',
      'Resolución rectoral',
      'Fecha de resolución',
      'Resolución rectoral de creación',
      'Fecha de resolución de creación',
    ];
  }

  public function query() {
    return DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
      ->select([
        'a.id',
        'a.grupo_nombre',
        'c.tipo',
        'b.condicion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'c.codigo',
        'c.doc_numero',
        'd.nombre AS facultad',
        'e.nombre AS area',
        'e.id AS area_id',
        'c.renacyt',
        'c.renacyt_nivel',
        'c.email3',
        'a.grupo_nombre_corto',
        DB::raw("CASE(a.estado)
          WHEN -2 THEN 'Disuelto'
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 4 THEN 'Registrado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado"),
        'a.grupo_categoria',
        'a.resolucion_rectoral',
        'a.resolucion_fecha',
        'a.resolucion_rectoral_creacion',
        'a.resolucion_creacion_fecha',
      ])
      ->where('a.facultad_id', '=', $this->facultad_id)
      ->orderBy('a.id')
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

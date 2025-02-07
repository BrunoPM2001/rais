<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicacionesExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {
  protected $facultad_id;

  public function __construct($facultad_id) {
    $this->facultad_id = $facultad_id;
  }

  public function headings(): array {
    return [
      'Facultad',
      'Periodo',
      'Codigo Publicacion',
      'Tipo',
      'Categoria',
      'Titulo',
      'ISBN',
      'ISSN',
      'Edicion',
      'Editorial',
      'Autor',
      'Apellidos y Nombres',
      'Puntaje',
      'Cargo',
      'Codigo Docente',
      'Doc Numero',
      'Publicacion Nombre',
      'DOI',
      'URL',
      'Estado',
      
    ];
  }

  public function query() {

    return  DB::table('Publicacion AS t1')
    ->leftJoin('Publicacion_autor AS t2', 't1.id', '=', 't2.publicacion_id')
    ->leftJoin('Usuario_investigador AS t3', 't3.id', '=', 't2.investigador_id')
    ->leftJoin('Facultad AS f', 'f.id', '=', 't3.facultad_id')
    ->leftJoin('Publicacion_categoria AS t5', 't1.categoria_id', '=', 't5.id')
    ->select([
      'f.nombre AS facultad',
      DB::raw("YEAR(t1.fecha_publicacion) AS fecha_publicacion"),
      't1.codigo_registro',
      't5.tipo',
      't5.categoria',
      't1.titulo',
      't1.isbn',
      't1.issn',
      't1.edicion',
      't1.editorial',
      't2.autor',
      DB::raw('CONCAT(t3.apellido1, " " , t3.apellido2, ", ", t3.nombres) AS apellidos_nombres'),
      't2.puntaje',
      't2.categoria AS cargo',
      't3.codigo AS codigo_docente',
      't3.doc_numero',
      't1.publicacion_nombre',
      't1.doi',
      't1.url',
      DB::raw("CASE(t1.estado)                     
        WHEN 1 THEN 'Registrado'
        WHEN 2 THEN 'Observado'
        WHEN 5 THEN 'Enviado'
        WHEN 6 THEN 'En proceso'
        WHEN 7 THEN 'Anulado'
        WHEN 8 THEN 'No Registrado'
        WHEN 9 THEN 'Reg. Duplicado'
      ELSE 'Sin estado' END AS estado")
    ])
    ->where('t3.facultad_id', $this->facultad_id)
    ->where('t1.estado', '!=', '1')
    ->orderByDesc('t1.fecha_inscripcion');

    
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

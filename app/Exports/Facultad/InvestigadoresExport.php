<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvestigadoresExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {

  protected $facultad_id;

  public function __construct($facultad_id) {
    $this->facultad_id = $facultad_id;
  }

  public function headings(): array {
    return [
      'Facultad',
      'Código',
      'Tipo',
      'Ap. paterno',
      'Ap. materno',
      'Nombres',
      'Fecha de nacimiento',
      'Edad',
      'Tipo de documento',
      'N° de documento',
      'Renacyt',
      'Renacyt nivel',
      'Código orcid',
      'Sexo',
      'Puntaje',
    ];
  }

  public function query() {
    $fechaInicio = date('Y') - 7;
    $fechaFin = date('Y') - 1;

    $investigadores = DB::table('Usuario_investigador as t1')
      ->select([
        't2.nombre as facultad',
        't1.codigo',
        't1.tipo',
        't1.apellido1 as apellido_paterno',
        't1.apellido2 as apellido_materno',
        't1.nombres',
        't1.fecha_nac as fecha_nacimiento',
        DB::raw('IF(YEAR(t1.fecha_nac) > 0, (YEAR(CURDATE()) - YEAR(t1.fecha_nac)), NULL) as edad'),
        't1.doc_tipo as tipo_documento',
        't1.doc_numero',
        't1.renacyt',
        't1.renacyt_nivel',
        't1.codigo_orcid',
        't1.sexo',
        DB::raw('COALESCE(SUM(pub.puntaje), 0) + COALESCE(SUM(pat.puntaje), 0) as puntaje_total'),
      ])
      ->leftJoin('Facultad as t2', 't1.facultad_id', '=', 't2.id')
      ->leftJoinSub(
        DB::table('Publicacion_autor as pautor')
          ->select('pautor.investigador_id', DB::raw('SUM(pautor.puntaje) as puntaje'))
          ->join('Publicacion as pb', 'pautor.publicacion_id', '=', 'pb.id')
          ->where('pb.validado', 1)
          ->whereBetween(DB::raw('YEAR(pb.fecha_publicacion)'), [$fechaInicio, $fechaFin])
          ->groupBy('pautor.investigador_id'),
        'pub',
        'pub.investigador_id',
        '=',
        't1.id'
      )
      ->leftJoinSub(
        DB::table('Patente_autor as pautor')
          ->select('pautor.investigador_id', DB::raw('SUM(pautor.puntaje) as puntaje'))
          ->join('Patente as pt', 'pautor.patente_id', '=', 'pt.id')
          ->whereBetween(DB::raw('YEAR(pt.created_at)'), [$fechaInicio, $fechaFin])
          ->groupBy('pautor.investigador_id'),
        'pat',
        'pat.investigador_id',
        '=',
        't1.id'
      )
      ->where('t1.facultad_id', $this->facultad_id)
      ->where('t1.tipo', 'not like', 'Sin categoria%')
      ->where('t1.tipo', 'not like', '%externo%')
      ->where('t1.doc_numero', '!=', '')
      ->groupBy('t1.id', 't2.nombre')
      ->orderBy('t1.apellido1')
      ->orderBy('t1.apellido2')
      ->orderBy('t1.nombres');

    return $investigadores;
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

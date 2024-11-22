<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FexExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles {
  protected $facultad_id;

  public function __construct($facultad_id) {
    $this->facultad_id = $facultad_id;
  }

  public function headings(): array {
    return [
      'Id',
      'Código de proyecto',
      'Título',
      'Responsable',
      'Facultad',
      'Moneda',
      'Aporte no unmsm',
      'Aporte unmsm',
      'Financiamiento fuente externa',
      'Monto asignado',
      'Participación unmsm',
      'Fuente fin.',
      'Periodo',
      'Registrado',
      'Actualizado',
      'Estado'
    ];
  }

  public function query() {

    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $projectDescriptions = function ($code) {
      return DB::table('Proyecto_descripcion')
        ->select('proyecto_id', 'detalle')
        ->where('codigo', '=', $code);
    };

    return DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->leftJoinSub($projectDescriptions('moneda_tipo'), 'moneda', 'moneda.proyecto_id', '=', 'a.id')
      ->leftJoinSub($projectDescriptions('participacion_ummsm'), 'p_unmsm', 'p_unmsm.proyecto_id', '=', 'a.id')
      ->leftJoinSub($projectDescriptions('fuente_financiadora'), 'fuente', 'fuente.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        'res.responsable',
        'b.nombre AS facultad',
        'moneda.detalle AS moneda',
        'a.aporte_no_unmsm',
        'a.aporte_unmsm',
        'a.financiamiento_fuente_externa',
        'a.monto_asignado',
        'p_unmsm.detalle AS participacion_unmsm',
        'fuente.detalle AS fuente_fin',
        'a.periodo',
        DB::raw('DATE(a.created_at) AS registrado'),
        DB::raw('DATE(a.updated_at) AS actualizado'),
        DB::raw("CASE(a.estado)
              WHEN -1 THEN 'Eliminado'
              WHEN 0 THEN 'No aprobado'
              WHEN 1 THEN 'Aprobado'
              WHEN 3 THEN 'En evaluacion'
              WHEN 5 THEN 'Enviado'
              WHEN 6 THEN 'En proceso'
              WHEN 7 THEN 'Anulado'
              WHEN 8 THEN 'Sustentado'
              WHEN 9 THEN 'En ejecución'
              WHEN 10 THEN 'Ejecutado'
              WHEN 11 THEN 'Concluído'
            ELSE 'Sin estado' END AS estado")
      )
      ->where('a.tipo_proyecto', '=', 'PFEX')
      ->where('a.facultad_id', $this->facultad_id)
      ->where('a.estado', '!=', -1);
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

<?php

namespace App\Exports\Admin;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Query\JoinClause;

class InformeTecnicoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
{

  protected $filters;

  public function __construct($filters)
  {

    $this->filters = $filters;
  }

  public function headings(): array
  {

    return [
      'Id',
      'Tipo Proyecto',
      'Codigo Proyecto',
      'Título',
      'Deuda',
      'Tipo de deuda',
      'N° de informes',
      'Responsable',
      'Facultad',
      'Periodo',
      'Estado'
    ];
  }

  public function query()
  {


    if (!empty($this->filters['tabla'])) {

      if ($this->filters['tabla'] == 'nuevos') {
        $responsable = DB::table('Proyecto_integrante AS a')
          ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
          ->select(
            'a.proyecto_id',
            DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
          )
          ->where('condicion', '=', 'Responsable');

        $deuda = DB::table('Proyecto_integrante AS a')
          ->leftJoin('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
          ->select([
            'a.proyecto_id',
            DB::raw("CASE
                      WHEN (b.tipo IS NULL OR b.tipo <= 0) THEN 'NO'
                      WHEN b.tipo > 0 AND b.tipo <= 3 THEN 'SI'
                      WHEN b.tipo > 3 THEN 'SUBSANADA'
                  END AS deuda"),
            'b.categoria'
          ])
          ->groupBy('a.proyecto_id');

        $proyectos = DB::table('Proyecto AS a')
          ->leftJoin('Informe_tecnico AS b', 'b.proyecto_id', '=', 'a.id')
          ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
          ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
          ->leftJoinSub($deuda, 'deu', 'deu.proyecto_id', '=', 'a.id')
          ->select(
            'a.id',
            'a.tipo_proyecto',
            'a.codigo_proyecto',
            'a.titulo',
            'deu.deuda',
            'deu.categoria AS tipo_deuda',
            DB::raw('COUNT(b.id) AS cantidad_informes'),
            'res.responsable',
            'c.nombre AS facultad',
            'a.periodo',
            DB::raw("CASE(b.estado)
                      WHEN 0 THEN 'En proceso'
                      WHEN 1 THEN 'Aprobado'
                      WHEN 2 THEN 'Presentado'
                      WHEN 3 THEN 'Observado'
                      ELSE 'No tiene informe'
                  END AS estado")
          )
          ->where('a.estado', '=', 1)
          ->where('a.tipo_proyecto', '!=', 'PFEX');
      } else {
        $responsable = DB::table('Proyecto_integrante_H AS a')
          ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
          ->select(
            'a.proyecto_id',
            DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
          )
          ->where('a.condicion', '=', 'Responsable');

        $deuda = DB::table('Proyecto_integrante_H AS a')
          ->leftJoin('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_h_id', '=', 'a.id')
          ->select([
            'a.proyecto_id',
            DB::raw("CASE
                      WHEN (b.tipo IS NULL OR b.tipo <= 0) THEN 'NO'
                      WHEN b.tipo > 0 AND b.tipo <= 3 THEN 'SI'
                      WHEN b.tipo > 3 THEN 'SUBSANADA'
                  END AS deuda"),
            'b.categoria'
          ])
          ->groupBy('a.proyecto_id');

        $proyectos = DB::table('Proyecto_H AS a')
          ->leftJoin('Informe_tecnico_H AS b', 'b.proyecto_id', '=', 'a.id')
          ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
          ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
          ->leftJoinSub($deuda, 'deu', 'deu.proyecto_id', '=', 'a.id')
          ->select(
            'a.id',
            'a.tipo AS tipo_proyecto',
            'a.codigo AS codigo_proyecto',
            'a.titulo',
            'deu.deuda',
            'deu.categoria AS tipo_deuda',
            DB::raw('COUNT(b.id) AS cantidad_informes'),
            'res.responsable',
            'c.nombre AS facultad',
            'a.periodo',
            DB::raw("CASE(b.status)
                      WHEN 0 THEN 'En proceso'
                      WHEN 1 THEN 'Aprobado'
                      WHEN 2 THEN 'Presentado'
                      WHEN 3 THEN 'Observado'
                      ELSE 'No tiene informe'
                  END AS estado")
          )
          ->where('a.status', '>', 0);
      }

      // Aplicar filtros dinámicos
      if (!empty($this->filters['id'])) {
        $proyectos->where('a.id', $this->filters['id']);
      }
      if (!empty($this->filters['tipo_proyecto'])) {
        $proyectos->where('a.tipo_proyecto', $this->filters['tipo_proyecto']);
      }
      if (!empty($this->filters['codigo_proyecto'])) {
        $proyectos->where('a.codigo_proyecto', $this->filters['codigo_proyecto']);
      }
      if (!empty($this->filters['titulo'])) {
        $proyectos->where('a.titulo', 'LIKE', "%{$this->filters['titulo']}%");
      }
      if (!empty($this->filters['deuda'])) {
        $proyectos->where('deu.deuda', $this->filters['deuda']);
      }
      if (!empty($this->filters['tipo_deuda'])) {
        $proyectos->where('deu.categoria', $this->filters['tipo_deuda']);
      }
      if (!empty($this->filters['cantidad_informes'])) {
        $proyectos->havingRaw('COUNT(b.id) = ?', [$this->filters['cantidad_informes']]);
      }
      if (!empty($this->filters['responsable'])) {
        $proyectos->where('res.responsable', 'LIKE', "%{$this->filters['responsable']}%");
      }
      if (!empty($this->filters['facultad'])) {
        $proyectos->where('c.nombre', $this->filters['facultad']);
      }
      if (!empty($this->filters['periodo'])) {
        $proyectos->where('a.periodo', $this->filters['periodo']);
      }
      if (!empty($this->filters['estado'])) {
        $proyectos->whereRaw("CASE(b.estado) WHEN 0 THEN 'En proceso' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Presentado' WHEN 3 THEN 'Observado' ELSE 'No tiene informe' END = ?", [$this->filters['estado']]);
      }

      return $proyectos->groupBy('a.id')->orderBy('a.id');
    }
  }


  public function styles(Worksheet $sheet)
  {
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

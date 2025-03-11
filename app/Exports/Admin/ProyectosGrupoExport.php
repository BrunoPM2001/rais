<?php

namespace App\Exports\Admin;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProyectosGrupoExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
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
      'Periodo',
      'Linea Investigación',
      'Título',
      'Responsable',
      'Grupo',
      'Facultad',
      'Monto',
      'Resolución Rectoral',
      'Fecha de Actualización',
      'Estado'
    ];
  }

  public function query()
  {
    $query = DB::table('Proyecto AS a')
      ->leftJoin('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->leftJoin('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.proyecto_id', '=', 'a.id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_presupuesto AS f', 'f.proyecto_id', '=', 'a.id')
      ->leftJoin('Usuario_investigador AS g', 'g.id', '=', 'd.investigador_id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.periodo',
        'c.nombre AS linea',
        'a.titulo',
        DB::raw('CONCAT(g.apellido1, " ", g.apellido2, ", ", g.nombres) AS responsable'),
        'b.grupo_nombre',
        'e.nombre AS facultad',
        DB::raw('SUM(f.monto) AS monto'),
        'a.resolucion_rectoral',
        'a.updated_at',
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
                ELSE 'Sin estado' END AS estado")
      )
      ->where('d.condicion', '=', 'Responsable');

    if (!empty($this->filters['year'])) {
      $query->where('a.periodo', $this->filters['year']);
    }


    // Filtro por id
    if (!empty($this->filters['id'])) {
      $query->where('a.id', $this->filters['id']);
    }

    // Filtro por tipo de proyecto
    if (!empty($this->filters['tipo_proyecto'])) {
      $query->where('a.tipo_proyecto', $this->filters['tipo_proyecto']);
    }

    // Filtro por código de proyecto
    if (!empty($this->filters['codigo_proyecto'])) {
      $query->where('a.codigo_proyecto', $this->filters['codigo_proyecto']);
    }

    // Filtro por línea (usando el campo "c.nombre" ya que en el SELECT se usa "AS linea")
    if (!empty($this->filters['linea'])) {
      $query->where('c.nombre', 'like', '%' . $this->filters['linea'] . '%');
    }

    // Filtro por título
    if (!empty($this->filters['titulo'])) {
      $query->where('a.titulo', 'like', '%' . $this->filters['titulo'] . '%');
    }

    // Filtro por responsable (usando la concatenación de los campos de g)
    if (!empty($this->filters['responsable'])) {
      $query->where(DB::raw("CONCAT(g.apellido1, ' ', g.apellido2, ', ', g.nombres)"), 'like', '%' . $this->filters['responsable'] . '%');
    }

    // Filtro por grupo (nombre del grupo)
    if (!empty($this->filters['grupo_nombre'])) {
      $query->where('b.grupo_nombre', 'like', '%' . $this->filters['grupo_nombre'] . '%');
    }

    // Filtro por facultad
    if (!empty($this->filters['facultad'])) {
      $query->where('e.nombre', 'like', '%' . $this->filters['facultad'] . '%');
    }

    // Filtro por monto (utilizamos having dado que es una suma agregada)
    if (!empty($this->filters['monto'])) {
      $query->havingRaw('SUM(f.monto) = ?', [$this->filters['monto']]);
    }

    // Filtro por resolución rectoral
    if (!empty($this->filters['resolucion_rectoral'])) {
      $query->where('a.resolucion_rectoral', $this->filters['resolucion_rectoral']);
    }

    // Filtro por estado (convertimos el texto a valor numérico)
    if (!empty($this->filters['estado'])) {
      $estadoFiltro = $this->filters['estado'];
      switch ($estadoFiltro) {
        case 'Eliminado':
          $query->where('a.estado', -1);
          break;
        case 'No aprobado':
          $query->where('a.estado', 0);
          break;
        case 'Aprobado':
          $query->where('a.estado', 1);
          break;
        case 'Observado':
          $query->where('a.estado', 2);
          break;
        case 'En evaluacion':
          $query->where('a.estado', 3);
          break;
        case 'Enviado':
          $query->where('a.estado', 5);
          break;
        case 'En proceso':
          $query->where('a.estado', 6);
          break;
        case 'Anulado':
          $query->where('a.estado', 7);
          break;
        case 'Sustentado':
          $query->where('a.estado', 8);
          break;
        case 'En ejecución':
          $query->where('a.estado', 9);
          break;
        case 'Ejecutado':
          $query->where('a.estado', 10);
          break;
        case 'Concluído':
          $query->where('a.estado', 11);
          break;
        default:
          // Opcional: manejar caso por defecto si se requiere
          break;
      }
    }

  
   return $query->groupBy('a.id')->orderBy('a.id');
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

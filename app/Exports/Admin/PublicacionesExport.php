<?php

namespace App\Exports\Admin;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Database\Query\JoinClause;
use Maatwebsite\Excel\Concerns\FromCollection;

class PublicacionesExport implements FromCollection, WithHeadings {

  protected $filters;

  public function __construct($filters) {

    $this->filters = $filters;
  }

  public function headings(): array {

    return [
      'Id',
      'Codigo',
      'Tipo publicación',
      'Calificación',
      'Isbn',
      'Issn',
      'Revista',
      'Editorial',
      'Evento nombre',
      'Presentador',
      'Facultad',
      'Área',
      'Título',
      'Doi',
      'Url',
      'Fecha publicación',
      'Periodo',
      'Fecha de creación',
      'Fecha de actualización',
      'Estado',
      'Procedencia'
    ];
  }

  public function collection() {
    $data = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', function (JoinClause $join) {
        $join->on('b.publicacion_id', '=', 'a.id')
          ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
          ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
          ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
          ->where('b.presentado', '=', 1);
      })
      ->leftJoin('Publicacion_categoria AS f', 'f.id', '=', 'a.categoria_id')
      ->select(
        'a.id',
        'a.codigo_registro',
        DB::raw("CASE (a.tipo_publicacion)
        WHEN 'articulo' THEN 'Artículo en revista'
        WHEN 'capitulo' THEN 'Capítulo de libro'
        WHEN 'libro' THEN 'Libro'
        WHEN 'tesis' THEN 'Tesis propia'
        WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
        WHEN 'evento' THEN 'R. en evento científico'
        WHEN 'ensayo' THEN 'Ensayo'
      ELSE tipo_publicacion END AS tipo"),
        'f.categoria AS calificacion',
        'a.isbn',
        'a.issn',
        'a.publicacion_nombre AS revista',
        'a.editorial',
        'a.evento_nombre',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS presentador"),
        'd.nombre AS facultad',
        'e.nombre AS area',
        'a.titulo',
        'a.doi',
        'a.url',
        'a.fecha_publicacion',
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'a.created_at',
        'a.updated_at',
        DB::raw("CASE(a.estado)
        WHEN -1 THEN 'Eliminado'
        WHEN 1 THEN 'Registrado'
        WHEN 2 THEN 'Observado'
        WHEN 5 THEN 'Enviado'
        WHEN 6 THEN 'En proceso'
        WHEN 7 THEN 'Anulado'
        WHEN 8 THEN 'No registrado'
        WHEN 9 THEN 'Duplicado'
      ELSE 'Sin estado' END AS estado"),
        'a.source AS procedencia'
      )
      ->groupBy('a.id')
      ->orderByDesc('a.id')
      ->get();

    return collect($data);
  }
}

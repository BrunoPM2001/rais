<?php

namespace App\Exports\Admin;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class InvestigadoresExport implements FromCollection, WithHeadings {

  protected $filters;

  public function __construct($filters) {

    $this->filters = $filters;
  }

  public function headings(): array {

    return [
      'id',
      'rrhh_status',
      'puntaje',
      'tipo',
      'facultad',
      'codigo',
      'codigo_orcid',
      'apellido1',
      'apellido2',
      'nombres',
      'fecha_nac',
      'doc_tipo',
      'doc_numero',
      'telefono_movil',
      'email',
    ];
  }

  public function collection() {
    $puntajeT = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select(
        'a.investigador_id',
        DB::raw('SUM(a.puntaje) AS puntaje')
      )
      ->groupBy('a.investigador_id');

    $data = DB::table('Usuario_investigador AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($puntajeT, 'puntaje', 'puntaje.investigador_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.rrhh_status',
        'puntaje.puntaje',
        'a.tipo',
        'b.nombre AS facultad',
        'a.codigo',
        'a.codigo_orcid',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.fecha_nac',
        'a.doc_tipo',
        'a.doc_numero',
        'a.telefono_movil',
        'a.email3'
      )
      ->get();

    return collect($data);
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DocentesController extends Controller {
  public function listadoSolicitudes() {
    $docentes = DB::table('Eval_docente_investigador AS a')
      ->leftJoin('Eval_declaracion_jurada AS b', 'b.investigador_id', '=', 'a.investigador_id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select(
        'a.id',
        'a.estado',
        'a.tipo_eval',
        'b.declaracion',
        'c.tipo',
        'c.codigo_orcid',
        'c.apellido1',
        'c.apellido2',
        'c.nombres',
        'c.doc_tipo',
        'a.doc_numero',
        'c.telefono_movil',
        'c.email3'
      )
      ->where('a.tipo_eval', '=', 'Solicitud')
      ->whereIn('a.estado', ['ENVIADO', 'TRAMITE', 'NO_APROBADO', 'PROCESO'])
      ->get();

    return ['data' => $docentes];
  }

  public function listadoConstancias() {
    $docentes = DB::table('Eval_docente_investigador AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select(
        'a.id',
        'a.estado',
        'a.tipo_eval',
        'b.tipo',
        'b.codigo_orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_tipo',
        'b.doc_numero',
        'b.telefono_movil',
        'b.email3'
      )
      ->where('a.tipo_eval', '=', 'Constancia')
      ->whereIn('a.estado', ['APROBADO', 'PENDIENTE'])
      ->get();

    return ['data' => $docentes];
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocenteInvestigadorController extends Controller {

  public function listado() {

    $evalSubQuery = DB::table('Eval_declaracion_jurada AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'DECLARACION_JURADA');
      })
      ->select([
        'b.key',
        'a.investigador_id'
      ])
      ->orderByDesc('a.fecha_inicio')
      ->groupBy('a.id');

    // Define la consulta principal
    $evaluaciones = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->joinSub($evalSubQuery, 'c', 'c.investigador_id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        DB::raw("CASE 
                WHEN a.estado = 'ENVIADO' THEN 'Enviado'
                WHEN a.estado = 'TRAMITE' THEN 'En trámite'
                WHEN a.estado = 'CONSTANCIA' THEN 'Constancia'
                WHEN a.estado = 'NO_APROBADO' THEN 'No aprobado'
                WHEN a.estado = 'PROCESO ' THEN 'Observado'
                ELSE ''
            END AS estado"),
        'a.tipo_eval',
        DB::raw("CONCAT('/minio/declaracion-jurada/', c.key) AS url"),
        'b.tipo',
        'd.nombre AS facultad',
        'b.codigo_orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_tipo',
        'a.doc_numero',
        'b.telefono_movil',
        'b.email3'
      ])
      ->where('a.tipo_eval', '=', 'Solicitud')
      ->whereIn('a.estado', ['ENVIADO', 'TRAMITE', 'NO_APROBADO', 'PROCESO'])
      ->groupBy('a.id')
      ->get();

    return $evaluaciones;
  }

  public function evaluarData(Request $request) {
    $detalles = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Repo_rrhh AS c', 'c.ser_cod_ant', '=', 'b.codigo')
      ->select([
        'a.nombres',
        DB::raw("CASE 
                WHEN a.estado = 'ENVIADO' THEN 'Enviado'
                WHEN a.estado = 'TRAMITE' THEN 'En trámite'
                WHEN a.estado = 'CONSTANCIA' THEN 'Constancia'
                WHEN a.estado = 'NO_APROBADO' THEN 'No aprobado'
                WHEN a.estado = 'PROCESO ' THEN 'Observado'
                ELSE ''
            END AS estado"),
        'a.doc_numero',
        'c.ser_fech_in_unmsm',
        'b.email3',
        'a.cti_vitae',
        'a.renacyt',
        'a.renacyt_nivel',
        'a.orcid',
        'a.google_scholar',
        'a.created_at',
        'a.tipo_docente',
        'a.docente_categoria',
        'a.clase',
        'a.horas',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    //  Requisito 1
    $req1 = DB::table('')
    ;

    return ['detalles' => $detalles];
  }
}

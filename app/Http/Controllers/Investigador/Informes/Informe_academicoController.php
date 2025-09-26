<?php

namespace App\Http\Controllers\Investigador\Informes;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Informe_academicoController extends S3Controller {
  public function listadoPendientes(Request $request) {
    $informes = DB::table(function ($subquery) use ($request) {
      $subquery
        ->from('Proyecto as a')
        ->join('Proyecto_integrante as b', 'b.proyecto_id', '=', 'a.id')
        ->join('Proyecto_integrante_tipo as c', function ($join) {
          $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
            ->whereIn('c.nombre', ['Responsable', 'Coordinador', 'Asesor', 'Autor Corresponsal']);
        })
        ->leftJoin('Informe_tecnico as d', 'd.proyecto_id', '=', 'a.id')
        ->leftJoin('Informe_tipo as e', 'd.informe_tipo_id', '=', 'e.id')
        ->select([
          DB::raw("CONCAT(IFNULL(d.id, ''), '_', a.id) AS id"),
          'd.id AS informe_id',
          'a.id AS proyecto_id',
          'a.codigo_proyecto',
          'a.titulo',
          'a.tipo_proyecto',
          'a.periodo',
          DB::raw("
                  CASE 
                      WHEN a.tipo_proyecto IN ('PTPMAEST', 'PTPGRADO')
                          AND e.informe = 'Informe académico de avance'
                          AND d.estado = 1
                          AND NOT EXISTS (
                              SELECT 1 FROM Informe_tecnico d2
                              JOIN Informe_tipo e2 ON d2.informe_tipo_id = e2.id
                              WHERE d2.proyecto_id = a.id
                              AND e2.informe = 'Informe académico final'
                          )
                      THEN 'Informe académico final'
  
                      WHEN a.tipo_proyecto = 'PTPDOCTO'
                          AND e.informe = 'Informe académico de avance'
                          AND d.estado = 1
                          AND NOT EXISTS (
                              SELECT 1 FROM Informe_tecnico d2
                              JOIN Informe_tipo e2 ON d2.informe_tipo_id = e2.id
                              WHERE d2.proyecto_id = a.id
                              AND e2.informe = 'Segundo informe académico de avance'
                          )
                      THEN 'Segundo informe académico de avance'
  
                      WHEN a.tipo_proyecto = 'PTPDOCTO'
                          AND e.informe = 'Segundo informe académico de avance'
                          AND d.estado = 1
                          AND NOT EXISTS (
                              SELECT 1 FROM Informe_tecnico d2
                              JOIN Informe_tipo e2 ON d2.informe_tipo_id = e2.id
                              WHERE d2.proyecto_id = a.id
                              AND e2.informe = 'Informe académico final'
                          )
                      THEN 'Informe académico final'

                      WHEN e.informe IS NOT NULL THEN e.informe
  
                      ELSE CASE 
                          WHEN a.tipo_proyecto LIKE 'PTP%' AND a.tipo_proyecto != 'PTPBACHILLER' THEN 'Informe académico de avance'
                          ELSE 'Informe académico'
                      END
                  END
               as informe"),
          DB::raw("
                  CASE 
                      WHEN (a.tipo_proyecto IN ('PTPMAEST', 'PTPGRADO')
                            AND e.informe = 'Informe académico de avance'
                            AND d.estado = 1
                            AND NOT EXISTS (
                                SELECT 1 FROM Informe_tecnico d2
                                JOIN Informe_tipo e2 ON d2.informe_tipo_id = e2.id
                                WHERE d2.proyecto_id = a.id
                                AND e2.informe = 'Informe académico final'
                            )) 
                      THEN 'Por presentar'
  
                      WHEN (a.tipo_proyecto = 'PTPDOCTO'
                            AND e.informe = 'Informe académico de avance'
                            AND d.estado = 1
                            AND NOT EXISTS (
                                SELECT 1 FROM Informe_tecnico d2
                                JOIN Informe_tipo e2 ON d2.informe_tipo_id = e2.id
                                WHERE d2.proyecto_id = a.id
                                AND e2.informe = 'Segundo informe académico de avance'
                            )) 
                      THEN 'Por presentar'
  
                      WHEN (a.tipo_proyecto = 'PTPDOCTO'
                            AND e.informe = 'Segundo informe académico de avance'
                            AND d.estado = 1
                            AND NOT EXISTS (
                                SELECT 1 FROM Informe_tecnico d2
                                JOIN Informe_tipo e2 ON d2.informe_tipo_id = e2.id
                                WHERE d2.proyecto_id = a.id
                                AND e2.informe = 'Informe académico final'
                            )) 
                      THEN 'Por presentar'
  
                      ELSE 
                          CASE (d.estado)
                              WHEN 0 THEN 'En proceso'
                              WHEN 1 THEN 'Aprobado'
                              WHEN 2 THEN 'Presentado'
                              WHEN 3 THEN 'Observado'
                              ELSE 'Por presentar'
                          END
                  END
               as estado")
        ])
        ->whereIn('a.estado', [1, 8])
        ->where('b.investigador_id', $request->attributes->get('token_decoded')->investigador_id);
    }, 'temp')
      ->whereIn('temp.estado', ['En proceso', 'Presentado', 'Observado', 'Por presentar'])
      ->get();

    return $informes;
  }

  public function listadoAceptados(Request $request) {
    $informes = DB::table('Informe_tipo as t1')
      ->join('Proyecto as t2', 't1.tipo', '=', 't2.tipo_proyecto')
      ->leftJoin('Informe_tecnico as t3', function (JoinClause $join) {
        $join->on('t3.proyecto_id', '=', 't2.id')
          ->on('t1.id', '=', 't3.informe_tipo_id');
      })
      ->leftJoin('Proyecto_integrante as t5', 't5.proyecto_id', '=', 't2.id')
      ->leftJoin('Informe_tecnico as t33', 't33.proyecto_id', '=', 't2.id')
      ->leftJoin('Informe_tipo as t11', 't11.id', '=', 't33.informe_tipo_id')
      ->select([
        't3.id AS id',
        't2.id AS proyecto_id',
        't2.codigo_proyecto',
        't2.titulo',
        't2.tipo_proyecto',
        't1.informe',
        't2.periodo',
        't3.fecha_presentacion',
        DB::raw("CASE(t3.estado)
          WHEN 0 THEN 'En proceso'
          WHEN 1 THEN 'Aprobado'
          WHEN 2 THEN 'Presentado'
          WHEN 3 THEN 'Observado'
        ELSE 'Por presentar' END AS estado")
      ])
      ->where(function ($query) {
        $query->whereIn(DB::raw('COALESCE(t5.condicion, t5.proyecto_integrante_tipo_id)'), ['Responsable', 'Asesor', 7]);
      })
      ->where('t3.estado', 1)
      ->whereIn('t2.estado', [1, 8])
      ->where('t2.periodo', '>', 2016)
      ->where('t5.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where(DB::raw("
        CASE
            WHEN t2.tipo_proyecto = 'PTPDOCTO' THEN
                IF (t1.informe = 'Segundo informe académico de avance',
                    IF (t11.informe = 'Informe académico de avance' AND t33.estado = 1, true, false),
                true)
                AND
                IF (t1.informe = 'Informe académico final',
                    IF (t11.informe = 'Segundo informe académico de avance' AND t33.estado = 1, true, false),
                true)
            ELSE
                IF (t1.informe = 'Informe académico final' AND t1.tipo <> 'ptpgrado',
                    IF (t11.informe = 'Informe académico de avance' AND t33.estado = 1, true, false),
                true)
        END
    "), true)
      ->groupBy('t2.id')
      ->groupBy('t1.id')
      ->get();

    return $informes;
  }
}

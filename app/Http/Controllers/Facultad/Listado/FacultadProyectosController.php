<?php

namespace App\Http\Controllers\Facultad\Listado;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Investigador\Convocatorias\PconfigiController;
use App\Http\Controllers\Investigador\Convocatorias\PinvposController;
use App\Http\Controllers\Investigador\Convocatorias\EciController;
use App\Http\Controllers\Investigador\Convocatorias\ProCTIController;
use App\Http\Controllers\Investigador\Convocatorias\PconfigiInvController;
use App\Http\Controllers\Investigador\Convocatorias\PicvController;
use App\Http\Controllers\Investigador\Convocatorias\PmultiController;
use App\Http\Controllers\Investigador\Convocatorias\PsinfinvController;
use App\Http\Controllers\Investigador\Convocatorias\PsinfipuController;
use Maatwebsite\Excel\Facades\Excel;

class FacultadProyectosController extends Controller {

  public function facultadId(Request $request) {
    $usuarioFacultadId = $request->attributes->get('token_decoded')->id;

    $facultadId = DB::table('Usuario_facultad')
    ->select('facultad_id')
    ->where('id', $usuarioFacultadId)
    ->first();

    return $facultadId ? $facultadId->facultad_id : null;
  }

  public function ListadoProyectos(Request $request) {
    $facultadId = $this->facultadId($request);

    $proyectos_nuevos = DB::table('Proyecto AS a')
    ->leftJoin('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
        ->where('condicion', '=', 'Responsable');
    })
    ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
    ->select([
        'a.id',
        DB::raw("'NUEVO' as origen"),
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        'a.periodo',
        'a.resolucion_rectoral',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inscripcion',
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
        ELSE 'Sin estado' END AS estado"),
    ])
    ->where('a.facultad_id', '=', $facultadId);

    $proyectos = DB::table('Proyecto_H AS a')
    ->leftJoin('Proyecto_integrante_H AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
        ->where('condicion', '=', 'Responsable');
    })
    ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
    ->select([
        'a.id',
        DB::raw("'HISTORICO' as origen"),
        'a.tipo AS tipo_proyecto',
        'a.codigo AS codigo_proyecto',
        'a.titulo',
        'a.periodo',
        'a.resolucion AS resolucion_rectoral',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        DB::raw("DATE(a.fecha_inscripcion) AS fecha_inscripcion"),
        DB::raw("CASE(a.status)
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
        ELSE 'Sin estado' END AS estado"),
    ])
    ->where('a.facultad_id', '=', $facultadId)
    ->union($proyectos_nuevos);
    
    $orden = DB::query()
    ->fromSub($proyectos, 'p')
    ->orderByDesc('periodo') 
    ->get();

    return $orden;
  }

public function listadoIntegrantes(Request $request)
{
    $id = $request->query('id');
    $origen = $request->query('origen');

    if ($origen === 'HISTORICO') {

        $integrantes = DB::table('Proyecto_integrante_H AS a')
            ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
            ->leftJoin('Licencia AS d', 'd.investigador_id', '=', 'c.id')
            ->leftJoin('Licencia_tipo AS e', 'e.id', '=', 'd.licencia_tipo_id')
            ->leftJoin('Grupo_integrante AS b', 'b.investigador_id', '=', 'c.id')
            ->leftJoin('Grupo AS g', 'g.id', '=', 'b.grupo_id')
            ->select(
                'a.id',
                'c.doc_numero',
                'c.apellido1',
                'c.apellido2',
                'c.nombres',
                'a.condicion',
                'e.tipo AS licencia',
                'g.grupo_nombre AS grupo'
            )
            ->where('a.proyecto_id', $id)
            ->where(function ($q) {
              $q->whereNull('b.condicion')
                ->orWhere('b.condicion', 'not like', 'Ex%');
              })
            ->orderBy('b.id', 'asc')
            ->get();

    } else {

      $integrantes = DB::table('Proyecto_integrante AS a')
          ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
          ->leftJoin('Licencia AS d', 'd.investigador_id', '=', 'c.id')
          ->leftJoin('Licencia_tipo AS e', 'e.id', '=', 'd.licencia_tipo_id')
          ->leftJoin('Proyecto_integrante_deuda AS f', 'f.proyecto_integrante_id', '=', 'a.id')
          ->leftJoin('Grupo AS g', 'g.id', '=', 'a.grupo_id')
          ->select(
              'a.id',
              'c.doc_numero',
              'c.apellido1',
              'c.apellido2',
              'c.nombres',
              'b.nombre AS condicion',
              'e.tipo AS licencia',
              'g.grupo_nombre AS grupo',
              'f.categoria AS tipo_deuda',
              'f.detalle AS comentario',
              'f.informe AS detalle',
              'f.fecha_deuda',
              'f.fecha_sub'
          )
          ->where('a.proyecto_id', $id)
          ->orderBy('b.id', 'asc')
          ->get();
    }

    return $integrantes;
  }

  public function ListadoProyectosFEX(Request $request) {

    $facultadId = $this->facultadId($request);

    // Subquery for the 'responsable' field
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    // Subquery for 'Proyecto_descripcion' fields based on 'codigo' value
    $projectDescriptions = function ($code) {
      return DB::table('Proyecto_descripcion')
        ->select('proyecto_id', 'detalle')
        ->where('codigo', '=', $code);
    };

    // Main query with simplified joins
    $proyectos = DB::table('Proyecto AS a')
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
      ->where('a.facultad_id', $facultadId)
      ->where('a.estado', '!=', -1)
      ->orderByDesc('a.periodo')
      ->get();

    return $proyectos;
  }

  public function reporte(Request $request)
  {

    $request->merge([
      'proyecto_id' => $request->query('id')
    ]);

    $tipo = $request->query('tipo_proyecto');

    switch ($tipo) {
      case 'PCONFIGI':
        return app(PconfigiController::class)->reporte($request);
      case 'PINVPOS':
        return app(PinvposController::class)->reporte($request);
      case 'PRO-CTIE':
        return app(ProCTIController::class)->reportePDF($request);
      case 'ECI':
        return app(EciController::class)->reporte($request);
      case 'PCONFIGI-INV':
        return app(PconfigiInvController::class)->reporte($request);
      case 'PICV':
        return app(PicvController::class)->reporte($request);
      case 'PMULTI':
        return app(PmultiController::class)->reporte($request);
      case 'PSINFINV':
        return app(PsinfinvController::class)->reporte($request);
      case 'PSINFIPU':
        return app(PsinfipuController::class)->reporte($request);

      default:
          return response()->json([
              'estado' => false,
              'message' => 'Tipo de proyecto no válido'
          ], 400);
    }
  }
};
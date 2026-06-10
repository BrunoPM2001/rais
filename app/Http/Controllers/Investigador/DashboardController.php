<?php

namespace App\Http\Controllers\Investigador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investigador\Perfil\OrcidController;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {

  private function contarDeudasVigentes($investigadorId)
  {
    $nuevos = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
        ->where('a.investigador_id', $investigadorId)
        ->whereIn('b.tipo', [1,2,3])
        ->count();

    $antiguos = DB::table('Proyecto_integrante_H AS a')
        ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_h_id', '=', 'a.id')
        ->where('a.investigador_id', $investigadorId)
        ->whereIn('b.tipo', [1,2,3])
        ->count();

    return $nuevos + $antiguos;
  }

  public function getData(Request $request) {
    $now = Carbon::now()->toDateString();
    // Deudas
    $deudasVigentes = $this->contarDeudasVigentes($request->attributes->get('token_decoded')->investigador_id);

    //  Detalles
    $orcid = new OrcidController();
    $isOrcidValid = $orcid->validarRegistro($request);

    $publicacionesEnProceso = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.publicacion_id')
          ->where('b.presentado', '=', 1);
      })
      ->where('a.estado', '=', 6)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    //  Métricas
    $grupos = DB::table('Grupo_integrante')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->count();

    $proyectosActuales = DB::table('Proyecto_integrante AS pi')
      ->join('Proyecto AS p', 'p.id', '=', 'pi.proyecto_id')
      ->where('pi.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('p.estado', [1, 8])
      ->count();

    $proyectosHistoricos = DB::table('Proyecto_integrante_H AS pi')
      ->join('Proyecto_H AS p', 'p.id', '=', 'pi.proyecto_id')
      ->where('pi.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('p.status', 1)
      ->count();

    $proyectos = $proyectosActuales + $proyectosHistoricos;

    $dj = DB::table('Proyecto as px')
      ->join('Proyecto_integrante as pix', 'px.id', '=', 'pix.proyecto_id')
      ->select(
        'px.dj_aceptada',
        'px.id',
      )
      ->where('pix.condicion', '=', 'Responsable')
      ->where('pix.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('px.estado', '=', 1)
      ->where('px.tipo_proyecto', '=', 'PCONFIGI')
      ->where('px.periodo', '=', 2025)
      ->first();


    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'a.id', '=', 'b.publicacion_id')
      ->select(
        '*'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $puntajePublicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'a.id', '=', 'b.publicacion_id')
      ->where('a.estado', '=', 1)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->sum('b.puntaje');

    $puntajePatentes = DB::table('Patente AS a')
      ->leftJoin('Patente_autor AS b', 'a.id', '=', 'b.patente_id')
      ->where('a.estado', '=', 1)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->sum('b.puntaje');

    $puntaje = $puntajePublicaciones + $puntajePatentes;

    $puntaje_pasado = DB::table('view_puntaje_7u')
      ->select(
        'puntaje'
      )
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first()?->puntaje ?? 0;

    //  Tipos de publicación
    $tipos1 = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'a.id', '=', 'b.publicacion_id')
      ->select(
        'a.tipo_publicacion AS title',
        DB::raw('COUNT(*) AS value')
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->groupBy('a.tipo_publicacion')
      ->get();

    //  Tipos de proyectos
    $tipos2 = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'a.id', '=', 'b.proyecto_id')
      ->select(
        'a.tipo_proyecto AS title',
        DB::raw('COUNT(*) AS cuenta')
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->groupBy('a.tipo_proyecto')
      ->get();

    $const = DB::table('Eval_docente_investigador')
      ->select([
        DB::raw('DATE(fecha_fin) AS fecha_fin')
      ])
      ->where('tipo_eval', '=', 'Constancia')
      ->where('estado', '=', 'Vigente')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->orderByDesc('fecha_fin')
      ->first();

    if ($const) {
      $fecha1 = Carbon::now()->addMonths(2);
      $fecha2 = Carbon::parse($const->fecha_fin);
    }

    $convocatorias = DB::table('Convocatoria')
      ->select([
        'tipo',
        'descripcion',
        DB::raw("CASE
          WHEN fecha_inicial <= '" . $now . "' && fecha_final >= '" . $now . "' THEN 'Abierta'
          ELSE 'Cerrada'
        END AS estado")
      ])
      ->where('evento', '=', 'registro')
      ->where('convocatoria', '=', 1)
      ->get();

    return [
      'detalles' => [
        'orcid' => $isOrcidValid,
        'publicacionesProceso' => $publicacionesEnProceso,
        'convocatorias' => $convocatorias
      ],
      'metricas' => [
        'grupos' => $grupos,
        'proyectos' => $proyectos,
        'publicaciones' => $publicaciones,
        'puntaje' => $puntaje,
        'puntaje_pasado' => $puntaje_pasado,
        'deudas_vigentes' => $deudasVigentes,
      ],
      'tipos_publicaciones' => $tipos1,
      'tipos_proyectos' => $tipos2,
      'dj' => $dj?->dj_aceptada,
      'proyecto_id' => $dj?->id,
      'alerta' => $const ? $fecha1->greaterThan($fecha2) : false,
      'convocatorias' => $convocatorias
    ];
  }
}

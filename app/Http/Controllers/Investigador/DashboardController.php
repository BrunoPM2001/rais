<?php

namespace App\Http\Controllers\Investigador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investigador\Perfil\OrcidController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {

  public function getData(Request $request) {
    //  Detalles
    $orcid = new OrcidController();
    $isOrcidValid = $orcid->validarRegistro($request);

    //  Métricas
    $grupos = DB::table('Grupo_integrante')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->count();

    $proyectos = DB::table('Proyecto_integrante')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'a.id', '=', 'b.publicacion_id')
      ->select(
        '*'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $puntaje = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'a.id', '=', 'b.publicacion_id')
      ->select(
        DB::raw('SUM(b.puntaje) AS puntaje')
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first()->puntaje;

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

    $now = Carbon::now();

    $cuenta = DB::table('Eval_docente_investigador')
      ->where('tipo_eval', '=', 'Constancia')
      ->where('estado', '=', 'Vigente')
      ->where(DB::raw('DATE(fecha_fin)'), '<', $now->addMonths(2))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    return [
      'detalles' => [
        'orcid' => $isOrcidValid
      ],
      'metricas' => [
        'grupos' => $grupos,
        'proyectos' => $proyectos,
        'publicaciones' => $publicaciones,
        'puntaje' => $puntaje,
        'puntaje_pasado' => $puntaje_pasado,
      ],
      'tipos_publicaciones' => $tipos1,
      'tipos_proyectos' => $tipos2,
      'alerta' => $cuenta
    ];
  }
}

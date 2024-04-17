<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
  public function metricas() {

    $grupos = DB::table('Grupo')
      ->where('estado', '=', 4)
      ->count();

    $investigadores = DB::table('Usuario_investigador')
      ->count();

    $publicaciones = DB::table('Publicacion')
      ->count();

    $proyectos = DB::table('Proyecto')
      ->whereNotNull('tipo_proyecto')
      ->count();

    return [
      'grupos' => $grupos,
      'investigadores' => $investigadores,
      'publicaciones' => $publicaciones,
      'proyectos' => $proyectos
    ];
  }

  public function proyectos($periodo) {
    $proyectos = DB::table('Proyecto')
      ->select(
        'tipo_proyecto AS title',
        DB::raw('COUNT(*) AS value')
      )
      ->where('periodo', '=', $periodo)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->groupBy('tipo_proyecto')
      ->get();

    return ['data' => $proyectos];
  }

  public function proyectosHistoricoData() {
    $tipos = DB::table('Proyecto')
      ->select(
        'tipo_proyecto'
      )
      ->where('periodo', '>', 2016)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->groupBy('tipo_proyecto')
      ->get();

    $countExp = [];

    foreach ($tipos as $tipo) {
      $countExp[] = DB::raw('COUNT(IF(tipo_proyecto = "' . $tipo->tipo_proyecto . '", 1, NULL)) AS "' . $tipo->tipo_proyecto . '"');
    }

    $cuenta =  DB::table('Proyecto')
      ->select(
        'periodo',
        ...$countExp,
      )
      ->where('periodo', '>', 2016)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->groupBy('periodo')
      ->orderBy('periodo')
      ->get();

    return ['tipos' => $tipos, 'cuenta' => $cuenta];
  }
}

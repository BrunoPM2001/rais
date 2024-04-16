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

  public function proyectosHistoricoData() {
    $tipos = DB::table('Proyecto')
      ->select(
        'tipo_proyecto'
      )
      ->groupBy('tipo_proyecto')
      ->get();

    $cuenta =  DB::table('Proyecto')
      ->select(
        'periodo',
        'tipo_proyecto',
        DB::raw('COUNT(*) AS cuenta')
      )
      ->where('periodo', '>', 2016)
      ->whereNotNull('periodo')
      ->whereNotNull('tipo_proyecto')
      ->groupBy('tipo_proyecto', 'periodo')
      ->orderBy('periodo')
      ->get();

    return ['tipos' => $tipos, 'cuenta' => $cuenta];
  }
}

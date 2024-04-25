<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TesisAsesoriaController extends Controller {

  public function listado(Request $request) {
    $patentes = DB::table('Patente AS a')
      ->get();

    return ['data' => $patentes];
  }
}

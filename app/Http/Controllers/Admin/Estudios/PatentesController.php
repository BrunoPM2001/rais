<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicacionesController extends S3Controller {
  public function detalle(Request $request) {
    DB::table('Patente')
      ->select([
        'nro_registro',
        'titulo',
        'tipo',
        'nro_expediente',
        'fecha_presentacion',
        'oficina_presentacion',
        'enlace',
        ''
      ])
      ->where('id', '=', $request->query('id'))
      ->first();
  }
}

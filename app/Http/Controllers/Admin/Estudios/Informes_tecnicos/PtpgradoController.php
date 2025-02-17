<?php

namespace App\Http\Controllers\Admin\Estudios\Informes_tecnicos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtpgradoController extends Controller {
  public function getData(Request $request) {
    $informe = DB::table('Informe_tecnico')
      ->select([
        'proyecto_id'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $proyecto = DB::table('Proyecto AS a')
      ->select([
        ''
      ])
      ->where('a.id', '=', $informe->proyecto_id)
      ->first();
  }
}

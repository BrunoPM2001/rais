<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinvposController extends S3Controller {
  public function verificar(Request $request) {
    $errores = [];

    $habilitado = DB::table('Proyecto_integrante_dedicado AS a')
      ->leftJoin('Proyecto AS b', 'a.proyecto_id', '=', 'b.id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        'b.id AS proyecto_id',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS responsable"),
        'c.doc_numero',
        'c.email3',
        'd.nombre AS facultad',
        'c.codigo',
        'c.tipo'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    if (!$habilitado) {
      $errores[] = 'Esta convocatoria no está disponible para usted';
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      return ['estado' => true, 'datos' => $habilitado];
    }
  }
}

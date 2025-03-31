<?php

namespace App\Http\Controllers\Secretaria\Constancias;

use App\Http\Controllers\S3Controller;
use App\Mail\Secretaria\Constancias\ConstanciaFirmada;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ConstanciasController extends S3Controller {
  public function listado() {
    $constancias = DB::table('Constancia AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.id',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        'a.tipo',
        DB::raw("CONCAT('/minio/constancias/', a.archivo_original) AS archivo_original"),
        DB::raw("CONCAT('/minio/constancias/', a.archivo_firmado) AS archivo_firmado"),
        DB::raw("CASE(a.estado)
          WHEN 1 THEN 'Firmado'
          ELSE 'Pendiente'
        END AS estado"),
        'a.created_at',
        'a.updated_at'
      ])
      ->get();

    return $constancias;
  }

  public function cargarDocumento(Request $request) {
    if ($request->hasFile('file')) {
      $constancia = DB::table('Constancia AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.archivo_firmado',
          DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
          'a.tipo',
          'b.email3',
        ])
        ->where('a.id', '=', $request->input('id'))
        ->first();

      $date = Carbon::now();
      $name = $constancia->archivo_firmado;
      $this->uploadFile($request->file('file'), "constancias", $name);

      DB::table('Constancia')
        ->where('id', '=', $request->input('id'))
        ->update([
          'estado' => 1,
          'updated_at' => $date
        ]);

      $file = $this->getFile('constancias', $constancia->archivo_firmado);

      Mail::to($constancia->email3)->cc('jninom@unmsm.edu.pe')->send(new ConstanciaFirmada($constancia->nombres, $constancia->tipo, $file));

      return ['message' => 'success', 'detail' => 'Constancia guardada y enviada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }
}

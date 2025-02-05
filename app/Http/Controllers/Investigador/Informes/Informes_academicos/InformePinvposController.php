<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformePinvposController extends S3Controller {
  public function getData(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'd.facultad_id')
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'a.resolucion_rectoral',
        'a.periodo',
        'e.nombre AS facultad',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ' ', d.nombres) AS responsable")
      ])
      ->where('a.id', '=', $request->get('proyecto_id'))
      ->first();

    $informe = DB::table('Informe_tecnico')
      ->select([
        'id',
        'objetivos_taller',
        'fecha_evento',
        'propuestas_taller',
        'conclusion_taller',
        'recomendacion_taller',
        'asistencia_taller',
        'observaciones',
        'estado'
      ])
      ->where('proyecto_id', '=', $request->get('proyecto_id'))
      ->first();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->get('proyecto_id'))
      ->where('nombre', '=', 'Anexos proyecto ECI')
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });

    return ['proyecto' => $proyecto, 'informe' => $informe, 'archivos' => $archivos];
  }

  public function sendData(Request $request) {
    $date = Carbon::now();

    $count = DB::table('Informe_tecnico')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->count();

    if ($count == 0) {
      DB::table('Informe_tecnico')
        ->updateOrInsert([
          'proyecto_id' => $request->input('proyecto_id')
        ], [
          'informe_tipo_id' => 43,
          'objetivos_taller' => $request->input('objetivos_taller'),
          'fecha_evento' => $request->input('fecha_evento'),
          'propuestas_taller' => $request->input('propuestas_taller'),
          'conclusion_taller' => $request->input('conclusion_taller'),
          'recomendacion_taller' => $request->input('recomendacion_taller'),
          'asistencia_taller' => $request->input('asistencia_taller'),
          'estado' => 0,
          'fecha_informe_tecnico' => $date,
          'created_at' => $date,
          'updated_at' => $date,
        ]);
    } else {
      DB::table('Informe_tecnico')
        ->updateOrInsert([
          'proyecto_id' => $request->input('proyecto_id')
        ], [
          'informe_tipo_id' => 43,
          'objetivos_taller' => $request->input('objetivos_taller'),
          'fecha_evento' => $request->input('fecha_evento'),
          'propuestas_taller' => $request->input('propuestas_taller'),
          'conclusion_taller' => $request->input('conclusion_taller'),
          'recomendacion_taller' => $request->input('recomendacion_taller'),
          'asistencia_taller' => $request->input('asistencia_taller'),
          'estado' => 0,
          'fecha_informe_tecnico' => $date,
          'updated_at' => $date,
        ]);
    }

    $proyecto_id = $request->input('proyecto_id');
    $date1 = Carbon::now();

    return ['message' => 'success', 'detail' => 'Informe guardado correctamente'];
  }

  public function presentar(Request $request) {
    $count1 = DB::table('Informe_tecnico')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->whereNotNull('objetivos_taller')
      ->whereNotNull('fecha_evento')
      ->whereNotNull('propuestas_taller')
      ->whereNotNull('conclusion_taller')
      ->whereNotNull('recomendacion_taller')
      ->whereNotNull('asistencia_taller')
      ->count();

    if ($count1 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita completar todos los campos'];
    }

    $count = DB::table('Informe_tecnico')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('estado', '=', 0)
      ->update([
        'estado' => 2,
        'fecha_envio' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    if ($count == 0) {
      return ['message' => 'warning', 'detail' => 'Necesita guardar el informe para enviarlo'];
    } else {
      return ['message' => 'info', 'detail' => 'Informe enviado correctamente'];
    }
  }

  public function updateFile($proyecto_id, $date, $name, $categoria) {
    DB::table('Proyecto_doc')
      ->where('proyecto_id', '=', $proyecto_id)
      ->where('categoria', '=', $categoria)
      ->where('nombre', '=', 'Anexos proyecto ECI')
      ->update([
        'estado' => 0
      ]);

    DB::table('Proyecto_doc')
      ->insert([
        'proyecto_id' => $proyecto_id,
        'categoria' => $categoria,
        'tipo' => 21,
        'nombre' => 'Anexos proyecto ECI',
        'comentario' => $date,
        'archivo' => $name,
        'estado' => 1
      ]);
  }
}

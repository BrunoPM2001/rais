<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;

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
      ->where('nombre', '=', 'Anexo Proyecto PINVPOS')
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
      $investigador = DB::table('Usuario_investigador')
        ->select([
          DB::raw("CONCAT(apellido1, ' ', apellido2) AS apellidos"),
          'nombres'
        ])
        ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      $audit[] = [
        'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
        'nombres' => $investigador->nombres,
        'apellidos' => $investigador->apellidos,
        'accion' => 'Creación de informe'
      ];

      $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

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
          'audit' => $audit,
          'created_at' => $date,
          'updated_at' => $date,
        ]);
    } else {
      $inf = DB::table('Informe_tecnico')
        ->select([
          'audit',
          'estado'
        ])
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->first();

      $investigador = DB::table('Usuario_investigador')
        ->select([
          DB::raw("CONCAT(apellido1, ' ', apellido2) AS apellidos"),
          'nombres'
        ])
        ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      $audit = json_decode($inf->audit ?? "[]");

      $audit[] = [
        'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
        'nombres' => $investigador->nombres,
        'apellidos' => $investigador->apellidos,
        'accion' => 'Actualización de información'
      ];

      $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

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
          'estado' => $inf->estado == 3 ? 3 : 0,
          'fecha_informe_tecnico' => $date,
          'audit' => $audit,
          'updated_at' => $date,
        ]);
    }

    $proyecto_id = $request->input('proyecto_id');
    $date1 = Carbon::now();

    if ($request->hasFile('file2')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
      $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "asistencia");
    }

    if ($request->hasFile('file1')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo1");
    }

    return ['message' => 'success', 'detail' => 'Informe guardado correctamente'];
  }

  public function presentar(Request $request) {
    $informe = DB::table('Informe_tecnico')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->first();

    $campos = [
      'objetivos_taller' => 'Objetivos',
      'fecha_evento' => 'Fecha del evento',
      'propuestas_taller' => 'Programa del taller',
      'conclusion_taller' => 'Conclusiones',
      'recomendacion_taller' => 'Recomendaciones',
      'asistencia_taller' => 'Asistencia',
    ];

    $faltantes = [];

    foreach ($campos as $campo => $nombre) {
      if (empty($informe->$campo)) {
        $faltantes[] = $nombre;
      }
    }

    $anexosObligatorios = ['anexo1'];

    $faltantesArchivos = [];

    foreach ($anexosObligatorios as $anexo) {

      $existe = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('categoria', '=', $anexo)
        ->where('nombre', '=', 'Anexo Proyecto PINVPOS')
        ->where('estado', '=', 1)
        ->exists();

      if (!$existe) {
        $faltantesArchivos[] = $anexo;
      }
    }

    $nombres = [
      'anexo1' => 'Anexos',
    ];

    foreach ($faltantesArchivos as $f) {
      $faltantes[] = $nombres[$f];
    }

    if (count($faltantes) > 0) {
      return [
        'message' => 'error',
        'detail' => 'Faltan completar los siguientes apartados',
        'faltantes' => $faltantes
      ];
    }

    $inf = DB::table('Informe_tecnico')
      ->select([
        'audit',
        'estado'
      ])
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->first();

    $investigador = DB::table('Usuario_investigador')
      ->select([
        DB::raw("CONCAT(apellido1, ' ', apellido2) AS apellidos"),
        'nombres'
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $audit = json_decode($inf->audit ?? "[]");

    $audit[] = [
      'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
      'nombres' => $investigador->nombres,
      'apellidos' => $investigador->apellidos,
      'accion' => 'Presentación del informe'
    ];

    $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

    $count = DB::table('Informe_tecnico')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->whereIn('estado', [0, 3])
      ->update([
        'estado' => 2,
        'audit' => $audit,
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
      ->where('nombre', '=', 'Anexo Proyecto PINVPOS')
      ->update([
        'estado' => 0
      ]);

    DB::table('Proyecto_doc')
      ->insert([
        'proyecto_id' => $proyecto_id,
        'categoria' => $categoria,
        'tipo' => 21,
        'nombre' => 'Anexo Proyecto PINVPOS',
        'comentario' => $date,
        'archivo' => $name,
        'estado' => 1
      ]);
  }

  public function reporte(Request $request) {
    $detalles = DB::table('Informe_tecnico AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id AS proyecto_id',
        'b.codigo_proyecto',
        'b.tipo_proyecto',
        DB::raw("COALESCE(a.fecha_registro_csi, a.fecha_envio) AS fecha_estado"),
        'a.*',
      ])
      ->where('a.id', '=', $request->query('informe_tecnico_id'))
      ->first();

    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Proyecto_integrante AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.condicion', '=', 'Responsable');
      })
      ->leftJoin('Usuario_investigador AS e', 'e.id', '=', 'd.investigador_id')
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'b.nombre AS facultad',
        'c.grupo_nombre',
        DB::raw("CONCAT(e.apellido1, ' ', e.apellido2, ', ', e.nombres) AS responsable"),
        'a.resolucion_rectoral',
      ])
      ->where('a.id', '=', $detalles->proyecto_id)
      ->first();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $detalles->proyecto_id)
      ->where('nombre', '=', 'Anexo Proyecto PINVPOS')
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });

    $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.pinvpos', [
      'proyecto' => $proyecto,
      'archivos' => $archivos,
      'detalles' => $detalles,
      'informe' => $request->query('tipo_informe')
    ]);

    return $pdf->stream();
  }
}

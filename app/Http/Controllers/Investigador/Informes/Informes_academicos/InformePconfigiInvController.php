<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformePconfigiInvController extends S3Controller {
  public function getData(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->leftJoin('Grupo_integrante AS c', function (JoinClause $join) {
        $join->on('c.grupo_id', '=', 'b.id')
          ->where('cargo', '=', 'Coordinador');
      })
      ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'a.facultad_id')
      ->leftJoin('Proyecto_descripcion AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'a.id')
          ->where('f.codigo', '=', 'tipo_investigacion');
      })
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'a.resolucion_rectoral',
        'a.periodo',
        'b.grupo_nombre',
        'a.localizacion',
        'e.nombre AS facultad',
        'd.nombre AS linea',
        'f.detalle AS tipo_investigacion'
      ])
      ->where('a.id', '=', $request->get('proyecto_id'))
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select([
        'a.id',
        'b.nombre AS condicion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres")
      ])
      ->where('a.proyecto_id', '=', $request->get('proyecto_id'))
      ->get();

    $informe = DB::table('Informe_tecnico')
      ->select([
        'id',
        'resumen_ejecutivo',
        'palabras_clave',
        'infinal1',
        'infinal2',
        'infinal3',
        'infinal4',
        'infinal5',
        'infinal6',
        'infinal7',
        'infinal9',
        'infinal10',
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
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });

    return ['proyecto' => $proyecto, 'miembros' => $miembros, 'informe' => $informe, 'archivos' => $archivos];
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
          'informe_tipo_id' => 45,
          'resumen_ejecutivo' => $request->input('resumen_ejecutivo'),
          'palabras_clave' => $request->input('palabras_clave'),
          'infinal1' => $request->input('infinal1'),
          'infinal2' => $request->input('infinal2'),
          'infinal3' => $request->input('infinal3'),
          'infinal4' => $request->input('infinal4'),
          'infinal5' => $request->input('infinal5'),
          'infinal6' => $request->input('infinal6'),
          'infinal7' => $request->input('infinal7'),
          'infinal9' => $request->input('infinal9'),
          'infinal10' => $request->input('infinal10'),
          'estado' => 0,
          'fecha_informe_tecnico' => $date,
          'audit' => $audit,
          'created_at' => $date,
          'updated_at' => $date,
        ]);
    } else {
      $inf = DB::table('Informe_tecnico')
        ->select([
          'audit'
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
          'informe_tipo_id' => 45,
          'resumen_ejecutivo' => $request->input('resumen_ejecutivo'),
          'palabras_clave' => $request->input('palabras_clave'),
          'infinal1' => $request->input('infinal1'),
          'infinal2' => $request->input('infinal2'),
          'infinal3' => $request->input('infinal3'),
          'infinal4' => $request->input('infinal4'),
          'infinal5' => $request->input('infinal5'),
          'infinal6' => $request->input('infinal6'),
          'infinal7' => $request->input('infinal7'),
          'infinal9' => $request->input('infinal9'),
          'infinal10' => $request->input('infinal10'),
          'estado' => 0,
          'fecha_informe_tecnico' => $date,
          'updated_at' => $date,
        ]);
    }

    $proyecto_id = $request->input('proyecto_id');
    $date1 = Carbon::now();

    if ($request->hasFile('file1')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "informe-PCONFIGI-INV-INFORME", "Archivos de informe", 22);
    }

    return ['message' => 'success', 'detail' => 'Informe guardado correctamente'];
  }

  public function presentar(Request $request) {
    $count1 = DB::table('Informe_tecnico')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->whereNotNull('resumen_ejecutivo')
      ->whereNotNull('infinal1')
      ->whereNotNull('infinal2')
      ->whereNotNull('infinal3')
      ->whereNotNull('infinal4')
      ->whereNotNull('infinal5')
      ->count();

    if ($count1 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita completar los campos de: Resumen, proceso de instalación, funcionamiento, gestión de uso, aplicación práctica e impacto, e impacto de uso.'];
    }

    $inf = DB::table('Informe_tecnico')
      ->select([
        'audit'
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
      ->where('estado', '=', 0)
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

  public function updateFile($proyecto_id, $date, $name, $categoria, $nombre, $tipo) {
    DB::table('Proyecto_doc')
      ->where('proyecto_id', '=', $proyecto_id)
      ->where('categoria', '=', $categoria)
      ->where('nombre', '=', $nombre)
      ->update([
        'estado' => 0
      ]);

    DB::table('Proyecto_doc')
      ->insert([
        'proyecto_id' => $proyecto_id,
        'categoria' => $categoria,
        'tipo' => $tipo,
        'nombre' => $nombre,
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
        DB::raw("COALESCE(a.fecha_registro_csi, a.fecha_envio, a.updated_at) AS fecha_estado"),
        'a.*',
      ])
      ->where('a.id', '=', $request->query('informe_tecnico_id'))
      ->first();

    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_descripcion AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.id')
          ->where('e.codigo', '=', 'tipo_investigacion');
      })
      ->select([
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.resolucion_rectoral',
        'a.periodo',
        'c.grupo_nombre',
        'a.localizacion',
        'b.nombre AS facultad',
        'd.nombre AS linea',
        'e.detalle AS tipo_investigacion',
      ])
      ->where('a.id', '=', $detalles->proyecto_id)
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'b.codigo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ' ', b.nombres) AS nombres"),
        'c.nombre AS condicion',
        'b.tipo'
      ])
      ->where('a.proyecto_id', '=', $detalles->proyecto_id)
      ->get();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $detalles->proyecto_id)
      ->where('estado', '=', 1)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url];
      });

    $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.pconfigi_inv', [
      'proyecto' => $proyecto,
      'miembros' => $miembros,
      'archivos' => $archivos,
      'detalles' => $detalles,
      'informe' => $request->query('tipo_informe')
    ]);
    return $pdf->stream();
  }
}

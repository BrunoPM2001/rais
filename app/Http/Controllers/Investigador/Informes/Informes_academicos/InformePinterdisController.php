<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformePinterdisController extends S3Controller {
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

    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        DB::raw("ROW_NUMBER() OVER (ORDER BY a.id desc) AS indice"),
        'a.id',
        'a.actividad',
        'a.justificacion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->get('proyecto_id'))
      ->get();

    return ['proyecto' => $proyecto, 'miembros' => $miembros, 'informe' => $informe, 'archivos' => $archivos, 'actividades' => $actividades];
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
          'informe_tipo_id' => 48,
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
          'informe_tipo_id' => 48,
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
          'audit' => $audit,
          'fecha_informe_tecnico' => $date,
          'updated_at' => $date,
        ]);
    }

    $proyecto_id = $request->input('proyecto_id');
    $date1 = Carbon::now();
    $date_format =  $date1->format('Ymd-His');

    if ($request->hasFile('file1')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "informe-PINTERDIS-INFORME", "Archivos de informe", 22);
    }

    if ($request->hasFile('file2')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
      $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "articulo1", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
    }

    if ($request->hasFile('file3')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file3')->getClientOriginalExtension();
      $this->uploadFile($request->file('file3'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "articulo2", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
    }

    if ($request->hasFile('file4')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file4')->getClientOriginalExtension();
      $this->uploadFile($request->file('file4'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "tesis1", "Tesis sustentadas Pregrado.", 65);
    }

    if ($request->hasFile('file5')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file5')->getClientOriginalExtension();
      $this->uploadFile($request->file('file5'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "tesis2", "Tesis sustentadas Pregrado.", 65);
    }

    if ($request->hasFile('file6')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file6')->getClientOriginalExtension();
      $this->uploadFile($request->file('file6'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "tesis3", "Tesis sustentadas Pregrado.", 65);
    }

    if ($request->hasFile('file7')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file7')->getClientOriginalExtension();
      $this->uploadFile($request->file('file7'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "tesis4", "Tesis sustentadas Posgrado", 65);
    }

    if ($request->hasFile('file8')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file8')->getClientOriginalExtension();
      $this->uploadFile($request->file('file8'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "investigacion1", "Trabajos de investigación para obtener el grado de bachiller.", 65);
    }

    if ($request->hasFile('file9')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file9')->getClientOriginalExtension();
      $this->uploadFile($request->file('file9'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "investigacion2", "Trabajos de investigación para obtener el grado de bachiller.", 65);
    }

    if ($request->hasFile('file10')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file10')->getClientOriginalExtension();
      $this->uploadFile($request->file('file10'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "investigacion3", "Trabajos de investigación para obtener el grado de bachiller.", 65);
    }

    if ($request->hasFile('file11')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file11')->getClientOriginalExtension();
      $this->uploadFile($request->file('file11'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "investigacion4", "Trabajos de investigación para obtener el grado de bachiller.", 65);
    }

    if ($request->hasFile('file12')) {
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file12')->getClientOriginalExtension();
      $this->uploadFile($request->file('file12'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date_format, $name, "registro", "Formación de una red científica o el registro y/o inscripción al menos de una solicitud", 65);
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
}

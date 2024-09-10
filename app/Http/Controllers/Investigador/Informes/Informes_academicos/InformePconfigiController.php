<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformePconfigiController extends S3Controller {
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
        'infinal1',
        'infinal2',
        'infinal3',
        'infinal4',
        'infinal5',
        'infinal6',
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
      });;

    return ['proyecto' => $proyecto, 'miembros' => $miembros, 'informe' => $informe, 'archivos' => $archivos];
  }

  public function sendData(Request $request) {
    $date = Carbon::now();

    DB::table('Informe_tecnico')
      ->updateOrInsert([
        'proyecto_id' => $request->input('proyecto_id')
      ], [
        'informe_tipo_id' => 35,
        'resumen_ejecutivo' => $request->input('resumen_ejecutivo'),
        'infinal1' => $request->input('infinal1'),
        'infinal2' => $request->input('infinal2'),
        'infinal3' => $request->input('infinal3'),
        'infinal4' => $request->input('infinal4'),
        'infinal5' => $request->input('infinal5'),
        'infinal6' => $request->input('infinal6'),
        'estado' => 0,
        'fecha_informe_tecnico' => $date,
        'created_at' => $date,
        'updated_at' => $date,
      ]);

    $proyecto_id = $request->input('proyecto_id');
    $date1 = Carbon::now();

    if ($request->hasFile('file1')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo1");
    }

    if ($request->hasFile('file2')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
      $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo2");
    }

    if ($request->hasFile('file3')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file3')->getClientOriginalExtension();
      $this->uploadFile($request->file('file3'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo3");
    }

    if ($request->hasFile('file4')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file4')->getClientOriginalExtension();
      $this->uploadFile($request->file('file4'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo4");
    }

    if ($request->hasFile('file5')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file5')->getClientOriginalExtension();
      $this->uploadFile($request->file('file5'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo5");
    }

    if ($request->hasFile('file6')) {
      $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file6')->getClientOriginalExtension();
      $this->uploadFile($request->file('file6'), "proyecto-doc", $name);
      $this->updateFile($proyecto_id, $date1, $name, "anexo6");
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

    $count2 = DB::table('Proyecto_doc')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('categoria', '=', 'anexo1')
      ->where('nombre', '=', 'Anexos proyecto ECI')
      ->where('estado', '=', 1)
      ->count();

    if ($count2 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita cargar al menos el primer anexo'];
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
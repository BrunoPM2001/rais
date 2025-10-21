<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformePtpmaestController extends S3Controller {
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

    $informe = DB::table('Informe_tecnico AS a')
      ->join('Informe_tipo AS b', 'a.informe_tipo_id', '=', 'b.id')
      ->select([
        'a.id',
        'a.estado_trabajo',
        'a.infinal1',
        'a.infinal2',
        'a.infinal3',
        'a.infinal7',
        'observaciones',
        'a.estado',
      ])
      ->where('a.proyecto_id', '=', $request->get('id'))
      ->where('b.informe', '=', $request->get('informe'))
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
    $id = $request->input('id');
    $date = Carbon::now();
    $proyecto_id = $request->input('proyecto_id');
    $date1 = Carbon::now();

    if ($request->input('informe') == "Informe académico de avance") {
      $existe = DB::table('Informe_tecnico')
        ->where('proyecto_id', $request->input('proyecto_id'))
        ->where('informe_tipo_id', 39)
        ->where('id', '=', $request->input('id'))
        ->first();

      if ($existe) {
        DB::table('Informe_tecnico')
          ->where('proyecto_id', $request->input('proyecto_id'))
          ->where('informe_tipo_id', 39)
          ->where('id', '=', $request->input('id'))
          ->update([
            'estado_trabajo' => $request->input('estado_trabajo'),
            'infinal1' => $request->input('infinal1'),
            'infinal2' => $request->input('infinal2'),
            'infinal3' => $request->input('infinal3'),
            'infinal7' => $request->input('infinal7'),
            'estado' => 0,
            'fecha_informe_tecnico' => $date,
            'updated_at' => $date,
          ]);
      } else {
        $id = DB::table('Informe_tecnico')
          ->insertGetId([
            'proyecto_id' => $request->input('proyecto_id'),
            'informe_tipo_id' => 39,
            'estado_trabajo' => $request->input('estado_trabajo'),
            'infinal1' => $request->input('infinal1'),
            'infinal2' => $request->input('infinal2'),
            'infinal3' => $request->input('infinal3'),
            'infinal7' => $request->input('infinal7'),
            'estado' => 0,
            'fecha_informe_tecnico' => $date,
            'created_at' => $date,
            'updated_at' => $date,
          ]);
      }

      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PTPMAEST-INFORME-AVANCE", "Archivos de informe", 22);
      }
    } else {
      $existe = DB::table('Informe_tecnico')
        ->where('proyecto_id', $request->input('proyecto_id'))
        ->where('informe_tipo_id', 40)
        ->where('id', '=', $request->input('id'))
        ->first();

      if ($existe) {
        DB::table('Informe_tecnico')
          ->where('proyecto_id', $request->input('proyecto_id'))
          ->where('informe_tipo_id', 40)
          ->where('id', '=', $request->input('id'))
          ->update([
            'estado_trabajo' => $request->input('estado_trabajo'),
            'infinal1' => $request->input('infinal1'),
            'infinal2' => $request->input('infinal2'),
            'estado' => 0,
            'fecha_informe_tecnico' => $date,
            'updated_at' => $date,
          ]);
      } else {
        $id = DB::table('Informe_tecnico')
          ->insertGetId([
            'proyecto_id' => $request->input('proyecto_id'),
            'informe_tipo_id' => 40,
            'estado_trabajo' => $request->input('estado_trabajo'),
            'infinal1' => $request->input('infinal1'),
            'infinal2' => $request->input('infinal2'),
            'estado' => 0,
            'fecha_informe_tecnico' => $date,
            'created_at' => $date,
            'updated_at' => $date,
          ]);
      }

      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PTPMAEST-INFORME-FINAL-tesis", "Archivos de informe", 22);
      }

      if ($request->hasFile('file2')) {
        $name = $request->input('proyecto_id') . "/" . $date1->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PTPMAEST-INFORME-FINAL-acta", "Archivos de informe", 22);
      }
    }

    return ['message' => 'success', 'detail' => 'Informe guardado correctamente', 'id' => $id];
  }

  public function presentar(Request $request) {
    if ($request->input('informe') == "Informe académico de avance") {
      $count1 = DB::table('Informe_tecnico')
        ->where('id', '=', $request->input('id'))
        ->whereNotNull('infinal1')
        ->whereNotNull('infinal2')
        ->whereNotNull('infinal3')
        ->whereNotNull('infinal7')
        ->count();

      if ($count1 == 0) {
        return ['message' => 'error', 'detail' => 'Necesita completar todos los campos'];
      }

      $count2 = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('categoria', '=', 'informe-PTPMAEST-INFORME-AVANCE')
        ->where('nombre', '=', 'Archivos de informe')
        ->where('estado', '=', 1)
        ->count();

      if ($count2 == 0) {
        return ['message' => 'error', 'detail' => 'Necesita cargar al archivo de medios probatorios'];
      }
    } else {
      $count1 = DB::table('Informe_tecnico')
        ->where('id', '=', $request->input('id'))
        ->whereNotNull('infinal1')
        ->whereNotNull('infinal2')
        ->count();

      if ($count1 == 0) {
        return ['message' => 'error', 'detail' => 'Necesita completar todos los campos'];
      }

      $count2 = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('categoria', '=', 'informe-PTPMAEST-INFORME-FINAL-tesis')
        ->where('nombre', '=', 'Archivos de informe')
        ->where('estado', '=', 1)
        ->count();

      if ($count2 == 0) {
        return ['message' => 'error', 'detail' => 'Necesita cargar la tesis'];
      }

      $count3 = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('categoria', '=', 'informe-PTPMAEST-INFORME-FINAL-acta')
        ->where('nombre', '=', 'Archivos de informe')
        ->where('estado', '=', 1)
        ->count();

      if ($count3 == 0) {
        return ['message' => 'error', 'detail' => 'Necesita cargar el acta de tesis'];
      }
    }

    $count = DB::table('Informe_tecnico')
      ->where('id', '=', $request->input('id'))
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
        DB::raw("COALESCE(a.fecha_registro_csi, a.fecha_envio) AS fecha_estado"),
        'a.*',
      ])
      ->where('a.id', '=', $request->query('informe_tecnico_id'))
      ->first();

    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Proyecto_descripcion AS d', function (JoinClause $join) {
        $join->on('d.proyecto_id', '=', 'a.id')
          ->where('d.codigo', '=', 'publicacion_tipo');
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
        'd.detalle AS tipo_publicacion',
      ])
      ->where('a.id', '=', $detalles->proyecto_id)
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ' ', b.nombres) AS nombres"),
        'c.nombre AS condicion',
      ])
      ->where('a.proyecto_id', '=', $detalles->proyecto_id)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->condicion => $item->nombres];
      });

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

    if ($request->query('tipo_informe') == "Informe académico de avance") {
      $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.ptpmaest1', [
        'proyecto' => $proyecto,
        'miembros' => $miembros,
        'archivos' => $archivos,
        'detalles' => $detalles,
        'informe' => $request->query('tipo_informe')
      ]);
    } else {
      $pdf = Pdf::loadView('admin.estudios.informes_tecnicos.ptpmaest2', [
        'proyecto' => $proyecto,
        'miembros' => $miembros,
        'archivos' => $archivos,
        'detalles' => $detalles,
        'informe' => $request->query('tipo_informe')
      ]);
    }

    return $pdf->stream();
  }
}

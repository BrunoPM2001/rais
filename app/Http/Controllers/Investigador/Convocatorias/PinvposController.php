<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinvposController extends S3Controller {
  public function verificar(Request $request) {
    $errores = [];

    $habilitado = DB::table('Proyecto_integrante_dedicado AS a')
      ->leftJoin('Proyecto AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_id')
          ->where('b.tipo_proyecto', '=', 'PINVPOS')
          ->where('b.periodo', '=', 2024);
      })
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Proyecto_doc AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'b.id')
          ->where('e.tipo', '=', 17)
          ->where('e.estado', '=', 1)
          ->where('e.categoria', '=', 'resolucion')
          ->where('e.nombre', '=', 'Resolución de Designación Oficial');
      })
      ->select([
        'b.id AS proyecto_id',
        'b.step',
        'b.estado',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS responsable"),
        'c.doc_numero',
        'c.email3',
        'd.nombre AS facultad',
        'c.codigo',
        'c.tipo',
        DB::raw("CONCAT('/minio/proyecto-doc/', e.archivo) AS url")
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    if (!$habilitado) {
      $errores[] = 'Esta convocatoria no está disponible para usted';
    }

    if ($habilitado->estado != 6 && $habilitado->estado != null) {
      $errores[] = 'Ya ha enviado una propuesta de proyecto';
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      return ['estado' => true, 'datos' => $habilitado];
    }
  }

  public function verificar2(Request $request) {
    $errores = [];

    $habilitado = DB::table('Proyecto_integrante_dedicado AS a')
      ->leftJoin('Proyecto AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_id')
          ->where('b.tipo_proyecto', '=', 'PINVPOS')
          ->where('b.periodo', '=', 2024);
      })
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        'b.id AS proyecto_id',
        'b.step',
        'b.estado',
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
      $errores[] = 'No tiene un proyecto creado';
    }

    if ($habilitado->estado != 6 && $habilitado->estado != null) {
      $errores[] = 'Ya ha enviado una propuesta de proyecto';
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      return ['estado' => true, 'datos' => $habilitado];
    }
  }

  public function verificar3(Request $request) {
    $errores = [];

    $habilitado = DB::table('Proyecto_integrante_dedicado AS a')
      ->leftJoin('Proyecto AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_id')
          ->where('b.tipo_proyecto', '=', 'PINVPOS')
          ->where('b.periodo', '=', 2024);
      })
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        'b.id AS proyecto_id',
        'b.step',
        'b.estado',
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
      $errores[] = 'No tiene un proyecto creado';
    }

    if ($habilitado->estado != 6 && $habilitado->estado != null) {
      $errores[] = 'Ya ha enviado una propuesta de proyecto';
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      $detalles = DB::table('Proyecto_descripcion')
        ->select([
          'codigo',
          'detalle'
        ])
        ->where('proyecto_id', '=', $habilitado->proyecto_id)
        ->get()
        ->mapWithKeys(function ($item) {
          return [$item->codigo => $item->detalle];
        });

      return ['estado' => true, 'datos' => $habilitado, 'detalles' => $detalles];
    }
  }

  public function verificar4(Request $request) {
    $errores = [];

    $habilitado = DB::table('Proyecto_integrante_dedicado AS a')
      ->leftJoin('Proyecto AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_id')
          ->where('b.tipo_proyecto', '=', 'PINVPOS')
          ->where('b.periodo', '=', 2024);
      })
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        'b.id AS proyecto_id',
        'b.step',
        'b.estado',
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
      $errores[] = 'No tiene un proyecto creado';
    }

    if ($habilitado->estado != 6 && $habilitado->estado != null) {
      $errores[] = 'Ya ha enviado una propuesta de proyecto';
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      $actividades = DB::table('Proyecto_actividad')
        ->select([
          'id',
          'actividad',
          'fecha_inicio',
          'fecha_fin',
          'duracion'
        ])
        ->where('proyecto_id', '=', $habilitado->proyecto_id)
        ->get();

      return ['estado' => true, 'datos' => $habilitado, 'actividades' => $actividades];
    }
  }

  public function registrar1(Request $request) {
    if ($request->input('id') != 'null') {
      $id = $request->input('id');

      //  Verificar que sea miembro de proyecto
      $cuenta = DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $id)
        ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->count();

      if ($cuenta == 0) {
        return ['message' => 'error', 'detail' => 'No es miembro de este proyecto'];
      } else {
        if ($request->hasFile('file')) {
          $date = Carbon::now();
          $date1 = Carbon::now();
          $name = $date1->format('Ymd-His');
          $nameFile = $id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
          $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

          DB::table('Proyecto_doc')
            ->where('proyecto_id', '=', $id)
            ->where('tipo', '=', 17)
            ->where('categoria', '=', 'resolucion')
            ->where('nombre', '=', 'Resolución de Designación Oficial')
            ->update([
              'estado' => 0,
            ]);

          DB::table('Proyecto_doc')
            ->insert([
              'proyecto_id' => $id,
              'tipo' => 17,
              'categoria' => 'resolucion',
              'nombre' => 'Resolución de Designación Oficial',
              'comentario' => $date,
              'archivo' => $nameFile,
              'estado' => 1
            ]);
        }

        return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
      }
    } else {
      if ($request->hasFile('file')) {
        $date = Carbon::now();

        $id = DB::table('Proyecto')
          ->insertGetId([
            'titulo' => 'Líneas de investigación de los GI en el marco de los Objetivos de Desarrollo Sostenible (ODS)',
            'tipo_proyecto' => 'PINVPOS',
            'periodo' => 2024,
            'step' => 2,
            'estado' => 6,
            'fecha_inscripcion' => $date,
            'created_at' => $date,
            'updated_at' => $date,
          ]);

        $date1 = Carbon::now();
        $name = $date1->format('Ymd-His');
        $nameFile = $id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
        $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

        DB::table('Proyecto_doc')
          ->where('proyecto_id', '=', $id)
          ->where('tipo', '=', 17)
          ->where('categoria', '=', 'resolucion')
          ->where('nombre', '=', 'Resolución de Designación Oficial')
          ->update([
            'estado' => 0,
          ]);

        DB::table('Proyecto_doc')
          ->insert([
            'proyecto_id' => $id,
            'tipo' => 17,
            'categoria' => 'resolucion',
            'nombre' => 'Resolución de Designación Oficial',
            'comentario' => $date,
            'archivo' => $nameFile,
            'estado' => 1
          ]);

        DB::table('Proyecto_integrante')
          ->insert([
            'proyecto_id' => $id,
            'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
            'proyecto_integrante_tipo_id' => 28,
            'condicion' => 'Responsable',
            'created_at' => $date,
            'updated_at' => $date,
          ]);

        DB::table('Proyecto_integrante_dedicado')
          ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
          ->update([
            'proyecto_id' => $id
          ]);

        return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
      } else {
        return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
      }
    }
  }

  public function registrar3(Request $request) {
    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'objetivo'
      ], [
        'detalle' => $request->input('objetivo')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'justificacion'
      ], [
        'detalle' => $request->input('justificacion')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'metas'
      ], [
        'detalle' => $request->input('metas')
      ]);
  }

  public function agregarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'actividad' => $request->input('actividad'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'duracion' => $request->input('duracion'),
      ]);

    return ['message' => 'success', 'detail' => 'Actividad agregada correctamente'];
  }

  public function actualizarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->input('id'))
      ->update([
        'actividad' => $request->input('actividad'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'duracion' => $request->input('duracion'),
      ]);

    return ['message' => 'info', 'detail' => 'Actividad actualizada correctamente'];
  }

  public function eliminarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Actividad eliminada correctamente'];
  }
}

<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
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
          ->where('b.periodo', '=', 2024)
          ->whereNotIn('b.estado', [-1]);
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
    } else {
      if ($habilitado?->estado == 5) {
        $errores[] = 'Ya ha enviado una propuesta de proyecto';
      }
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
      ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
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
      $miembros = DB::table('Proyecto_integrante AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->join('Proyecto_integrante_dedicado AS c', 'c.investigador_id', '=', 'b.id')
        ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
        ->select([
          'a.id',
          'a.condicion',
          'b.apellido1',
          'b.apellido2',
          'b.nombres',
          'b.doc_numero',
          'b.codigo',
          'b.email3',
          'd.nombre AS facultad',
          'c.cargo'
        ])
        ->where('a.proyecto_id', '=', $habilitado->proyecto_id)
        ->where('c.proyecto_id', '=', $habilitado->proyecto_id)
        ->get();

      return ['estado' => true, 'datos' => $habilitado, 'miembros' => $miembros];
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

  public function verificar5(Request $request) {
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
          ->where('e.tipo', '=', 18)
          ->where('e.estado', '=', 1)
          ->where('e.categoria', '=', 'erd-cofinanciamiento')
          ->where('e.nombre', '=', 'ERD de cofinanciamiento');
      })
      ->leftJoin('Proyecto_descripcion AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'b.id')
          ->where('f.codigo', '=', 'facultad_monto');
      })
      ->select([
        'b.id AS proyecto_id',
        'b.step',
        'b.estado',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS responsable"),
        'c.doc_numero',
        'c.email3',
        'd.nombre AS facultad',
        'd.id AS facultad_id',
        'c.codigo',
        'c.tipo',
        DB::raw("CONCAT('/minio/proyecto-doc/', e.archivo) AS url"),
        'f.detalle AS monto'
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
      $presupuesto = DB::table('Proyecto_presupuesto AS a')
        ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
        ->select([
          'a.id',
          'b.id AS partida_id',
          'b.codigo',
          'b.partida',
          'b.tipo',
          'a.monto'
        ])
        ->where('a.proyecto_id', '=', $habilitado->proyecto_id)
        ->get();

      //  Ids no repetidos
      $partidaIds = $presupuesto->pluck('partida_id');

      $partidas = DB::table('Partida_proyecto AS a')
        ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
        ->select([
          'b.id AS value',
          DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
          'b.tipo',
        ])
        ->where('a.tipo_proyecto', '=', 'PINVPOS')
        ->where('a.postulacion', '=', 1)
        ->whereNotIn('b.id', $partidaIds)
        ->get();

      //  Montos
      $monto_coefinanciamiento = 0;
      $subvencion = 0;

      switch ($habilitado->facultad_id) {
        case 19:
        case 11:
        case 20:
          $subvencion = 5150;
          $monto_coefinanciamiento = 7725;
          break;
        case 12:
        case 2:
        case 16:
        case 18:
        case 9:
        case 17:
        case 14:
        case 6:
          $subvencion = 5150;
          $monto_coefinanciamiento = 10300;
          break;
        case 13:
        case 15:
        case 7:
          $subvencion = 5150;
          $monto_coefinanciamiento = 12875;
          break;
        case 5:
        case 4:
          $subvencion = 5150;
          $monto_coefinanciamiento = 15450;
          break;
        case 3:
        case 8:
        case 1:
        case 10:
          $subvencion = 5150;
          $monto_coefinanciamiento = 18025;
          break;
        default:
          $monto_asignado = 0;
          break;
      }

      return [
        'estado' => true,
        'datos' => $habilitado,
        'partidas' => $partidas,
        'presupuesto' => $presupuesto,
        'montos' => [
          'monto_coefinanciamiento' => $monto_coefinanciamiento,
          'subvencion' => $subvencion,
        ]
      ];
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
        $monto_asignado = 0;

        $fac = DB::table('Proyecto_integrante_dedicado')
          ->select([
            'facultad_id'
          ])
          ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
          ->first();

        switch ($fac->facultad_id) {
          case 19:
          case 11:
          case 20:
            $monto_asignado = 28325;
            break;
          case 12:
          case 2:
          case 16:
          case 18:
          case 9:
          case 17:
          case 14:
          case 6:
            $monto_asignado = 87550;
            break;
          case 13:
          case 15:
          case 7:
            $monto_asignado = 43775;
            break;
          case 5:
          case 4:
            $monto_asignado = 36050;
            break;
          case 3:
          case 8:
          case 1:
          case 10:
            $monto_asignado = 77250;
            break;
          default:
            $monto_asignado = 0;
            break;
        }

        $id = DB::table('Proyecto')
          ->insertGetId([
            'titulo' => 'Líneas de investigación de los GI en el marco de los Objetivos de Desarrollo Sostenible (ODS)',
            'tipo_proyecto' => 'PINVPOS',
            'periodo' => 2024,
            'step' => 2,
            'estado' => 6,
            'monto_asignado' => $monto_asignado,
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

        //  Agregar al resto de integrantes
        $miembros = DB::table('Proyecto_integrante_dedicado')
          ->select([
            'investigador_id'
          ])
          ->where('facultad_id', '=', $fac->facultad_id)
          ->where('investigador_id', '!=', $request->attributes->get('token_decoded')->investigador_id)
          ->get();

        foreach ($miembros as $miembro) {
          DB::table('Proyecto_integrante')
            ->insert([
              'proyecto_id' => $id,
              'investigador_id' => $miembro->investigador_id,
              'proyecto_integrante_tipo_id' => 29,
              'condicion' => 'Miembro',
              'created_at' => $date,
              'updated_at' => $date,
            ]);

          DB::table('Proyecto_integrante_dedicado')
            ->where('investigador_id', '=', $miembro->investigador_id)
            ->update([
              'proyecto_id' => $id
            ]);
        }

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

  public function agregarPartida(Request $request) {
    $date = Carbon::now();

    DB::table('Proyecto_presupuesto')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'partida_id' => $request->input('partida')["value"],
        'monto' => $request->input('monto'),
        'created_at' => $date,
        'updated_at' => $date,
      ]);

    return ['message' => 'success', 'detail' => 'Partida agregada correctamente'];
  }

  public function actualizarPartida(Request $request) {
    $date = Carbon::now();

    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'partida_id' => $request->input('partida')["value"],
        'monto' => $request->input('monto'),
        'updated_at' => $date,
      ]);

    return ['message' => 'info', 'detail' => 'Partida actualizada correctamente'];
  }

  public function eliminarPartida(Request $request) {
    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Partida eliminada correctamente'];
  }

  public function registrar5(Request $request) {
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
          ->where('tipo', '=', 18)
          ->where('categoria', '=', 'erd-cofinanciamiento')
          ->where('nombre', '=', 'ERD de cofinanciamiento')
          ->update([
            'estado' => 0,
          ]);

        DB::table('Proyecto_doc')
          ->insert([
            'proyecto_id' => $id,
            'tipo' => 18,
            'categoria' => 'erd-cofinanciamiento',
            'nombre' => 'ERD de cofinanciamiento',
            'comentario' => $date,
            'archivo' => $nameFile,
            'estado' => 1
          ]);

        DB::table('Proyecto_descripcion')
          ->updateOrInsert([
            'proyecto_id' => $id,
            'codigo' => 'facultad_monto'
          ], [
            'detalle' => $request->input('monto')
          ]);

        return ['message' => 'success', 'detail' => 'Datos guardados'];
      } else {
        $cuenta = DB::table('Proyecto_doc')
          ->where('proyecto_id', '=', $id)
          ->where('tipo', '=', 18)
          ->where('categoria', '=', 'erd-cofinanciamiento')
          ->where('nombre', '=', 'ERD de cofinanciamiento')
          ->where('estado', '=', 1)
          ->count();

        if ($cuenta == 0) {
          return ['message' => 'error', 'detail' => 'Necesita cargar un archivo'];
        } else {
          DB::table('Proyecto_descripcion')
            ->updateOrInsert([
              'proyecto_id' => $id,
              'codigo' => 'facultad_monto'
            ], [
              'detalle' => $request->input('monto')
            ]);

          return ['message' => 'success', 'detail' => 'Datos guardados'];
        }
      }
    }
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Proyecto_doc AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.id')
          ->where('e.tipo', '=', 17)
          ->where('e.estado', '=', 1)
          ->where('e.categoria', '=', 'resolucion')
          ->where('e.nombre', '=', 'Resolución de Designación Oficial');
      })
      ->leftJoin('Proyecto_doc AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'a.id')
          ->where('f.tipo', '=', 18)
          ->where('f.estado', '=', 1)
          ->where('f.categoria', '=', 'erd-cofinanciamiento')
          ->where('f.nombre', '=', 'ERD de cofinanciamiento');
      })
      ->select([
        'a.titulo',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'c.doc_numero',
        'c.email3',
        'd.nombre AS facultad',
        'c.codigo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        DB::raw("CASE
          WHEN e.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS anexo"),
        DB::raw("CASE
          WHEN f.archivo IS NULL THEN 'No'
          ELSE 'Sí'
        END AS rd"),
        'a.updated_at',
        DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 3 THEN 'En evaluacion'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'Sustentado'
            WHEN 9 THEN 'En ejecución'
            WHEN 10 THEN 'Ejecutado'
            WHEN 11 THEN 'Concluído'
          ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.condicion',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_numero',
        'b.codigo',
        'b.email3',
        'c.nombre AS facultad',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
        'duracion'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.id AS partida_id',
        'b.codigo',
        'b.partida',
        'b.tipo',
        'a.monto'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.actividades.taller', [
      'proyecto' => $proyecto,
      'miembros' => $miembros,
      'detalles' => $detalles,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);
    return $pdf->stream();
  }

  public function enviar(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'estado' => 5
      ]);

    return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
  }
}

<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EciController extends S3Controller {
  public function listado(Request $request) {
    $listado = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id',
        'b.titulo',
        'b.step',
        DB::raw("CASE(b.estado)
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
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.condicion', '=', 'Responsable')
      ->where('b.tipo_proyecto', '=', 'ECI')
      ->where('b.periodo', '=', 2025)
      ->get();

    return $listado;
  }

  public function eliminarPropuesta(Request $request) {
    $proyectoId = $request->query('id');

    if ($proyectoId) {
      DB::table('Proyecto_descripcion')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_presupuesto')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto')
        ->where('id', '=', $proyectoId)
        ->delete();

      return ['message' => 'info', 'detail' => 'Propuesta eliminada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'No se pudo eliminar la propuesta'];
    }
  }

  public function verificar(Request $request, $proyecto_id = null) {
    $errores = [];

    //  Ser coordinador de un grupo de investigación
    $req1 = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', function (JoinClause $join) {
        $join->on('b.investigador_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Titular')
          ->where('b.cargo', '=', 'Coordinador');
      })
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req1 == 0 && $errores[] = "Necesita ser coordinador de un grupo de investigación";

    if ($proyecto_id != null) {
      $req2 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->where('a.proyecto_id', '=', $proyecto_id)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'ECI')
        ->count();

      $req2 == 0 && $errores[] = "No figura como responsable del proyecto";
    }

    $req3 = DB::table('view_deudores AS vdeuda')
      ->where('vdeuda.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req3 != 0 && $errores[] = "Usted tiene registradas deudas pendientes que deben ser resueltas para participar en el concurso";


    if (!empty($errores)) {
      return ['estado' => false, 'errores' => $errores];
    } else {
      return ['estado' => true];
    }
  }

  public function verificar1(Request $request) {

    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $grupo = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'a.investigador_id')
      ->select([
        'a.grupo_id',
        'b.facultad_id',
        'b.grupo_nombre',
        'c.nombre AS facultad',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS responsable")
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.cargo', '=', 'Coordinador')
      ->first();

    $miembros = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.investigador_id', '=', 'b.id')
      ->leftJoin('Proyecto_integrante_tipo AS e', function (JoinClause $join) {
        $join->on('e.id', '=', 'd.proyecto_integrante_tipo_id')
          ->where('e.nombre', '=', 'Tesista');
      })
      ->select([
        'a.id',
        'a.condicion',
        'b.doc_numero',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        'b.codigo_orcid',
        'b.tipo',
        'c.nombre AS facultad',
        DB::raw("CASE
          WHEN COUNT(e.id) > 0 THEN 'Sí'
          ELSE 'No'
        END AS tesista")
      ])
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->groupBy('b.id')
      ->get();

    return [
      'estado' => true,
      'grupo' => $grupo,
      'miembros' => $miembros,
    ];
  }

  public function registrar1(Request $request) {
    $date = Carbon::now();
    $id = $request->input('id');
    if (!$request->input('id')) {
      $cuenta = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', function (JoinClause $join) {
          $join->on('b.id', '=', 'a.proyecto_id')
            ->where('b.tipo_proyecto', '=', 'ECI')
            ->where('b.periodo', '=', 2025);
        })
        ->select([
          'b.id'
        ])
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      if ($cuenta) {
        return ['message' => 'success', 'detail' => 'Redirigiendo a su proyecto', 'id' => $cuenta->id];
      }

      $id = DB::table('Proyecto')
        ->insertGetId([
          'facultad_id' => $request->input('facultad_id'),
          'grupo_id' => $request->input('grupo_id'),
          'tipo_proyecto' => 'ECI',
          'periodo' => 2025,
          'step' => 2,
          'estado' => 6,
          'fecha_inscripcion' => $date,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'proyecto_integrante_tipo_id' => 30,
          'condicion' => 'Responsable',
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
    } else {
      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
    }
  }

  public function verificar2(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $proyecto = DB::table('Proyecto')
      ->select([
        'grupo_id',
        'titulo',
        'linea_investigacion_id',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();


    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->whereIn('codigo', ['resumen', 'justificacion', 'propuesta'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'b.id AS value',
        'nombre AS label'
      ])
      ->where('a.grupo_id', '=', $proyecto->grupo_id)
      ->get();

    return [
      'estado' => true,
      'data' => [
        'titulo' => $proyecto->titulo,
        'linea_investigacion_id' => $proyecto->linea_investigacion_id,
        'descripcion' => $descripcion,
        'lineas' => $lineas
      ],
    ];
  }

  public function registrar2(Request $request) {
    $date = Carbon::now();
    DB::table('Proyecto')->where('id', '=', $request->input('id'))->update(['titulo' => $request->input('titulo'), 'linea_investigacion_id' => $request->input('linea'), 'step' => 3, 'updated_at' => $date]);

    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'resumen', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('resumen')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'justificacion', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('justificacion')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'propuesta', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('propuesta')]);

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar3(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->whereIn('codigo', ['nombre_equipo', 'desc_equipo'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $documentos = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('tipo', [7, 10])
      ->get();

    return [
      'estado' => true,
      'data' => [
        'descripcion' => $descripcion,
        'documentos' => $documentos,
      ],
    ];
  }

  public function registrar3(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'nombre_equipo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('nombre_equipo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'desc_equipo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('desc_equipo')]);

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function agregarDoc(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'tipo' => $request->input('tipo'),
          'categoria' => $request->input('categoria'),
          'nombre' => $request->input('nombre'),
          'comentario' => $request->input('comentario'),
          'archivo' => $name,
          'estado' => 1,
        ]);

      return ['message' => 'success', 'detail' => 'Documento añadido'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function eliminarDoc(Request $request) {
    DB::table('Proyecto_doc')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Documento eliminado correctamente'];
  }

  public function verificar4(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.id AS partida_id',
        'b.codigo',
        'b.partida',
        'b.tipo',
        'a.monto',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->orderBy('a.tipo')
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
      ->where('a.tipo_proyecto', '=', 'ECI')
      ->where('a.postulacion', '=', 1)
      ->whereNotIn('b.id', $partidaIds)
      ->get();

    //  Info de presupuesto
    $info = [
      'bienes_monto' => 0.00,
      'bienes_cantidad' => 0,
      'servicios_monto' => 0.00,
      'servicios_cantidad' => 0,
      'otros_monto' => 0.00,
      'otros_cantidad' => 0
    ];

    foreach ($presupuesto as $data) {
      if ($data->tipo == "Bienes") {
        $info["bienes_monto"] += $data->monto;
        $info["bienes_cantidad"]++;
      }
    }

    $div = ($info["bienes_monto"] + $info["servicios_monto"] + $info["otros_monto"]);

    if ($div != 0) {
      $info["bienes_porcentaje"] = number_format(($info["bienes_monto"] / $div) * 100, 2);
      $info["servicios_porcentaje"] = number_format(($info["servicios_monto"] / $div) * 100, 2);
      $info["otros_porcentaje"] = number_format(($info["otros_monto"] / $div) * 100, 2);
    } else {
      $info["bienes_porcentaje"] = 0;
      $info["servicios_porcentaje"] = 0;
      $info["otros_porcentaje"] = 0;
    }

    return [
      'estado' => true,
      'partidas' => $partidas,
      'presupuesto' => $presupuesto,
      'info' => $info
    ];
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

  public function eliminarPartida(Request $request) {
    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Partida eliminada correctamente'];
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

  public function verificar5(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->whereIn('codigo', ['impacto_propuesta'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $documentos = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('tipo', [1, 2, 4])
      ->get();

    return [
      'estado' => true,
      'data' => [
        'descripcion' => $descripcion,
        'documentos' => $documentos,
      ],
    ];
  }

  public function registrar5(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'impacto_propuesta', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('impacto_propuesta')]);

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar6(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->whereIn('codigo', ['plan_manejo'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $documentos = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('tipo', [8, 9, 11])
      ->get();

    return [
      'estado' => true,
      'data' => [
        'descripcion' => $descripcion,
        'documentos' => $documentos,
      ],
    ];
  }

  public function registrar6(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'plan_manejo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('plan_manejo')]);

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar7(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    return ['estado' => true];
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'a.grupo_id',
        'a.titulo',
        'a.periodo',
        'b.nombre AS linea'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $grupo = DB::table('Grupo AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS c', 'c.id', '=', 'b.area_id')
      ->leftJoin('Grupo_integrante AS d', function (JoinClause $join) {
        $join->on('d.grupo_id', '=', 'a.id')
          ->where('d.cargo', '=', 'Coordinador')
          ->whereNot('d.condicion', 'LIKE', 'Ex%');
      })
      ->leftJoin('Usuario_investigador AS e', 'e.id', '=', 'd.investigador_id')
      ->select([
        'a.grupo_nombre',
        DB::raw("CONCAT(e.apellido1, ' ', e.apellido2, ', ', e.nombres) AS responsable"),
        'c.nombre AS area',
        'b.nombre AS facultad',
        'c.sigla AS categoria',
      ])
      ->where('a.id', '=', $proyecto->grupo_id)
      ->first();

    $docs1 = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('tipo', [7, 10])
      ->get();

    $docs2 = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('tipo', [1, 2, 4])
      ->get();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.partida',
        'a.justificacion',
        'b.tipo',
        'a.monto',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('investigador.convocatorias.eci', [
      'proyecto' => $proyecto,
      'grupo' => $grupo,
      'docs1' => $docs1,
      'docs2' => $docs2,
      'detalles' => $detalles,
      'presupuesto' => $presupuesto,
    ]);

    return $pdf->stream();
  }

  public function enviar(Request $request) {
    //  Verificar autorización de grupo
    $req1 = DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 6)
      ->where('autorizacion_grupo', '=', 1)
      ->count();

    if ($req1 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita que el coordinador de su grupo autorice la propuesta de proyecto'];
    }

    $count = DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 6)
      ->update([
        'estado' => 5,
        'updated_at' => Carbon::now()
      ]);

    if ($count > 0) {
      return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
    } else {
      return ['message' => 'error', 'detail' => 'Ya ha enviado su solicitud'];
    }
  }
}

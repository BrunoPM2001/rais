<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PconfigiInvController extends S3Controller {
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
      ->where('b.tipo_proyecto', '=', 'PCONFIGI-INV')
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

    //  Ser titular de un grupo de investigación
    $req1 = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', function (JoinClause $join) {
        $join->on('b.investigador_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Titular');
      })
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req1 == 0 && $errores[] = "Necesita ser titular de un grupo de investigación";

    if ($proyecto_id != null) {
      $req2 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->where('a.proyecto_id', '=', $proyecto_id)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PCONFIGI-INV')
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
    } else {
      if (isset($res1["id"])) {
        return ['go' => $res1["id"], 'step' => $res1["step"]];
      }
    }

    $datos = DB::table('Usuario_investigador AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS c', 'c.id', '=', 'b.area_id')
      ->join('Grupo_integrante AS d', function (JoinClause $join) {
        $join->on('d.investigador_id', '=', 'a.id')
          ->where('d.condicion', '=', 'Titular');
      })
      ->join('Grupo AS e', 'e.id', '=', 'd.grupo_id')
      ->select([
        DB::raw("CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS nombres"),
        'c.nombre AS area',
        'b.nombre AS facultad',
        'e.id AS grupo_id',
        'a.facultad_id',
        'e.grupo_nombre'
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $lineas =  DB::table('Linea_investigacion AS a')
      ->join('Grupo_linea AS b', 'b.linea_investigacion_id', '=', 'a.id')
      ->select([
        'a.id AS value',
        'a.nombre AS label'
      ])
      ->where('b.grupo_id', '=', $datos->grupo_id)
      ->where('a.estado', '=', 1)
      ->get();

    $ods = DB::table('Grupo_linea as gl')
      ->join('Linea_investigacion as li', 'gl.linea_investigacion_id', '=', 'li.id')
      ->join('Linea_investigacion_ods as lo', 'lo.linea_investigacion_id', '=', 'li.id')
      ->join('Ods as odsx', 'odsx.id', '=', 'lo.ods_id')
      ->where('gl.grupo_id', '=', $datos->grupo_id)
      ->where('li.estado', 1)
      ->groupBy('odsx.id')
      ->select('odsx.id as value', 'descripcion as label')
      ->get();

    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label"),
        'parent_id'
      ])
      ->get();

    if ($request->query('id')) {
      $proyecto = DB::table('Proyecto AS a')
        ->leftJoin('Proyecto_descripcion AS b', function (JoinClause $join) {
          $join->on('a.id', '=', 'b.proyecto_id')
            ->where('b.codigo', '=', 'objetivo_ods');
        })
        ->leftJoin('Proyecto_descripcion AS c', function (JoinClause $join) {
          $join->on('a.id', '=', 'c.proyecto_id')
            ->where('c.codigo', '=', 'tipo_investigacion');
        })
        ->select([
          'a.titulo',
          'a.linea_investigacion_id',
          'a.ocde_id',
          'b.detalle AS objetivo_ods',
          'c.detalle AS tipo_investigacion',
          'a.localizacion',
        ])
        ->where('a.id', '=', $request->query('id'))
        ->first();

      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'proyecto' => $proyecto,
        'ocde' => $ocde,
        'ods' => $ods,
      ];
    } else {
      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'ocde' => $ocde,
        'ods' => $ods,
      ];
    }
  }

  public function registrar1(Request $request) {
    $date = Carbon::now();
    if ($request->input('id')) {
      DB::table('Proyecto')
        ->where('id', '=', $request->input('id'))
        ->update([
          'titulo' => $request->input('titulo'),
          'linea_investigacion_id' => $request->input('linea')["value"],
          'ocde_id' => $request->input('ocde')["value"],
          'localizacion' => $request->input('localizacion')["value"],
          'step' => 2,
          'estado' => 6,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_descripcion')
        ->updateOrInsert([
          'proyecto_id' => $request->input('id'),
          'codigo' => 'tipo_investigacion',
        ], [
          'detalle' => $request->input('tipo_investigacion')["value"],
        ]);

      DB::table('Proyecto_descripcion')
        ->updateOrInsert([
          'proyecto_id' => $request->input('id'),
          'codigo' => 'objetivo_ods',
        ], [
          'detalle' => $request->input('ods')["value"],
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $request->input('id')];
    } else {
      $datos = DB::table('Usuario_investigador AS a')
        ->join('Grupo_integrante AS b', function (JoinClause $join) {
          $join->on('b.investigador_id', '=', 'a.id')
            ->where('b.condicion', '=', 'Titular');
        })
        ->select([
          'a.facultad_id',
          'b.grupo_id',
          'b.id'
        ])
        ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      $id = DB::table('Proyecto')
        ->insertGetId([
          'titulo' => $request->input('titulo'),
          'linea_investigacion_id' => $request->input('linea')["value"],
          'ocde_id' => $request->input('ocde')["value"],
          'facultad_id' => $datos->facultad_id,
          'grupo_id' => $datos->grupo_id,
          'localizacion' => $request->input('localizacion')["value"],
          'tipo_proyecto' => 'PCONFIGI-INV',
          'step' => 2,
          'estado' => 6,
          'periodo' => 2025,
          'fecha_inscripcion' => $date,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      DB::table('Proyecto_descripcion')
        ->insert([
          'proyecto_id' => $id,
          'codigo' => 'tipo_investigacion',
          'detalle' => $request->input('tipo_investigacion')["value"],
        ]);

      DB::table('Proyecto_descripcion')
        ->insert([
          'proyecto_id' => $id,
          'codigo' => 'objetivo_ods',
          'detalle' => $request->input('ods')["value"],
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'proyecto_integrante_tipo_id' => 36,
          'grupo_id' => $datos->grupo_id,
          'grupo_integrante_id' => $datos->id,
          'condicion' => 'Responsable',
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
    }
  }

  public function verificar2(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $data = DB::table('Usuario_investigador AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->join('Dependencia AS c', 'c.id', '=', 'a.dependencia_id')
      ->select([
        DB::raw("CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS nombres"),
        'a.doc_numero',
        'a.fecha_nac',
        'a.especialidad',
        'a.titulo_profesional',
        'a.grado',
        'a.tipo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(a.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        'a.codigo',
        'c.dependencia',
        'b.nombre AS facultad',
        'a.codigo_orcid',
        'a.scopus_id',
        'a.google_scholar',
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $carta = DB::table('Proyecto_doc')
      ->select([
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url"),
        'comentario AS url_fecha'
      ])
      ->where('proyecto_id', '=', $request->input('id'))
      ->where('categoria', '=', 'documento')
      ->where('nombre', '=', 'Carta de Vinculación')
      ->where('estado', '=', 1)
      ->first();

    return [
      'estado' => true,
      'data' => $data,
      'carta' => $carta
    ];
  }

  public function registrar2(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('categoria', '=', 'documento')
        ->where('nombre', '=', 'Carta de Vinculación')
        ->where('estado', '=', 1)
        ->update([
          'estado' => 0
        ]);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'archivo' => $name,
          'categoria' => 'documento',
          'nombre' => 'Carta de Vinculación',
          'estado' => 1,
          'comentario' => Carbon::now(),
          'tipo' => 2,
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados'];
    } else {
      $count = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $request->input('id'))
        ->where('categoria', '=', 'documento')
        ->where('nombre', '=', 'Carta de Vinculación')
        ->where('estado', '=', 1)
        ->count();

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'No hay una carta de compromiso cargada'];
      } else {
        return ['message' => 'success', 'detail' => 'Datos guardados'];
      }
    }
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
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'antecedentes',
        'justificacion',
        'pintelectual',
        'contribucion_impacto',
        'hipotesis',
        'objetivos',
        'metodologia_trabajo',
        'calidad_viabilidad',
        'referencias_bibliograficas',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $palabras_clave = DB::table('Proyecto')
      ->select([
        'palabras_clave'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $archivo1 = DB::table('File AS a')
      ->select([
        DB::raw("CONCAT('/minio/', a.bucket,'/', a.key) AS url")
      ])
      ->where('a.tabla_id', '=', $request->query('id'))
      ->where('a.tabla', '=', 'Proyecto')
      ->where('a.recurso', '=', 'METODOLOGIA_TRABAJO')
      ->where('a.estado', '=', 20)
      ->first();

    return [
      'estado' => true,
      'descripcion' => $descripcion,
      'palabras_clave' => $palabras_clave->palabras_clave ?? "",
      'archivos' => [
        'metodologia' => $archivo1?->url,
      ]
    ];
  }

  public function registrar3(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'resumen_ejecutivo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('resumen_ejecutivo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'antecedentes', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('antecedentes')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'justificacion', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('justificacion')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'pintelectual', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('pintelectual')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'contribucion_impacto', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('contribucion_impacto')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'hipotesis', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('hipotesis')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'objetivos', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('objetivos')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'metodologia_trabajo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('metodologia_trabajo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'calidad_viabilidad', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('calidad_viabilidad')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'referencias_bibliograficas', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('referencias_bibliograficas')]);

    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = $request->input('id') . "/token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $name);

      DB::table('File')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('tabla', '=', 'Proyecto')
        ->where('recurso', '=', 'METODOLOGIA_TRABAJO')
        ->where('estado', '=', 20)
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla_id' => $request->input('id'),
          'tabla' => 'Proyecto',
          'recurso' => 'METODOLOGIA_TRABAJO',
          'bucket' => 'proyecto-doc',
          'key' => $name,
          'estado' => 20,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'step' => 4,
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar4(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $actividades = DB::table('Proyecto_actividad AS a')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    return ['estado' => true, 'actividades' => $actividades];
  }

  public function addActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'actividad' => $request->input('actividad'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
      ]);

    return ['message' => 'info', 'detail' => 'Actividad añadida'];
  }

  public function eliminarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Actividad eliminada'];
  }

  public function editActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->input('id'))
      ->update([
        'actividad' => $request->input('actividad'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
      ]);

    return ['message' => 'info', 'detail' => 'Actividad actualizada'];
  }

  public function verificar5(Request $request) {
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
      ->where('a.tipo_proyecto', '=', 'PCONFIGI-INV')
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
      if ($data->tipo == "Servicios") {
        $info["servicios_monto"] += $data->monto;
        $info["servicios_cantidad"]++;
      }
      if ($data->tipo == "Otros") {
        $info["otros_monto"] += $data->monto;
        $info["otros_cantidad"]++;
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

  public function validarPresupuesto(Request $request) {
    $alerta = [];

    $partidas = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida_proyecto AS b', function (JoinClause $join) {
        $join->on('b.partida_id', '=', 'a.partida_id')
          ->where('b.tipo_proyecto', '=', 'PCONFIGI-INV');
      })
      ->leftJoin('Partida_proyecto_grupo AS c', 'c.partida_proyecto_id', '=', 'b.id')
      ->leftJoin('Partida_grupo AS d', 'd.id', '=', 'c.partida_grupo_id')
      ->select([
        'd.nombre',
        'd.monto_max',
        DB::raw("SUM(a.monto) AS total")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->groupBy('d.id')
      ->get();

    foreach ($partidas as $item) {
      if ($item->monto_max < $item->total && $item->nombre != null) {
        $alerta[] = $item->nombre . ": " . $item->monto_max;
      }
    };

    if (sizeof($alerta) == 0) {
      return ['message' => 'info', 'detail' => 'Su proyecto respeta los límites de la directiva'];
    } else {
      return ['message' => 'warning', 'detail' => 'El presupuesto presenta excesos en la(s) siguiente(s) categoría(s). ' . implode(',', $alerta) . '; para mayor detalle revisar la directiva correspondiente.', $alerta];
    }
  }

  public function verificar6(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'd.nombre AS facultad',
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->groupBy('b.id')
      ->get();

    return ['estado' => true, 'integrantes' => $integrantes];
  }

  public function listadoCorresponsables(Request $request) {
    $grupo = DB::table('Proyecto')
      ->select([
        'grupo_id'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('view_deudores AS d', 'd.investigador_id', '=', 'b.id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'c.nombre AS facultad',
        DB::raw("COUNT(d.deuda_id) AS deudas"),
      )
      ->where('a.condicion', '=', 'Titular')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->facultad,
          'Deudas: ' . $item->deudas,
        ];
        $item->disabled = $item->deudas == 0 ? false : true;
        return $item;
      });

    return $listado;
  }

  public function listadoDocentes(Request $request) {
    $grupo = DB::table('Proyecto')
      ->select([
        'grupo_id'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('view_deudores AS d', 'd.investigador_id', '=', 'b.id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'c.nombre AS facultad',
        DB::raw("COUNT(d.deuda_id) AS deudas"),
      )
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('a.condicion', '=', 'Titular')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->facultad,
          'Deudas: ' . $item->deudas,
        ];
        $item->disabled = $item->deudas == 0 ? false : true;
        return $item;
      });

    return $listado;
  }

  public function listadoTesistas(Request $request) {
    $grupo = DB::table('Proyecto')
      ->select([
        'grupo_id'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Repo_sum AS c', 'c.codigo_alumno', '=', 'b.codigo')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS g', 'g.investigador_id', '=', 'b.id')
      ->leftJoin('Proyecto_integrante_tipo AS h', function (JoinClause $join) {
        $join->on('h.id', '=', 'g.proyecto_integrante_tipo_id')
          ->where('h.nombre', '=', 'Tesista');
      })
      ->leftJoin('Proyecto AS i', 'i.id', '=', 'g.proyecto_id') // Se mantiene el join sin condiciones adicionales
      ->where(function ($query) {
        $query->where('i.estado', 1)
          ->orWhereNull('i.id'); // Permitir registros sin proyecto
      })
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'b.tipo',
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'f.nombre AS facultad',
        DB::raw("CASE
              WHEN c.programa LIKE 'E.P.%' THEN 'Licenciatura o Segunda Especialidad'
              WHEN c.programa LIKE 'Maest%' THEN 'Maestría'
              WHEN c.programa LIKE 'Doct%' THEN 'Doctorado'
              ELSE 'Licenciatura o Segunda Especialidad'
          END AS tipo_programa"),
        DB::raw("COUNT(h.id) AS cantidad_tesista")
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Adherente')
      ->where('b.tipo', 'LIKE', 'Estudiante%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->tipo,
          $item->facultad,
          "Participaciones como tesista: " . $item->cantidad_tesista
        ];
        $item->disabled = $item->cantidad_tesista > 0;
        return $item;
      });

    return $listado;
  }

  public function listadoExterno(Request $request) {
    $grupo = DB::table('Proyecto')
      ->select([
        'grupo_id'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'b.tipo',
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'c.nombre AS facultad',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Adherente')
      ->where('b.tipo', 'LIKE', 'Externo%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->tipo,
          $item->facultad,
        ];
        return $item;
      });

    return $listado;
  }

  public function listadoColaborador(Request $request) {
    $grupo = DB::table('Proyecto')
      ->select([
        'grupo_id'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $listado = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(b.doc_numero, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'b.tipo',
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'c.nombre AS facultad',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->where('a.condicion', '=', 'Adherente')
      ->where('b.tipo', 'LIKE', 'Estudiante%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->groupBy('b.id')
      ->limit(10)
      ->get()
      ->map(function ($item) {
        $item->tags = [
          $item->tipo,
          $item->facultad,
        ];
        return $item;
      });

    return $listado;
  }

  public function agregarIntegrante(Request $request) {
    $count = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->input('id'))
      ->where('investigador_id', '=', $request->input('investigador_id'))
      ->count();

    if ($count == 0) {
      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'grupo_id' => $request->input('grupo_id'),
          'investigador_id' => $request->input('investigador_id'),
          'grupo_integrante_id' => $request->input('grupo_integrante_id'),
          'proyecto_integrante_tipo_id' => $request->input('proyecto_integrante_tipo_id'),
          'tipo_tesis' => $request->input('tipo'),
          'titulo_tesis' => $request->input('titulo'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      return ['message' => 'success', 'detail' => 'Integrante añadido'];
    } else {
      return ['message' => 'error', 'detail' => 'No puede añadir al mismo integrante 2 veces'];
    }
  }

  public function eliminarIntegrante(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('proyecto_integrante_id', '=', $request->query('id'))
      ->delete();

    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Integrante eliminado'];
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
      ->leftJoin('Proyecto_descripcion AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.proyecto_id')
          ->where('codigo', '=', 'tipo_investigacion');
      })
      ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
      ->leftJoin('Linea_investigacion AS f', 'f.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Ocde AS g', 'g.id', '=', 'a.ocde_id')
      ->select([
        'a.periodo',
        'c.grupo_nombre',
        'd.nombre AS facultad',
        'e.nombre AS area',
        'b.detalle AS tipo_investigacion',
        'g.linea AS ocde',
        'a.palabras_clave',
        'a.codigo_proyecto',
        'a.titulo',
        'f.nombre AS linea',
        'a.localizacion',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

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

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->groupBy('b.id')
      ->get();

    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin',
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.partida',
        'b.tipo',
        'a.monto',
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('investigador.convocatorias.pconfigi_inv', [
      'proyecto' => $proyecto,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
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

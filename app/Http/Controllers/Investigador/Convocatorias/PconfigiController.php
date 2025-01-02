<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PconfigiController extends S3Controller {

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
      ->where('b.tipo_proyecto', '=', 'PCONFIGI')
      ->where('b.periodo', '=', 2025)
      ->get();

    return $listado;
  }

  public function validarDatos(Request $request) {
    $errores = [];
    $detail = null;

    /* DATOS GENERALES*/
    $datosGenerales = DB::table('Usuario_investigador AS i')
      ->select(['i.cti_vitae', 'i.codigo_orcid', 'i.google_scholar']) // Selecciona solo lo necesario
      ->whereNotNull('i.cti_vitae')
      ->where('i.cti_vitae', '!=', '')
      ->whereNotNull('i.codigo_orcid')
      ->where('i.codigo_orcid', '!=', '')
      ->whereNotNull('i.google_scholar')
      ->where('i.google_scholar', '!=', '')
      ->where('i.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->exists(); // Solo verifica existencia

    !$datosGenerales &&
      $errores[] = [
        'message' => 'Todos los docentes que participen en el concurso deben contar con: 
                      <b>Google Scholar</b> gestionado con el correo institucional con dominio unmsm.edu.pe, 
                      <b>CTI vitae</b> y <b>código Orcid</b> <br>
                      (<a href="https://vrip.unmsm.edu.pe/wp-content/uploads/2024/12/directiva_pconfigi_2025.pdf" target="_blank">ver Anexo 1</a>).',
        'isHtml' => true
      ];

    /* DEUDAS */
    $deudas = DB::table('view_deudores AS vdeuda')
      ->select(['vdeuda.ptipo', 'vdeuda.categoria', 'vdeuda.periodo'])
      ->where('vdeuda.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();
    if ($deudas->isNotEmpty()) {
      // Genera los detalles de las deudas
      $detallesDeuda = $deudas->map(function ($deuda) {
        return "Tipo: {$deuda->ptipo}, Categoría: {$deuda->categoria}, Período: {$deuda->periodo}";
      })->implode('<br>'); // Combina los detalles en una cadena con saltos de línea HTML

      // Agrega el mensaje de error
      $errores[] = [
        'message' => 'Existen deudas pendientes que deben ser resueltas para participar en el concurso:<br>' . $detallesDeuda,
        'isHtml' => true
      ];
    }
    /* TITULAR GI */
    $titularGI = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', function (JoinClause $join) {
        $join->on('b.investigador_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Titular');
      })
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $titularGI == 0 && $errores[] = [

      'message' => 'Necesita ser titular de un grupo de investigación',
      'isHtml' => false
    ];

    /* PROYECTO ACTUAL */
    $proyectoActual = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->where('a.condicion', '=', 'Responsable')
      ->where('b.tipo_proyecto', '=', 'PCONFIGI')
      ->where('b.periodo', '=', 2025)
      // ->where('b.estado', '!=', 6)
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $proyectoActual > 0 && $errores[] = [
      'message' => "Actualmente, cuenta con una propuesta de proyecto PCONFIGI 2025 en proceso como Responsable, por lo que no es posible registrar nuevos proyectos en esta categoría. No obstante, puede participar como Miembro Docente en otro proyecto del mismo tipo.",
      'isHtml' => false
    ];


    return ['estado' => empty($errores), 'errores' => $errores];
  }

  public function verificar(Request $request, $proyecto_id = null) {
    $errores = [];
    $detail = null;

    $datosGenerales = DB::table('Usuario_investigador AS i')
      ->select(['i.cti_vitae', 'i.codigo_orcid', 'i.google_scholar']) // Selecciona solo lo necesario
      ->whereNotNull('i.cti_vitae')
      ->where('i.cti_vitae', '!=', '')
      ->whereNotNull('i.codigo_orcid')
      ->where('i.codigo_orcid', '!=', '')
      ->whereNotNull('i.google_scholar')
      ->where('i.google_scholar', '!=', '')
      ->where('i.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->exists(); // Solo verifica existencia

    !$datosGenerales &&
      $errores[] = [
        'message' => 'Todos los docentes que participen en el concurso deben contar con: 
                      <b>Google Scholar</b> gestionado con el correo institucional con dominio unmsm.edu.pe, 
                      <b>CTI vitae</b> y <b>código Orcid</b> <br>
                      (<a href="https://vrip.unmsm.edu.pe/wp-content/uploads/2024/12/directiva_pconfigi_2025.pdf" target="_blank">ver Anexo 1</a>).',
        'isHtml' => true
      ];


    $deudas = DB::table('view_deudores AS vdeuda')
      ->select(['vdeuda.ptipo', 'vdeuda.categoria', 'vdeuda.periodo'])
      ->where('vdeuda.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    // Verifica si existen deudas
    if ($deudas->isNotEmpty()) {
      // Genera los detalles de las deudas
      $detallesDeuda = $deudas->map(function ($deuda) {
        return "Tipo: {$deuda->ptipo}, Categoría: {$deuda->categoria}, Período: {$deuda->periodo}";
      })->implode('<br>'); // Combina los detalles en una cadena con saltos de línea HTML

      // Agrega el mensaje de error
      $errores[] = [
        'message' => 'Existen deudas pendientes que deben ser resueltas para participar en el concurso:<br>' . $detallesDeuda,
        'isHtml' => true
      ];
    }

    //  Ser titular de un grupo de investigación
    $req1 = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', function (JoinClause $join) {
        $join->on('b.investigador_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Titular');
      })
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    $req1 == 0 && $errores[] = [
      'message' => 'Necesita ser titular de un grupo de investigación',
      'isHtml' => false
    ];


    if ($proyecto_id != null) {
      $req2 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->where('a.proyecto_id', '=', $proyecto_id)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PCONFIGI')
        ->count();

      $req2 == 0 && $errores[] = [
        'message' => 'Necesita ser responsable de un proyecto',
        'isHtml' => false
      ];
    } else {
      $req3 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PCONFIGI')
        ->where('b.periodo', '=', 2025)
        ->where('b.estado', '!=', 6)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->count();

      $req3 > 0 && $errores[] = [
        'message' => 'Ya se encuentra inscrito en un proyecto',
        'isHtml' => false
      ];

      $detail = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->select([
          'b.id',
          'b.step'
        ])
        ->where('a.condicion', '=', 'Responsable')
        ->where('b.tipo_proyecto', '=', 'PCONFIGI')
        ->where('b.id', '=', $proyecto_id)
        ->where('b.periodo', '=', 2025)
        ->where('b.estado', '=', 6)
        ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();
    }

    if (!empty($errores)) {
      return ['estado' => false, 'errores' => $errores];
    } else {
      if ($detail == null) {
        return ['estado' => true];
      } else {
        return ['estado' => true, 'id' => $detail->id, 'step' => $detail->step];
      }
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
        'e.grupo_nombre'
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.grupo_id', '=', $datos->grupo_id)
      // ->whereNull('a.concytec_codigo')
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
        ->join('Proyecto_descripcion AS b', function (JoinClause $join) {
          $join->on('a.id', '=', 'b.proyecto_id')
            ->where('codigo', '=', 'tipo_investigacion');
        })
        ->select([
          'a.linea_investigacion_id',
          'a.ocde_id',
          'a.titulo',
          'a.localizacion',
          'b.detalle AS tipo_investigacion'
        ])
        ->where('a.id', '=', $request->query('id'))
        ->first();

      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'proyecto' => $proyecto,
        'ocde' => $ocde,
      ];
    } else {
      return [
        'estado' => true,
        'datos' => $datos,
        'lineas' => $lineas,
        'ocde' => $ocde,
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
          'tipo_proyecto' => 'PCONFIGI',
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

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'proyecto_integrante_tipo_id' => 1,
          'grupo_id' => $datos->grupo_id,
          'grupo_integrante_id' => $datos->id,
          'condicion' => 'Responsable',
        ]);

      // DB::table('Proyecto_presupuesto')
      //   ->insert([
      //     'proyecto_id' => $id,
      //     'partida_id' => 61,
      //     'justificacion' => '',
      //     'monto' => 0,
      //     'created_at' => $date,
      //     'updated_at' => $date,
      //   ]);

      return ['message' => 'success', 'detail' => 'Datos guardados', 'id' => $id];
    }
  }

  public function verificar2(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $documentos = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    return [
      'estado' => true,
      'documentos' => $documentos,
    ];
  }

  public function agregarDoc(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $date1 = Carbon::now();
      $date_format =  $date1->format('Ymd-His');
      $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();

      $this->uploadFile($request->file('file'), "proyecto-doc", $name);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $request->input('proyecto_id'),
          'categoria' => 'documento',
          'tipo' => 25,
          'nombre' => 'Documento de colaboración externa',
          'comentario' => $date,
          'archivo' => $name,
          'estado' => 1
        ]);

      return ['message' => 'success', 'detail' => 'Documento cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'No se pudo cargar el documento'];
    }
  }

  public function eliminarDoc(Request $request) {
    DB::table('Proyecto_doc')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Documento eliminado correctamente'];
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
        'contribucion_impacto',
        'hipotesis',
        'objetivos',
        'metodologia_trabajo',
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
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('tabla', '=', 'Proyecto')
      ->where('tabla_id', '=', $request->query('id'))
      ->where('bucket', '=', 'proyecto-doc')
      ->where('recurso', '=', 'METODOLOGIA_TRABAJO')
      ->where('estado', '=', 1)
      ->first();

    return [
      'estado' => true,
      'descripcion' => $descripcion,
      'palabras_clave' => $palabras_clave->palabras_clave,
      'archivos' => [
        'metodologia' => $archivo1?->url,
      ]
    ];
  }

  public function registrar3(Request $request) {
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'resumen_ejecutivo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('resumen_ejecutivo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'antecedentes', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('antecedentes')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'justificacion', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('justificacion')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'contribucion_impacto', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('contribucion_impacto')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'hipotesis', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('hipotesis')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'objetivos', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('objetivos')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'metodologia_trabajo', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('metodologia_trabajo')]);
    DB::table('Proyecto_descripcion')->updateOrInsert(['codigo' => 'referencias_bibliograficas', 'proyecto_id' => $request->input('id')], ['detalle' => $request->input('referencias_bibliograficas')]);

    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'step' => 4,
      ]);

    if ($request->hasFile('file1')) {
      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
      $this->uploadFile($request->file('file1'), "proyecto-doc", $name);

      DB::table('File')
        ->where('tabla', '=', 'Proyecto')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('recurso', '=', 'METODOLOGIA_TRABAJO')
        ->where('estado', '=', 1)
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Proyecto',
          'tabla_id' => $request->input('id'),
          'bucket' => 'proyecto-doc',
          'key' => $name,
          'recurso' => 'METODOLOGIA_TRABAJO',
          'estado' => 1,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    return ['message' => 'success', 'detail' => 'Datos guardados'];
  }

  public function verificar4(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $actividades = DB::table('Proyecto_actividad')
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
    $errores = [];

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
        'a.justificacion'
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
      ->where('a.tipo_proyecto', '=', 'PCONFIGI')
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

  public function verificarPresupuesto(Request $request) {

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->select([
        'a.id',
        'a.proyecto_id',
        'a.partida_id',
        'a.tipo',
        'a.monto'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->orderBy('a.partida_id')
      ->get();

    return $presupuesto;
  }
  public function agregarPartida(Request $request) {
    $date = Carbon::now();

    DB::table('Proyecto_presupuesto')
      ->insert([
        'proyecto_id' => $request->input('id'),
        'partida_id' => $request->input('partida')["value"],
        'monto' => $request->input('monto'),
        'justificacion' => $request->input('justificacion'),
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
        'justificacion' => $request->input('justificacion'),
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

  public function verificar6(Request $request) {
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

    return [
      'estado' => true,
      'data' => $data,
    ];
  }

  public function verificar7(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis',
        'a.excluido'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return ['estado' => true, 'integrantes' => $integrantes];
  }

  public function listadoGrupoDocente(Request $request) {
    $grupo = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        DB::raw("CONCAT(b.tipo, ' - ' , a.condicion, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'a.grupo_id'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('b.tipo', 'LIKE', '%DOCENTE%')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function listadoGrupoExterno(Request $request) {
    $grupo = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        DB::raw("CONCAT(b.tipo, ' - ' , a.condicion, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'a.grupo_id'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('b.tipo', '=', 'Externo')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function listadoGrupoEstudiante(Request $request) {
    $grupo = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->first();

    $listado = Db::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        DB::raw("CONCAT(b.tipo, ' - ' , a.condicion, ' | ', b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS value"),
        'a.investigador_id',
        'a.id AS grupo_integrante_id',
        'a.grupo_id'
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('b.tipo', 'LIKE', 'Estudiante%')
      ->limit(10)
      ->get();

    return $listado;
  }

  public function agregarIntegrante(Request $request) {
    $tipoIntegrante = $request->input('proyecto_integrante_tipo_id');
    $responsable = 0;
    $corresponsable = 0;
    $miembroDocente = 0;
    $colaboradorExterno = 0;
    $tesista = 0;
    $colaborador = 0;



    $participacion = DB::table('Proyecto as a')
      ->join('Proyecto_integrante as b', 'a.id', '=', 'b.proyecto_id')
      ->where('b.investigador_id', '=', $request->input('investigador_id'))
      ->where('b.proyecto_integrante_tipo_id', '=', $tipoIntegrante)
      ->where('a.tipo_proyecto', '=', 'PCONFIGI')
      ->where('a.periodo', '=', 2025)
      ->get();

    $investigadorProyecto = DB::table('Proyecto as a')
      ->join('Proyecto_integrante as b', 'a.id', '=', 'b.proyecto_id')
      ->where('b.investigador_id', '=', $request->input('investigador_id'))
      ->where('b.proyecto_id', '=', $request->input('id'))
      ->where('a.tipo_proyecto', '=', 'PCONFIGI')
      ->where('a.periodo', '=', 2025)
      ->count();

    if ($tipoIntegrante == 5) {

      $tesistaProyecto = DB::table('Proyecto_integrante as a')
        ->join('Proyecto_integrante_tipo as c', 'a.proyecto_integrante_tipo_id', '=', 'c.id')
        ->where('a.investigador_id', '=', $request->input('investigador_id'))
        ->whereIn('c.id', [5, 11, 16, 18, 20, 40, 47, 59, 67, 77])
        ->count();
    }

    $deudas = DB::table('view_deudores AS vdeuda')
      ->select(['vdeuda.ptipo', 'vdeuda.categoria', 'vdeuda.periodo'])
      ->where('vdeuda.investigador_id', '=', $request->input('investigador_id'))
      ->count();


    foreach ($participacion as $data) {

      switch ($data->proyecto_integrante_tipo_id) {
        case 1:
          $responsable++;
          break;
        case 2:
          $corresponsable++;
          break;
        case 3:
          $miembroDocente++;
          break;
        case 4:
          $colaboradorExterno++;
          break;
        case 5:
          $tesista++;
          break;
        case 6:
          $colaborador++;
          break;
      }
    }


    $numParticipacion = count($participacion);

    if ($numParticipacion == 0 && $investigadorProyecto == 0 && $tesistaProyecto == 0 && $deudas == 0) {

      if ($request->input('tipo_tesis') == null) {
        DB::table('Proyecto_integrante')
          ->insert([
            'proyecto_id' => $request->input('id'),
            'grupo_id' => $request->input('grupo_id'),
            'investigador_id' => $request->input('investigador_id'),
            'grupo_integrante_id' => $request->input('grupo_integrante_id'),
            'proyecto_integrante_tipo_id' => $request->input('proyecto_integrante_tipo_id'),
            'excluido' => 'Incluido',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
      } else {
        DB::table('Proyecto_integrante')
          ->insert([
            'proyecto_id' => $request->input('id'),
            'grupo_id' => $request->input('grupo_id'),
            'investigador_id' => $request->input('investigador_id'),
            'grupo_integrante_id' => $request->input('grupo_integrante_id'),
            'proyecto_integrante_tipo_id' => $request->input('proyecto_integrante_tipo_id'),
            'tipo_tesis' => $request?->input('tipo_tesis')["value"],
            'titulo_tesis' => $request->input('titulo_tesis'),
            'excluido' => 'Incluido',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);
      }

      return ['message' => 'success', 'detail' => 'Integrante añadido'];
    } else {
      if ($investigadorProyecto > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es parte del proyecto. Por favor, elija a otro integrante.'
        ];
      }

      if ($tesistaProyecto > 0) {
        return [
          'message' => 'error',
          'detail' => 'No seran elegibles los estudiantes que esten participando como Tesista en proyectos en curso o que haya concluido recientemente.'
        ];
      }
      if ($deudas > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado tiene deudas pendientes con la Universidad. Por favor, elija a otro integrante.'
        ];
      }

      if ($responsable > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es Responsable de un proyecto. Por favor, elija a otro integrante.'
        ];
      } elseif ($corresponsable > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es Co-rresponsable de un proyecto. Por favor, elija a otro integrante.'
        ];
      } elseif ($miembroDocente > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es Miembro docente de un proyectos. Por favor, elija a otro integrante.'
        ];
      } elseif ($colaboradorExterno > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es Colaborador Externo de un proyecto. Por favor, elija a otro integrante.'
        ];
      } elseif ($tesista > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es Tesista de un proyecto. Por favor, elija a otro integrante.'
        ];
      } elseif ($colaborador > 0) {
        return [
          'message' => 'error',
          'detail' => 'El integrante seleccionado ya es Colaborador de un proyecto. Por favor, elija a otro integrante.'
        ];
      }
    }
  }

  public function eliminarIntegrante(Request $request) {
    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Integrante eliminado'];
  }

  public function verificar8(Request $request) {
    $res1 = $this->verificar($request, $request->query('id'));
    if (!$res1["estado"]) {
      return $res1;
    }

    return ['estado' => true];
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Grupo AS b', function (JoinClause $join) {
        $join->on('a.grupo_id', '=', 'b.id')
          ->join('Facultad AS b1', 'b1.id', '=', 'b.facultad_id')
          ->join('Area AS b2', 'b2.id', '=', 'b1.area_id');
      })
      ->leftJoin('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
      ->select([
        //  Grupo
        'b.grupo_nombre',
        'b1.nombre AS facultad',
        'b2.nombre AS area',
        'c.linea AS ocde',
        //  Proyecto
        'a.codigo_proyecto',
        'a.titulo',
        'd.nombre AS linea',
        'a.localizacion',
        'a.palabras_clave',
        'a.tipo_proyecto',
        'a.updated_at',
        'a.periodo',
        DB::raw("CASE(a.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluación'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecucion'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluido'
          ELSE 'Sin estado'
        END AS estado")
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

    $calendario = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.partida',
        'a.justificacion',
        'a.monto',
        'b.tipo'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select([
        'b.nombre AS condicion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) AS nombres"),
        'c.tipo',
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.convocatorias.pconfigi', [
      'proyecto' => $proyecto,
      'detalles' => $detalles,
      'calendario' => $calendario,
      'presupuesto' => $presupuesto,
      'integrantes' => $integrantes
    ]);
    return $pdf->stream();
  }

  public function enviar(Request $request) {
    $count = DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 6)
      ->update([
        'estado' => 5
      ]);

    if ($count > 0) {
      return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
    } else {
      return ['message' => 'error', 'detail' => 'Ya ha enviado su solicitud'];
    }
  }
}

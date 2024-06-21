<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProCTIController extends S3Controller {
  public function verificar(Request $request) {
    $errores = [];

    $perteneceGrupo = DB::table('Grupo_integrante')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->count();

    if ($perteneceGrupo == 0) {
      $errores[] = 'Tiene que pertenecer a algún grupo de investigación.';
    }

    $proyecto = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'a.proyecto_id',
        'b.estado',
        'b.step'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('b.periodo', '=', '2024')
      ->first();

    if ($proyecto != null) {
      if ($proyecto->estado != 6) {
        $errores[] = 'Ya es participante en otro proyecto PRO-CTI de este año.';
      } else {
        return ['estado' => true, 'paso' => 'paso' . $proyecto->step . '?proyecto_id=' . $proyecto->proyecto_id];
      }
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      return ['estado' => true, 'paso' => 'paso1'];
    }
  }

  public function getDataToPaso1(Request $request) {
    $data = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        'd.nombre AS facultad',
        'b.grupo_nombre',
        'b.id AS grupo_id'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.grupo_id', '=', $data->grupo_id)
      ->whereNull('a.concytec_codigo')
      ->get();

    $ocdeLevel1 = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label")
      ])
      ->whereNull('parent_id')
      ->get();

    return ['data' => $data, 'lineas' => $lineas, 'ocde1' => $ocdeLevel1];
  }

  public function getOcde(Request $request) {
    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label")
      ])
      ->where('parent_id', '=', $request->query('parent_id'))
      ->get();

    return $ocde;
  }

  public function getOds(Request $request) {
    $ods = DB::table('Ods AS a')
      ->join('Linea_investigacion_ods AS b', 'b.ods_id', '=', 'a.id')
      ->select([
        'a.descripcion AS value'
      ])
      ->where('b.linea_investigacion_id', '=', $request->query('linea_investigacion_id'))
      ->get();

    return $ods;
  }

  public function registrarPaso1(Request $request) {
    $data = DB::table('Grupo_integrante')
      ->select([
        'facultad_id',
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->first();

    $id = DB::table('Proyecto')
      ->insertGetId([
        'facultad_id' => $data->facultad_id,
        'grupo_id' => $data->grupo_id,
        'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"],
        'ocde_id' => $request->input('ocde_3')["value"],
        'titulo' => $request->input('titulo'),
        'tipo_proyecto' => 'PRO-CTIE',
        'fecha_inscripcion' => Carbon::now(),
        'localizacion' => $request->input('localizacion')["value"],
        'periodo' => 2024,
        'convocatoria' => 1,
        'step' => 2,
        'estado' => 6,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'objetivo_ods',
        'detalle' => $request->input('ods')["value"]
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $id,
        'codigo' => 'tipo_investigacion',
        'detalle' => $request->input('tipo_investigacion')["value"]
      ]);

    DB::table('Proyecto_integrante')
      ->insert([
        'proyecto_id' => $id,
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'condicion' => 'Responsable',
        'proyecto_integrante_tipo_id' => 86,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'proyecto_id' => $id];
  }

  //  Paso 2
  public function getDataPaso2(Request $request) {
    $s3 = $this->s3Client;

    $data = DB::table('Usuario_investigador')
      ->select([
        DB::raw("CONCAT(apellido1, ' ', apellido2, ', ', nombres) AS nombres"),
        'doc_numero',
        'doc_numero',
        'fecha_nac',
        'codigo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS docente_categoria"),
        'codigo_orcid',
        'renacyt',
        'cti_vitae'
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    //  Formato
    $cmd = $s3->getCommand('GetObject', [
      'Bucket' => 'templates',
      'Key' => 'compromiso-confidencialidad.docx'
    ]);

    //  Generar url temporal
    $data->url = (string) $s3->createPresignedRequest($cmd, '+5 minutes')->getUri();

    return $data;
  }

  public function registrarPaso2(Request $request) {
    $id = $request->input('proyecto_id');
    $date = Carbon::now();
    $name = $id . "-" . $date->format('Ymd-His');

    if ($request->hasFile('file')) {
      $nameFile = $id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

      DB::table('Proyecto')
        ->where('id', '=', $id)
        ->update([
          'step' => 3,
        ]);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $id,
          'categoria' => 'carta',
          'tipo' => 29,
          'nombre' => 'Carta de compromiso del asesor',
          'comentario' => $date,
          'archivo' => $nameFile
        ]);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  //  Paso 3
  public function listarIntegrantes(Request $request) {
    $s3 = $this->s3Client;

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('File AS e', function ($join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.estado', '=', 1);
      })
      ->select([
        'a.id',
        'b.nombre AS tipo_integrante',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombre"),
        'c.tipo',
        'd.nombre AS facultad',
        'e.bucket',
        'e.key',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    // foreach ($integrantes as $integrante) {
    //   $url = null;
    //   $cmd = $s3->getCommand('GetObject', [
    //     'Bucket' => $integrante->bucket,
    //     'Key' => $integrante->key
    //   ]);
    //   //  Generar url temporal
    //   $url = (string) $s3->createPresignedRequest($cmd, '+60 minutes')->getUri();

    //   $integrante->url = $url;
    //   unset($integrante->bucket);
    //   unset($integrante->key);
    // }

    return $integrantes;
  }

  public function searchEstudiante(Request $request) {
    $estudiantes = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno',
        'a.dni',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.facultad',
        'a.programa',
        'a.permanencia',
        'a.correo_electronico'
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function verificarEstudiante(Request $request) {
    $errores = [];

    $participaProyecto = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->where('c.codigo', '=', $request->query('codigo'))
      ->where('b.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('b.periodo', '=', '2024')
      ->count();

    if ($participaProyecto > 0) {
      $errores[] = 'Ya es participante en otro proyecto PRO-CTI de este año.';
    }

    if (!empty($errores)) {
      return ['message' => 'error', 'detail' => $errores[0]];
    } else {
      return ['message' => 'success', 'detail' => 'No ha participado de ningún proyecto PRO-CTIE este año'];
    }
  }

  public function agregarIntegrante(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();

      DB::table('Proyecto')
        ->where('id', '=', $request->input('proyecto_id'))
        ->update([
          'step' => 3,
        ]);

      $id_investigador = $request->input('investigador_id');

      if ($id_investigador == "null") {

        $sumData = DB::table('Repo_sum')
          ->select([
            'id_facultad',
            'codigo_alumno',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'dni',
            'sexo',
            'correo_electronico',
          ])
          ->where('id', '=', $request->input('sum_id'))
          ->first();

        $id_investigador = DB::table('Usuario_investigador')
          ->insertGetId([
            'facultad_id' => $sumData->id_facultad,
            'codigo' => $sumData->codigo_alumno,
            'nombres' => $sumData->nombres,
            'apellido1' => $sumData->apellido_paterno,
            'apellido2' => $sumData->apellido_materno,
            'doc_tipo' => 'DNI',
            'doc_numero' => $sumData->dni,
            'sexo' => $sumData->sexo,
            'email3' => $sumData->correo_electronico,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'tipo_investigador' => 'Estudiante'
          ]);
      }

      $id = DB::table('Proyecto_integrante')
        ->insertGetId([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $id_investigador,
          'condicion' => 'Colaborador',
          'proyecto_integrante_tipo_id' => 88,
          'created_at' => $date,
          'updated_at' => $date
        ]);

      $name = $date->format('Ymd-His') . "-" . $id;

      $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

      DB::table('File')
        ->insert([
          'tabla_id' => $id,
          'tabla' => 'Proyecto_integrante',
          'bucket' => 'carta-compromiso',
          'key' => $nameFile,
          'recurso' => 'CARTA_COMPROMISO',
          'estado' => 1,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      $this->uploadFile($request->file('file'), "carta-compromiso", $nameFile);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function eliminarIntegrante(Request $request) {
    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    DB::table('File')
      ->where('tabla_id', '=', $request->query('id'))
      ->where('tabla', '=', 'Proyecto_integrante')
      ->where('estado', '=', 1)
      ->update([
        'estado' => -1,
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Integrante eliminado correctamente'];
  }

  public function registrarPaso4(Request $request) {

    $palabrasConcatenadas = "";
    foreach ($request->input('palabras_clave') as $palabra) {
      $palabrasConcatenadas = $palabrasConcatenadas . $palabra["label"] . ",";
    }
    $palabrasConcatenadas = rtrim($palabrasConcatenadas, ',');

    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'palabras_clave' => $palabrasConcatenadas,
        'step' => 5,
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'resumen_ejecutivo',
        'detalle' => $request->input('resumen'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'antecedentes',
        'detalle' => $request->input('antecedentes'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'justificacion',
        'detalle' => $request->input('justificacion'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'contribucion',
        'detalle' => $request->input('contribucion'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'objetivos',
        'detalle' => $request->input('objetivos'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'metodologia_trabajo',
        'detalle' => $request->input('metodologia'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'hipotesis',
        'detalle' => $request->input('hipotesis'),
      ]);

    DB::table('Proyecto_descripcion')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo' => 'referencias_bibliograficas',
        'detalle' => $request->input('referencias'),
      ]);

    return ['message' => 'success', 'detail' => 'Datos cargados correctamente'];
  }

  //  Paso 5
  public function listarActividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
  }

  public function agregarActividad(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'step' => 5,
      ]);

    DB::table('Proyecto_actividad')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'actividad' => $request->input('actividad'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
      ]);

    return ['message' => 'success', 'detail' => 'Actividad registrada correctamente'];
  }

  public function eliminarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Actividad eliminada correctamente'];
  }

  public function listarPartidas(Request $request) {
    $partidas = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.tipo',
        'b.partida',
        'a.monto'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $monto_disponible = 25000;
    foreach ($partidas as $partida) {
      $monto_disponible = $monto_disponible - $partida->monto;
    }

    return ['presupuesto' => $partidas, 'monto_disponible' => $monto_disponible];
  }

  public function listarTiposPartidas(Request $request) {
    $partidas = DB::table('Partida_proyecto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label")
      ])
      ->where('a.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('b.tipo', '=', $request->query('tipo'))
      ->where('a.postulacion', '=', 1)
      ->get();

    return $partidas;
  }

  public function agregarPartida(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'step' => 6,
      ]);

    $cuenta = DB::table('Proyecto_presupuesto')
      ->select([
        'id',
        'monto'
      ])
      ->where('partida_id', '=', $request->input('partida_id'))
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->get();

    if (sizeof($cuenta) > 0) {
      DB::table('Proyecto_presupuesto')
        ->where('id', '=', $cuenta[0]->id)
        ->update([
          'monto' => $cuenta[0]->monto + $request->input('monto'),
          'updated_at' => Carbon::now()
        ]);
    } else {
      DB::table('Proyecto_presupuesto')
        ->insert([
          'partida_id' => $request->input('partida_id'),
          'proyecto_id' => $request->input('proyecto_id'),
          'monto' => $request->input('monto'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
    }

    return ['message' => 'success', 'detail' => 'Partida registrada correctamente'];
  }

  public function eliminarPartida(Request $request) {
    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Partida eliminada correctamente'];
  }

  public function enviarProyecto(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'estado' => 5
      ]);

    return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
  }
}

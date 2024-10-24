<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformesTecnicosController extends S3Controller {
  public function proyectosListado(Request $request) {
    if ($request->query('lista') == 'nuevos') {
      $responsable = DB::table('Proyecto_integrante AS a')
        ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select(
          'a.proyecto_id',
          DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
        )
        ->where('condicion', '=', 'Responsable');

      //  TODO - Incluir deuda dentro de otra consulta para una nueva tabla en la UI
      $proyectos = DB::table('Proyecto AS a')
        ->leftJoin('Informe_tecnico AS b', 'b.proyecto_id', '=', 'a.id')
        ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
        ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
        ->select(
          'a.id',
          'a.tipo_proyecto',
          'a.codigo_proyecto',
          'a.titulo',
          DB::raw('COUNT(b.id) AS cantidad_informes'),
          'res.responsable',
          'c.nombre AS facultad',
          'a.periodo',
          DB::raw("CASE(b.estado)
            WHEN 0 THEN 'En proceso'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Presentado'
            WHEN 3 THEN 'Observado'
            ELSE 'No tiene informe'
          END AS estado")
        )
        ->where('a.estado', '>', 0)
        ->groupBy('a.id')
        ->get();

      return $proyectos;
    } else {
      $responsable = DB::table('Proyecto_integrante_H AS a')
        ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select(
          'a.proyecto_id',
          DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
        )
        ->where('condicion', '=', 'Responsable');

      $proyectos = DB::table('Proyecto_H AS a')
        ->leftJoin('Informe_tecnico_H AS b', 'b.proyecto_id', '=', 'a.id')
        ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
        ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
        ->select(
          'a.id',
          'a.tipo AS tipo_proyecto',
          'a.codigo AS codigo_proyecto',
          'a.titulo',
          DB::raw('COUNT(b.id) AS cantidad_informes'),
          'res.responsable',
          'c.nombre AS facultad',
          'a.periodo',
          DB::raw("CASE(b.estado)
            WHEN 0 THEN 'En proceso'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Presentado'
            WHEN 3 THEN 'Observado'
            ELSE 'No tiene informe'
          END AS estado")
        )
        ->where('a.estado', '>', 0)
        ->groupBy('a.id')
        ->get();
    }
  }

  public function informes($proyecto_id) {
    $informes = DB::table('Informe_tecnico AS a')
      ->leftJoin('Informe_tipo AS b', 'b.id', '=', 'a.informe_tipo_id')
      ->select(
        'a.id',
        'b.informe',
        'a.estado',
        'a.fecha_envio',
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->get();

    return $informes;
  }

  public function getDataInforme(Request $request) {
    $actividades = [];
    $detalles = DB::table('Informe_tecnico AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'b.id AS proyecto_id',
        'b.codigo_proyecto',
        'b.tipo_proyecto',
        'b.titulo',
        'b.periodo',
        'b.resolucion_rectoral',
        'c.nombre AS facultad',
        'a.*',
      ])
      ->where('a.id', '=', $request->query('informe_tecnico_id'))
      ->first();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $detalles->proyecto_id)
      ->where('estado', '=', 1);

    switch ($detalles->tipo_proyecto) {
      case "ECI":
        $archivos = $archivos
          ->where('nombre', '=', 'Anexos proyecto ECI')
          ->get()
          ->mapWithKeys(function ($item) {
            return [$item->categoria => $item->url];
          });
        break;
      case "PCONFIGI":
      case "PCONFIGI-INV":
      case "PSINFINV":
      case "PSINFIPU":
      case "PTPBACHILLER":
      case "PTPDOCTO":
        $archivos = $archivos
          ->get()
          ->mapWithKeys(function ($item) {
            return [$item->categoria => $item->url];
          });
        break;
      case "PMULTI":
      case "PINTERDIS":
        $archivos = $archivos
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
          ->where('a.proyecto_id', '=', $detalles->proyecto_id)
          ->get();
        break;
    }

    return ['detalles' => $detalles, 'archivos' => $archivos, 'actividades' => $actividades];
  }

  public function updateInforme(Request $request) {
    $data = $request->all();

    // Función para convertir "null" string a null
    $data = array_map(function ($item) {
      return $item === "null" || $item === "" ? null : $item;
    }, $data);

    $request->merge($data);

    DB::table('Informe_tecnico')
      ->where('id', '=', $request->input('informe_tecnico_id'))
      ->update([
        'estado' => $request->input('estado'),
        'fecha_presentacion' => $request->input('fecha_presentacion'),
        'registro_nro_vrip' => $request->input('registro_nro_vrip'),
        'fecha_registro_csi' => $request->input('fecha_registro_csi'),
        'observaciones' => $request->input('observaciones'),
        'observaciones_admin' => $request->input('observaciones_admin'),
        'resumen_ejecutivo' => $request->input('resumen_ejecutivo'),
        'palabras_clave' => $request->input('palabras_clave'),
        'fecha_evento' => $request->input('fecha_evento'),
        'fecha_informe_tecnico' => $request->input('fecha_informe_tecnico'),
        'objetivos_taller' => $request->input('objetivos_taller'),
        'resultados_taller' => $request->input('resultados_taller'),
        'propuestas_taller' => $request->input('propuestas_taller'),
        'conclusion_taller' => $request->input('conclusion_taller'),
        'recomendacion_taller' => $request->input('recomendacion_taller'),
        'asistencia_taller' => $request->input('asistencia_taller'),
        'infinal1' => $request->input('infinal1'),
        'infinal2' => $request->input('infinal2'),
        'infinal3' => $request->input('infinal3'),
        'infinal4' => $request->input('infinal4'),
        'infinal5' => $request->input('infinal5'),
        'infinal6' => $request->input('infinal6'),
        'infinal7' => $request->input('infinal7'),
        'infinal8' => $request->input('infinal8'),
        'infinal9' => $request->input('infinal9'),
        'infinal10' => $request->input('infinal10'),
        'infinal11' => $request->input('infinal11'),
        'estado_trabajo' => $request->input('estado_trabajo'),
      ]);

    return [
      'message' => 'success',
      'detail' => 'Informe actualizado exitosamente',
    ];
  }

  public function loadActividad(Request $request) {
    if ($request->hasFile('file')) {
      $proyecto = DB::table("Informe_tecnico")
        ->select([
          'proyecto_id'
        ])
        ->where('id', '=', $request->input('informe_id'))
        ->first();

      $date = Carbon::now();
      $date1 = Carbon::now();

      $name = $date1->format('Ymd-His');
      $nameFile = $proyecto->proyecto_id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

      DB::table('Proyecto_doc')
        ->updateOrInsert([
          'proyecto_id' => $proyecto->proyecto_id,
          'nombre' => 'Actividades',
          'categoria' => 'actividad' . $request->input('indice'),
        ], [
          'comentario' => $date,
          'archivo' => $nameFile,
          'estado' => 1
        ]);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }
}

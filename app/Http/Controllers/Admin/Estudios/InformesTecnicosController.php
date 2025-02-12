<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

      $deuda = DB::table('Proyecto_integrante AS a')
        ->leftJoin('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
        ->select([
          'a.proyecto_id',
          DB::raw("CASE
          WHEN (b.tipo IS NULL OR b.tipo <= 0) THEN 'NO'
          WHEN b.tipo > 0 AND b.tipo <= 3 THEN 'SI'
          WHEN b.tipo > 3 THEN 'SUBSANADA'
        END AS deuda"),
          'b.categoria'
        ])
        ->groupBy('a.proyecto_id');

      //  TODO - Incluir deuda dentro de otra consulta para una nueva tabla en la UI
      $proyectos = DB::table('Proyecto AS a')
        ->leftJoin('Informe_tecnico AS b', 'b.proyecto_id', '=', 'a.id')
        ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
        ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
        ->leftJoinSub($deuda, 'deu', 'deu.proyecto_id', '=', 'a.id')
        ->select(
          'a.id',
          'a.tipo_proyecto',
          'a.codigo_proyecto',
          'a.titulo',
          'deu.deuda',
          'deu.categoria AS tipo_deuda',
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
        ->where('a.estado', '=', 1)
        ->where('a.tipo_proyecto', '!=', 'PFEX')
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
        ->where('a.condicion', '=', 'Responsable');

      $deuda = DB::table('Proyecto_integrante_H AS a')
        ->leftJoin('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_h_id', '=', 'a.id')
        ->select([
          'a.proyecto_id',
          DB::raw("CASE
          WHEN (b.tipo IS NULL OR b.tipo <= 0) THEN 'NO'
          WHEN b.tipo > 0 AND b.tipo <= 3 THEN 'SI'
          WHEN b.tipo > 3 THEN 'SUBSANADA'
        END AS deuda"),
          'b.categoria'
        ])
        ->groupBy('a.proyecto_id');

      $proyectos = DB::table('Proyecto_H AS a')
        ->leftJoin('Informe_tecnico_H AS b', 'b.proyecto_id', '=', 'a.id')
        ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
        ->leftJoin('Proyecto_integrante_H AS d', function (JoinClause $join) {
          $join->on('d.proyecto_id', '=', 'a.id')
            ->where('d.condicion', '=', 'Responsable');
        })
        ->leftJoin('Usuario_investigador AS e', 'e.id', '=', 'd.investigador_id')
        ->leftJoinSub($deuda, 'deu', 'deu.proyecto_id', '=', 'a.id')
        ->select(
          'a.id',
          'a.tipo AS tipo_proyecto',
          'a.codigo AS codigo_proyecto',
          'a.titulo',
          'deu.deuda',
          'deu.categoria AS tipo_deuda',
          DB::raw('COUNT(b.id) AS cantidad_informes'),
          DB::raw("CONCAT(e.apellido1, ' ', e.apellido2, ', ', e.nombres) AS responsable"),
          'c.nombre AS facultad',
          'a.periodo',
          DB::raw("CASE(b.status)
            WHEN 0 THEN 'En proceso'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Presentado'
            WHEN 3 THEN 'Observado'
            ELSE 'No tiene informe'
          END AS estado")
        )
        ->where('a.status', '>', 0)
        ->groupBy('a.id')
        ->get();

      return $proyectos;
    }
  }

  public function informes(Request $request) {
    if ($request->query('tabla') == "nuevos") {
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
        ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
        ->get();

      return $informes;
    } else {
      $informes = DB::table('Informe_tecnico_H AS a')
        ->leftJoin('Informe_tipo AS b', 'b.id', '=', 'a.tipo')
        ->select(
          'a.id',
          'b.informe',
          'a.status AS estado',
          'a.fecha_presentacion AS fecha_envio',
          'a.created_at',
          'a.updated_at'
        )
        ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
        ->get();

      return $informes;
    }
  }

  public function eliminarInforme(Request $request) {
    if ($request->query('tabla') == "nuevos") {
      DB::table('Informe_tecnico AS a')
        ->where('id', '=', $request->query('id'))
        ->delete();
    } else {
      DB::table('Informe_tecnico_H AS a')
        ->where('id', '=', $request->query('id'))
        ->delete();
    }

    return ['message' => 'info', 'detail' => 'Informe eliminado correctamente'];
  }

  public function getDataInforme(Request $request) {
    $proyecto = [];
    $actividades = [];
    $detalles = DB::table('Informe_tecnico AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id AS proyecto_id',
        'b.codigo_proyecto',
        'b.tipo_proyecto',
        'a.*',
      ])
      ->where('a.id', '=', $request->query('informe_tecnico_id'))
      ->first();

    $archivos = DB::table('Proyecto_doc')
      ->select([
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url"),
        'comentario'
      ])
      ->where('proyecto_id', '=', $detalles->proyecto_id)
      ->where('estado', '=', 1);

    $miembros = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->select([
        'b.codigo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ' ', b.nombres) AS nombres"),
        'c.nombre AS condicion',
        'b.tipo'
      ])
      ->where('a.proyecto_id', '=', $detalles->proyecto_id)
      ->get();

    switch ($detalles->tipo_proyecto) {
      case "ECI":
        $proyecto = DB::table('Proyecto AS a')
          ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
          ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
          ->select([
            'a.titulo',
            'a.codigo_proyecto',
            'a.tipo_proyecto',
            'a.resolucion_rectoral',
            'a.periodo',
            'c.grupo_nombre',
            'b.nombre AS facultad',
          ])
          ->where('a.id', '=', $detalles->proyecto_id)
          ->first();

        $archivos = $archivos
          ->where('nombre', '=', 'Anexos proyecto ECI')
          ->get()
          ->mapWithKeys(function ($item) {
            return [$item->categoria => [
              'url' => $item->url,
              'fecha' => $item->comentario
            ]];
          });
        break;
      case "PCONFIGI":
      case "PRO-CTIE":
      case "PCONFIGI-INV":
      case "PSINFINV":
      case "PSINFIPU":
      case "PTPBACHILLER":
      case "PTPDOCTO":
        $proyecto = DB::table('Proyecto AS a')
          ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
          ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
          ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
          ->leftJoin('Proyecto_descripcion AS e', function (JoinClause $join) {
            $join->on('e.proyecto_id', '=', 'a.id')
              ->where('e.codigo', '=', 'tipo_investigacion');
          })
          ->leftJoin('Proyecto_presupuesto AS f', 'f.proyecto_id', '=', 'a.id')
          ->select([
            'a.titulo',
            'a.codigo_proyecto',
            'a.tipo_proyecto',
            'a.resolucion_rectoral',
            'a.periodo',
            'c.grupo_nombre',
            'a.localizacion',
            'b.nombre AS facultad',
            'd.nombre AS linea',
            'e.detalle AS tipo_investigacion',
            DB::raw("SUM(f.monto) AS monto")
          ])
          ->where('a.id', '=', $detalles->proyecto_id)
          ->first();
        $archivos = $archivos
          ->get()
          ->mapWithKeys(function ($item) {
            return [$item->categoria => [
              'url' => $item->url,
              'fecha' => $item->comentario
            ]];
          });
        break;
      case "PMULTI":
      case "PINTERDIS":
      case "PINVPOS":
        $proyecto = DB::table('Proyecto AS a')
          ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
          ->leftJoin('Grupo AS c', 'c.id', '=', 'a.grupo_id')
          ->leftJoin('Linea_investigacion AS d', 'd.id', '=', 'a.linea_investigacion_id')
          ->leftJoin('Proyecto_presupuesto AS f', 'f.proyecto_id', '=', 'a.id')
          ->select([
            'a.titulo',
            'a.codigo_proyecto',
            'a.tipo_proyecto',
            'a.resolucion_rectoral',
            'a.periodo',
            'c.grupo_nombre',
            'a.localizacion',
            'b.nombre AS facultad',
            'd.nombre AS linea',
            DB::raw("SUM(f.monto) AS monto")
          ])
          ->where('a.id', '=', $detalles->proyecto_id)
          ->first();

        $archivos = $archivos
          ->get()
          ->mapWithKeys(function ($item) {
            return [$item->categoria => [
              'url' => $item->url,
              'fecha' => $item->comentario
            ]];
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

    return [
      'detalles' => $detalles,
      'archivos' => $archivos,
      'actividades' => $actividades,
      'proyecto' => $proyecto,
      'miembros' => $miembros
    ];
  }

  public function updateInforme(Request $request) {
    $data = $request->all();

    // Función para convertir "null" string a null
    $data = array_map(function ($item) {
      return $item === "null" || $item === "" ? null : $item;
    }, $data);

    $request->merge($data);

    $proyecto = DB::table('Informe_tecnico')
      ->select([
        'proyecto_id'
      ])
      ->where('id', '=', $request->input('informe_tecnico_id'))
      ->first();

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
        'updated_at' => Carbon::now()
      ]);

    $this->agregarAudit($request);

    $proyecto_id = $proyecto->proyecto_id;
    $date1 = Carbon::now();
    $date_format =  $date1->format('Ymd-His');

    if ($request->input('tipo_proyecto') == "ECI") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "anexo1");
      }

      if ($request->hasFile('file2')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
        $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "anexo2");
      }

      if ($request->hasFile('file3')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file3')->getClientOriginalExtension();
        $this->uploadFile($request->file('file3'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "anexo3");
      }

      if ($request->hasFile('file4')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file4')->getClientOriginalExtension();
        $this->uploadFile($request->file('file4'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "anexo4");
      }

      if ($request->hasFile('file5')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file5')->getClientOriginalExtension();
        $this->uploadFile($request->file('file5'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "anexo5");
      }

      if ($request->hasFile('file6')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file6')->getClientOriginalExtension();
        $this->uploadFile($request->file('file6'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "anexo6");
      }
    } else if ($request->input('tipo_proyecto') == "PCONFIGI") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PCONFIGI-INFORME", "Archivos de informe", 22);
      }

      if ($request->hasFile('file2')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
        $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "viabilidad", "Actividades", 65);
      }
    } else if ($request->input('tipo_proyecto') == "PCONFIGI-INV") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PCONFIGI-INV-INFORME", "Archivos de informe", 22);
      }
    } else if ($request->input('tipo_proyecto') == "PINTERDIS") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PINTERDIS-INFORME", "Archivos de informe", 22);
      }

      if ($request->hasFile('file2')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
        $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "articulo1", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
      }

      if ($request->hasFile('file3')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file3')->getClientOriginalExtension();
        $this->uploadFile($request->file('file3'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "articulo2", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
      }

      if ($request->hasFile('file4')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file4')->getClientOriginalExtension();
        $this->uploadFile($request->file('file4'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "tesis1", "Tesis sustentadas Pregrado.", 65);
      }

      if ($request->hasFile('file5')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file5')->getClientOriginalExtension();
        $this->uploadFile($request->file('file5'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "tesis2", "Tesis sustentadas Pregrado.", 65);
      }

      if ($request->hasFile('file6')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file6')->getClientOriginalExtension();
        $this->uploadFile($request->file('file6'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "tesis3", "Tesis sustentadas Pregrado.", 65);
      }

      if ($request->hasFile('file7')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file7')->getClientOriginalExtension();
        $this->uploadFile($request->file('file7'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "tesis4", "Tesis sustentadas Posgrado", 65);
      }

      if ($request->hasFile('file8')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file8')->getClientOriginalExtension();
        $this->uploadFile($request->file('file8'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "investigacion1", "Trabajos de investigación para obtener el grado de bachiller.", 65);
      }

      if ($request->hasFile('file9')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file9')->getClientOriginalExtension();
        $this->uploadFile($request->file('file9'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "investigacion2", "Trabajos de investigación para obtener el grado de bachiller.", 65);
      }

      if ($request->hasFile('file10')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file10')->getClientOriginalExtension();
        $this->uploadFile($request->file('file10'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "investigacion3", "Trabajos de investigación para obtener el grado de bachiller.", 65);
      }

      if ($request->hasFile('file11')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file11')->getClientOriginalExtension();
        $this->uploadFile($request->file('file11'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "investigacion4", "Trabajos de investigación para obtener el grado de bachiller.", 65);
      }

      if ($request->hasFile('file12')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file12')->getClientOriginalExtension();
        $this->uploadFile($request->file('file12'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "registro", "Formación de una red científica o el registro y/o inscripción al menos de una solicitud", 65);
      }
    } else if ($request->input('tipo_proyecto') == "PMULTI") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PMULTI-INFORME", "Archivos de informe", 22);
      }

      if ($request->hasFile('file2')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
        $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "articulo1", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
      }

      if ($request->hasFile('file3')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file3')->getClientOriginalExtension();
        $this->uploadFile($request->file('file3'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "articulo2", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
      }

      if ($request->hasFile('file4')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file4')->getClientOriginalExtension();
        $this->uploadFile($request->file('file4'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "articulo3", "Artículos publicados o aceptados en revistas indizadas a SCOPUS O WoS,o un libro,o dos capítulos de libro publicados en editoriales reconocido prestigio, de acuerdo con las normas internas de la universidad.", 65);
      }

      if ($request->hasFile('file5')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file5')->getClientOriginalExtension();
        $this->uploadFile($request->file('file5'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "capituloLibro1", "Capítulos de libros publicados en editoriales de reconocido prestigio, de acuerdo con las normas internas de la universidad", 65);
      }

      if ($request->hasFile('file6')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file6')->getClientOriginalExtension();
        $this->uploadFile($request->file('file6'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "capituloLibro2", "Capítulos de libros publicados en editoriales de reconocido prestigio, de acuerdo con las normas internas de la universidad", 65);
      }

      if ($request->hasFile('file7')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file7')->getClientOriginalExtension();
        $this->uploadFile($request->file('file7'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "tesis1", "Tesis sustentadas Pregrado", 65);
      }

      if ($request->hasFile('file8')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file8')->getClientOriginalExtension();
        $this->uploadFile($request->file('file8'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "tesis4", "Tesis sustentadas Posgrado", 65);
      }

      if ($request->hasFile('file9')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file9')->getClientOriginalExtension();
        $this->uploadFile($request->file('file9'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "registro", "Formación de una red científica o el registro y/o inscripción al menos de una solicitud", 65);
      }
    } else if ($request->input('tipo_proyecto') == "PRO-CTIE") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PRO-CTIE-INFORME", "Archivos de informe");
      }
      if ($request->hasFile('file2')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file2')->getClientOriginalExtension();
        $this->uploadFile($request->file('file2'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "viabilidad", "Actividades", 65);
      }
    } else if ($request->input('tipo_proyecto') == "PRO-CTIE") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PRO-CTIE-INFORME", "Archivos de informe");
      }
    } else if ($request->input('tipo_proyecto') == "PSINFINV") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PSINFINV-INFORME", "Archivos de informe", 22);
      }
    } else if ($request->input('tipo_proyecto') == "PSINFIPU") {
      if ($request->hasFile('file1')) {
        $name = $request->input('proyecto_id') . "/" . $date_format . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();
        $this->uploadFile($request->file('file1'), "proyecto-doc", $name);
        $this->updateFile($proyecto_id, $date1, $name, "informe-PSINFIPU-RESULTADOS", "Archivos de informe", 22);
      }
    }


    return [
      'message' => 'success',
      'detail' => 'Informe actualizado exitosamente',
    ];
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

  public function loadActividad(Request $request) {
    if ($request->hasFile('file')) {

      $proyecto = DB::table('Informe_tecnico')
        ->select([
          'proyecto_id'
        ])
        ->where('id', '=', $request->input('id'))
        ->first();

      $proyecto_id = $proyecto->proyecto_id;

      $date = Carbon::now();
      $date1 = Carbon::now();

      $name = $date1->format('Ymd-His');
      $nameFile = $proyecto_id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

      DB::table('Proyecto_doc')
        ->updateOrInsert([
          'proyecto_id' => $proyecto_id,
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

  /**
   * AUDITORÍA:
   * Dentro de la columna audit (JSON almacenado como string) se guardarán
   * los cambios de estado por parte de los administradores.
   */

  public function agregarAudit(Request $request) {
    $documento = DB::table('Informe_tecnico')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->input('informe_tecnico_id'))
      ->first();

    $audit = json_decode($documento->audit ?? "[]");

    $audit[] = [
      'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
      'nombres' => $request->attributes->get('token_decoded')->nombre,
      'apellidos' => $request->attributes->get('token_decoded')->apellidos,
      'accion' => 'Actualización de técnico'
    ];

    $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

    DB::table('Informe_tecnico')
      ->where('id', '=', $request->input('informe_tecnico_id'))
      ->update([
        'audit' => $audit
      ]);
  }

  public function verAuditoria(Request $request) {
    $documento = DB::table('Informe_tecnico')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $audit = json_decode($documento->audit ?? "[]");

    return $audit;
  }
}

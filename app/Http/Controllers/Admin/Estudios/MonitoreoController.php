<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Exports\Admin\FromDataExport;
use App\Exports\Admin\MonitoreoExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class MonitoreoController extends Controller {

  public function listadoProyectos() {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'b.proyecto_integrante_tipo_id')
      ->leftJoin('Proyecto_integrante_deuda AS d', 'd.proyecto_integrante_id', '=', 'b.id')
      ->join('Meta_tipo_proyecto AS e', function (JoinClause $join) {
        $join->on('e.tipo_proyecto', '=', 'a.tipo_proyecto')
          ->where('e.estado', '=', 1);
      })
      ->join('Meta_periodo AS f', function (JoinClause $join) {
        $join->on('f.id', '=', 'e.meta_periodo_id')
          ->on('f.periodo', '=', 'a.periodo')
          ->where('f.estado', '=', 1);
      })
      ->join('Meta_publicacion AS g', 'g.meta_tipo_proyecto_id', '=', 'e.id')
      ->leftJoin('Monitoreo_proyecto AS h', 'h.proyecto_id', '=', 'a.id')
      ->leftJoin('Proyecto_integrante AS i', function (JoinClause $join) {
        $join->on('i.proyecto_id', '=', 'a.id')
          ->where('i.condicion', '=', 'Responsable');
      })
      ->leftJoin('Usuario_investigador AS j', 'j.id', '=', 'i.investigador_id')
      ->leftJoin('Monitoreo_proyecto_publicacion AS k', 'k.monitoreo_proyecto_id', '=', 'h.id')
      ->leftJoin('Facultad AS l', 'l.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("CASE
          WHEN (d.tipo IS NULL OR d.tipo <= 0) THEN 'NO'
          WHEN d.tipo > 0 AND d.tipo <= 3 THEN 'SI'
          WHEN d.tipo > 3 THEN 'SUBSANADA'
        END AS deuda"),
        'd.categoria as deuda_categoria',
        'd.informe as deuda_detalle',
        'a.tipo_proyecto',
        'l.nombre AS facultad',
        DB::raw('CONCAT(j.apellido1, " " , j.apellido2, ", ", j.nombres) AS responsable'),
        'a.periodo',
        DB::raw("CASE(h.estado)
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'Por presentar'
          ELSE 'Por presentar' END AS estado_meta"),
        DB::raw("COUNT(DISTINCT k.id) AS publicaciones"),
      )
      ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador'])
      ->whereIn('a.estado', [1, 8, 9, 10, 11])
      ->wherenOTiN('a.tipo_proyecto', ['PSINFIPU', 'PTPBACHILLER', 'PTPGRADO', 'PTPDOCTO', 'PTPMAEST'])
      ->groupBy('a.id')
      ->get();

    return $proyectos;
  }

  public function detalles(Request $request) {
    $datos = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
        $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
          ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
      })
      ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'a.facultad_id')
      ->leftJoin('Monitoreo_proyecto AS f', 'f.proyecto_id', '=', 'a.id')
      ->select([
        'f.id',
        'a.titulo',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS responsable"),
        DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 3 THEN 'En evaluacion'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'Sustentado'
            WHEN 9 THEN 'En ejecución'
            WHEN 10 THEN 'Ejecutado'
            WHEN 11 THEN 'Concluído'
          ELSE 'Sin estado' END AS estado"),
        'a.periodo',
        'e.nombre AS facultad',
        DB::raw("CASE(f.estado)
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'Por presentar'
          ELSE 'Por presentar' END AS estado_meta"),
        'f.descripcion',
        'f.observacion'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $metas = DB::table('Meta_publicacion AS a')
      ->join('Meta_tipo_proyecto AS b', 'b.id', '=', 'a.meta_tipo_proyecto_id')
      ->join('Meta_periodo AS c', 'c.id', '=', 'b.meta_periodo_id')
      ->leftJoin('Publicacion AS d', 'd.tipo_publicacion', '=', 'a.tipo_publicacion')
      ->leftJoin('Publicacion_proyecto AS e', function ($join) use ($request) {
        $join->on('e.publicacion_id', '=', 'd.id')
          ->where('e.proyecto_id', '=', $request->query('id'));
      })
      ->select([
        'a.tipo_publicacion',
        'a.cantidad AS requerido',
        DB::raw('COUNT(e.id) AS completado')
      ])
      ->where('c.periodo', '=', $datos->periodo)
      ->where('b.tipo_proyecto', '=', $datos->tipo_proyecto)
      ->where('a.estado', '=', 1)
      ->groupBy('a.tipo_publicacion', 'a.cantidad')
      ->get();

    $publicaciones = DB::table('Publicacion_proyecto AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select([
        'a.id',
        'b.id AS publicacion_id',
        'b.titulo',
        'b.tipo_publicacion',
        DB::raw("YEAR(b.fecha_publicacion) AS periodo"),
        DB::raw("CASE(b.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 1 THEN 'Registrado'
          WHEN 2 THEN 'Observado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'No registrado'
          WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

        $anexos = DB::table('Proyecto_doc')
          ->select([
              'categoria',
              'comentario',
              DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
          ])
          ->where('proyecto_id', '=', $request->query('id'))
          ->where('categoria', '=', 'monitoreo')
          ->where('nombre', '=', 'Declaracion_jurada o carta')
          ->where('estado', '=', 1)
          ->first();
        
        $anexos = $anexos ? ['declaracion_jurada' => [
            'url' => $anexos->url,
            'fecha' => $anexos->comentario,
            ]
          ] : [];

        $evaluacion_metas = $this->evaluarMetas($request);

        return [
          'datos' => $datos,
          'metas' => $metas,
          'publicaciones' => $publicaciones,
          'evaluacion_metas' => $evaluacion_metas,
          'anexos' => $anexos
        ];
      }

  public function publicacionesDisponibles(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
        $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
          ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
      })
      ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->select([
        'a.periodo',
        'd.id AS investigador_id'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_proyecto AS c', 'c.publicacion_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw('YEAR(a.fecha_publicacion) AS periodo'),
        DB::raw('COUNT(c.id) AS proyectos_asociados')
      )
      ->where('a.estado', '=', 1)
      ->where('b.investigador_id', '=', $proyecto->investigador_id)
      ->where('a.tipo_publicacion', '=', $request->query('tipo_publicacion'))
      ->having('periodo', '>=', $proyecto->periodo)
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return $publicaciones;
  }

  public function agregarPublicacion(Request $request) {
    $count = DB::table('Publicacion_proyecto')
      ->where('publicacion_id', '=', $request->input('publicacion_id'))
      ->count();

    if ($count == 0) {
      $proyecto = DB::table('Proyecto AS a')
        ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
        ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
          $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
            ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
        })
        ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
        ->select([
          'a.titulo',
          'a.codigo_proyecto',
          'd.id AS investigador_id'
        ])
        ->where('a.id', '=', $request->input('proyecto_id'))
        ->first();

      DB::table('Publicacion_proyecto')
        ->insert([
          'investigador_id' => $proyecto->investigador_id,
          'publicacion_id' => $request->input('publicacion_id'),
          'proyecto_id' => $request->input('proyecto_id'),
          'tipo' => 'INTERNO',
          'codigo_proyecto' => $proyecto->codigo_proyecto,
          'nombre_proyecto' => $proyecto->titulo,
          'entidad_financiadora' => 'UNMSM',
          'estado' => 1,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      return ['message' => 'success', 'detail' => 'Publicación añadida'];
    } else {
      return ['message' => 'warning', 'detail' => 'Esta publicación ya está asociada a un proyecto'];
    }
  }

  public function eliminarPublicacion(Request $request) {
    $registro = DB::table('Monitoreo_proyecto_publicacion')
      ->select([
        'id',
        'monitoreo_proyecto_id',
        'publicacion_id',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    if (!$registro) {
      return [
        'message' => 'warning',
        'detail' => 'No se encontró la publicación del monitoreo.'
      ];
    }

    $monitoreo = DB::table('Monitoreo_proyecto')
      ->select([
        'id',
        'proyecto_id',
      ])
      ->where('id', '=', $registro->monitoreo_proyecto_id)
      ->first();

    if ($monitoreo) {
      DB::table('Publicacion_proyecto')
        ->where('proyecto_id', '=', $monitoreo->proyecto_id)
        ->where('publicacion_id', '=', $registro->publicacion_id)
        ->delete();
    }

    DB::table('Monitoreo_proyecto_publicacion')
      ->where('id', '=', $registro->id)
      ->delete();

    return [
      'message' => 'info',
      'detail' => 'Publicación eliminada correctamente'
    ];
  }

  //  Metas
  public function listadoMetas() {
    $periodos = DB::table('Meta_periodo')
      ->select(
        'id',
        'periodo',
        'descripcion',
        DB::raw("CASE
          WHEN estado = 1 THEN 'Válido'
          WHEN estado = 0 THEN 'Inválido'
        END AS estado")
      )
      ->get();

    $tipos = DB::table('Meta_tipo_proyecto')
      ->select(
        'id',
        'meta_periodo_id',
        'tipo_proyecto',
        'condicion',
        DB::raw("CASE
          WHEN estado = 1 THEN 'Válido'
          WHEN estado = 0 THEN 'Inválido'
        END AS estado")
      )
      ->get();

    return [
      'periodos' => $periodos,
      'tipos' => $tipos
    ];
  }

  public function agregarPeriodo(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_periodo')
      ->insert([
        'periodo' => $request->input('periodo'),
        'descripcion' => $request->input('descripcion'),
        'estado' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

    return ['message' => 'success', 'detail' => 'Periodo agregado correctamente'];
  }

  public function agregarProyecto(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_tipo_proyecto')
      ->insert([
        'meta_periodo_id' => $request->input('meta_periodo_id'),
        'tipo_proyecto' => $request->input('tipo_proyecto'),
        'estado' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

    return ['message' => 'success', 'detail' => 'Tipo de proyecto agregado correctamente'];
  }

  public function agregarMeta(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_publicacion')
      ->insert([
        'meta_tipo_proyecto_id' => $request->input('meta_tipo_proyecto_id'),
        'tipo_publicacion' => $request->input('tipo_publicacion'),
        'cantidad' => $request->input('cantidad'),
        'estado' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

    return ['message' => 'success', 'detail' => 'Meta agregada correctamente'];
  }

  public function editarMeta(Request $request) {
    $now = Carbon::now();

    DB::table('Meta_tipo_proyecto')
      ->where('id', '=', $request->input('meta_tipo_proyecto'))
      ->update([
        'condicion' => $request->input('condicion'),
        'updated_at' => $now,
      ]);

    return ['message' => 'info', 'detail' => 'Meta editada correctamente'];
  }

  public function eliminarMeta(Request $request) {
    DB::table('Meta_publicacion')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Meta eliminada correctamente'];
  }
  
  public function guardarAnexo(Request $request) {
    $date = Carbon::now();
    $proyectoId = $request->input('proyecto_id');

    if (!$request->hasFile('file1')) {
      return ['message' => 'error', 'detail' => 'Debe adjuntar un archivo PDF'];
    }

    $name = $proyectoId . "/" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file1')->getClientOriginalExtension();

    $this->uploadFile($request->file('file1'), "proyecto-doc", $name);

    DB::table('Proyecto_doc')
      ->where('proyecto_id', '=', $proyectoId)
      ->where('categoria', '=', 'monitoreo')
      ->where('nombre', '=', 'Declaracion_jurada o carta')
      ->update(['estado' => 0]);

    DB::table('Proyecto_doc')
      ->insert([
        'proyecto_id' => $proyectoId,
        'categoria' => 'monitoreo',
        'tipo' => 23,
        'nombre' => 'Declaracion_jurada o carta',
        'comentario' => $date->format('Y-m-d H:i:s'),
        'archivo' => $name,
        'estado' => 1
      ]);

    return ['message' => 'success', 'detail' => 'Documento guardado correctamente'];
  }

  public function excel(Request $request) {

    $data = $request->all();

    $export = new FromDataExport($data);

    return Excel::download($export, 'monitoreo.xlsx');
  }

  public function guardar(Request $request) {
    if ($request->input('estado') == 5) {
      $publicacionesRegistradas = DB::table('Publicacion_proyecto AS a')
        ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
        ->where('a.proyecto_id', '=', $request->input('proyecto_id'))
        ->where('a.estado', '=', 1)
        ->where('b.estado', '=', 1)
        ->count();

      if ($publicacionesRegistradas == 0) {
        return [
          'message' => 'warning',
          'detail' => 'Debe asociar al menos una publicación en estado Registrado para remitir el monitoreo.'
        ];
      }
    }

    if ($request->input('id')) {
      DB::table('Monitoreo_proyecto')
        ->where('id', '=', $request->input('id'))
        ->update([
          'observacion' => $request->input('observacion'),
          'descripcion' => $request->input('descripcion'),
          'estado' => $request->input('estado'),
          'updated_at' => Carbon::now()
        ]);

      return ['message' => 'info', 'detail' => 'Data actualizada'];
    } else {
      $count = DB::table('Monitoreo_proyecto')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->count();
      if ($count == 0) {
        DB::table('Monitoreo_proyecto')
          ->insert([
            'proyecto_id' => $request->input('proyecto_id'),
            'observacion' => $request->input('observacion'),
            'descripcion' => $request->input('descripcion'),
            'estado' => 5,
            'fecha_envio' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);

        return ['message' => 'info', 'detail' => 'Registro de monitoreo agregado'];
      } else {
        return ['message' => 'warning', 'detail' => 'Ya hay un registro de monitoreo para este proyecto, recargue la página'];
      }
    }
  }

  public function verObs(Request $request) {
    $observaciones = DB::table('Monitoreo_proyecto_obs')
      ->select([
        'observacion',
        'created_at',
        'updated_at'
      ])
      ->where('monitoreo_proyecto_id', '=', $request->query('id'))
      ->orderByDesc('created_at')
      ->get();

    return $observaciones;
  }

  public function observar(Request $request) {
    DB::table('Monitoreo_proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'estado' => 2,
        'updated_at' => Carbon::now()
      ]);

    DB::table('Monitoreo_proyecto_obs')
      ->insert([
        'monitoreo_proyecto_id' => $request->input('id'),
        'observacion' => $request->input('observacion'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Monitoreo observado'];
  }

  public function reporte(Request $request) {
    $datos = DB::table('Proyecto AS a')
      ->join('Proyecto_integrante AS b', 'b.proyecto_id', '=', 'a.id')
      ->join('Proyecto_integrante_tipo AS c', function (JoinClause $join) {
        $join->on('c.id', '=', 'b.proyecto_integrante_tipo_id')
          ->whereIn('c.nombre', ['Responsable', 'Asesor', 'Autor Corresponsal', 'Coordinador']);
      })
      ->join('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'a.facultad_id')
      ->leftJoin('Monitoreo_proyecto AS f', 'f.proyecto_id', '=', 'a.id')
      ->select([
        'a.titulo',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS responsable"),
        DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 3 THEN 'En evaluacion'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'Sustentado'
            WHEN 9 THEN 'En ejecución'
            WHEN 10 THEN 'Ejecutado'
            WHEN 11 THEN 'Concluído'
          ELSE 'Sin estado' END AS estado"),
        'a.periodo',
        'e.nombre AS facultad',
        DB::raw("CASE(f.estado)
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
          ELSE 'Por presentar' END AS estado_meta"),
        'f.descripcion',
        'f.updated_at',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $metas = DB::table('Meta_publicacion AS a')
      ->join('Meta_tipo_proyecto AS b', 'b.id', '=', 'a.meta_tipo_proyecto_id')
      ->join('Meta_periodo AS c', 'c.id', '=', 'b.meta_periodo_id')
      ->leftJoin('Publicacion AS d', 'd.tipo_publicacion', '=', 'a.tipo_publicacion')
      ->leftJoin('Publicacion_proyecto AS e', function ($join) use ($request) {
        $join->on('e.publicacion_id', '=', 'd.id')
          ->where('e.proyecto_id', '=', $request->query('id'));
      })
      ->select([
        'a.tipo_publicacion',
        'a.cantidad AS requerido',
        DB::raw('COUNT(e.id) AS completado')
      ])
      ->where('c.periodo', '=', $datos->periodo)
      ->where('b.tipo_proyecto', '=', $datos->tipo_proyecto)
      ->where('a.estado', '=', 1)
      ->groupBy('a.tipo_publicacion', 'a.cantidad')
      ->get();

    $publicaciones = DB::table('Publicacion_proyecto AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select([
        'b.id',
        'b.titulo',
        'b.tipo_publicacion',
        DB::raw("YEAR(b.fecha_publicacion) AS periodo"),
        DB::raw("CASE(b.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.informes.monitoreo.reporte', [
      'datos' => $datos,
      'metas' => $metas,
      'publicaciones' => $publicaciones
    ]);

    return $pdf->stream();
  }

    /**
   * Helpers para árbol en metas
   */
  function isWrappedByParens(string $str): bool {
    $depth = 0;
    $len = strlen($str);

    for ($i = 0; $i < $len; $i++) {
      if ($str[$i] === '(') $depth++;
      if ($str[$i] === ')') $depth--;
      if ($depth === 0 && $i < $len - 1) return false;
    }

    return $str[0] === '(' && $str[$len - 1] === ')';
  }

  function stripOuterParens(string $str): string {
    return trim(substr($str, 1, -1));
  }

  function findRootOperator(string $str): ?array {
    $depth = 0;
    $len = strlen($str);

    for ($i = 0; $i < $len; $i++) {
      if ($str[$i] === '(') $depth++;
      if ($str[$i] === ')') $depth--;

      if ($depth === 0) {
        if (substr($str, $i, 5) === ' AND ') {
          return ['operator' => 'AND', 'index' => $i];
        }
        if (substr($str, $i, 4) === ' OR ') {
          return ['operator' => 'OR', 'index' => $i];
        }
      }
    }

    return null;
  }

  function queryToTree(string $query): ?array {
    $query = trim($query);
    if ($query === '') return null;

    $wrapped = $this->isWrappedByParens($query);
    $expr = $wrapped ? $this->stripOuterParens($query) : $query;

    $rootOp = $this->findRootOperator($expr);

    // CONDICIÓN
    if (!$rootOp) {
      if (!preg_match('/^(.+?)\s*=\s*(\d+)$/', $expr, $m)) {
        return null;
      }

      $condition = [
        'type'   => 'condition',
        'key'    => trim($m[1]),
        'number' => (int) $m[2],
      ];

      // Siempre envolver en group si venía entre paréntesis
      if ($wrapped) {
        return [
          'type'     => 'group',
          'operator' => 'AND',
          'children' => [$condition],
        ];
      }

      return $condition;
    }

    // GRUPO
    $operator = $rootOp['operator'];
    $index = $rootOp['index'];

    $left  = trim(substr($expr, 0, $index));
    $right = trim(substr($expr, $index + strlen($operator) + 2));

    return [
      'type'     => 'group',
      'operator' => $operator,
      'children' => [
        $this->queryToTree($left),
        $this->queryToTree($right),
      ],
    ];
  }

  function extractKeys(array $node): array {
    if ($node['type'] === 'condition') {
      return [$node['key']];
    }

    $keys = [];
    foreach ($node['children'] as $child) {
      $keys = array_merge($keys, $this->extractKeys($child));
    }

    return array_unique($keys);
  }

  function evaluarMetas(Request $request) {
    $tipoToKey = [
      'Artículo'        => 'Artículo',
      'Capítulo'        => 'Capítulo',
      'Libro'           => 'Libro',
      'Tesis propia'    => 'Tesis propia',
      'Tesis asesoria'  => 'Tesis asesoria',
      'Evento'          => 'Evento',
      'Ensayo'          => 'Ensayo',
    ];

    $info1 = DB::table('Proyecto AS a')
      ->join('Meta_periodo AS b', 'b.periodo', '=', 'a.periodo')
      ->join('Meta_tipo_proyecto AS c', function (JoinClause $join) {
        $join->on('c.meta_periodo_id', '=', 'b.id')
          ->on('c.tipo_proyecto', '=', 'a.tipo_proyecto');
      })
      ->select([
        'c.condicion',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $tree = $this->queryToTree($info1->condicion ?? "");

    $columns = $this->extractKeys($tree);

    $values = [];

    foreach ($columns as $key) {
      $values[$key] = 0;
    }

    $publicaciones = DB::table('Publicacion_proyecto AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->select([
        DB::raw("CASE (b.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo'
            WHEN 'capitulo' THEN 'Capítulo'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'Evento'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE tipo_publicacion END AS tipo"),
        DB::raw("COUNT(b.id) AS cuenta"),
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->where('b.estado', '=', 1)
      ->groupBy('b.tipo_publicacion')
      ->get();

    foreach ($publicaciones as $row) {
      if (!isset($tipoToKey[$row->tipo])) {
        continue;
      }

      $key = $tipoToKey[$row->tipo];

      // solo si esa key está en el árbol
      if (array_key_exists($key, $values)) {
        $values[$key] = (int) $row->cuenta;
      }
    }

    $resultado = $this->evaluateTree($tree, $values);

    $comparacion = $this->buildComparisonArray($tree, $publicaciones);

    return [
      'resultado' => $resultado,
      'comparacion' => $comparacion,
      'condicion' => $info1->condicion,
      'publicaciones' => $publicaciones
    ];
  }

  private function evaluateTree(array $node, array $values): bool {
    // CONDICIÓN
    if ($node['type'] === 'condition') {
      $actual = $values[$node['key']] ?? 0;
      return $actual >= $node['number'];
    }

    // GRUPO
    if ($node['type'] === 'group') {
      if ($node['operator'] === 'AND') {
        foreach ($node['children'] as $child) {
          if (!$this->evaluateTree($child, $values)) {
            return false;
          }
        }
        return true;
      }

      if ($node['operator'] === 'OR') {
        foreach ($node['children'] as $child) {
          if ($this->evaluateTree($child, $values)) {
            return true;
          }
        }
        return false;
      }
    }

    return false;
  }

  function extractRequirements(array $node): array {
    $requirements = [];

    // Si es condición válida
    if (
      isset($node['type']) &&
      $node['type'] === 'condition' &&
      isset($node['key'], $node['number'])
    ) {
      $requirements[$node['key']] = [
        'value' => (int) $node['number'],
      ];

      return $requirements;
    }

    // Si tiene hijos, recorrerlos
    if (isset($node['children']) && is_array($node['children'])) {
      foreach ($node['children'] as $child) {
        $childReq = $this->extractRequirements($child);
        $requirements = array_merge($requirements, $childReq);
      }
    }

    return $requirements;
  }

  function buildComparisonArray(array $tree, $publicaciones): array {
    $requirements = $this->extractRequirements($tree);

    $counts = [];

    foreach ($publicaciones as $p) {
      $counts[$p->tipo] = (int) $p->cuenta;
    }

    $result = [];

    foreach ($requirements as $tipo => $data) {
      $valorActual   = $counts[$tipo] ?? 0;
      $valorEsperado = (int) $data['value'];

      $result[] = [
        $tipo,
        $valorActual,
        $valorEsperado
      ];
    }

    return $result;
  }
}

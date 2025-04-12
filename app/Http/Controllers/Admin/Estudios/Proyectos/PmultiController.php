<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\S3Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PmultiController extends S3Controller {

  public function detalle(Request $request) {
    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Facultad_programa AS d', 'd.id', '=', 'a.programa_id')
      ->leftJoin('Geco_proyecto AS e', 'e.proyecto_id', '=', 'a.id')
      ->leftJoin('Proyecto_descripcion AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'a.id')
          ->where('f.codigo', '=', 'autorizacion_grupo');
      })
      ->select(
        'a.tipo_proyecto',
        'a.estado',
        'a.titulo',
        'a.codigo_proyecto',
        'a.resolucion_rectoral',
        DB::raw("IFNULL(a.resolucion_fecha, '') AS resolucion_fecha"),
        'c.nombre AS linea',
        'a.localizacion',
        'd.programa',
        'a.comentario',
        'a.observaciones_admin',
        'e.id AS geco_proyecto_id',
        'f.detalle AS autorizacion_grupo'
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    //  Ver si hay registro
    $coordinadores = json_decode($detalle->autorizacion_grupo ?? "[]");
    $ids = array_column($coordinadores, 'investigador_id');

    $grupos = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select([
        'a.investigador_id',
        DB::raw("UPPER(b.grupo_nombre_corto) AS grupo_nombre_corto"),
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('a.investigador_id', $ids)
      ->get()
      ->map(function ($item) use ($coordinadores) {
        foreach ($coordinadores as $coordinador) {
          if ($coordinador->investigador_id == $item->investigador_id) {
            return [
              'grupo_nombre_corto' => $item->grupo_nombre_corto,
              'autorizado' => $coordinador->autorizado == 1 ? 'SÍ' : 'NO'
            ];
          }
        }
      });

    $detalle->grupos = $grupos;

    return $detalle;
  }

  public function miembros(Request $request) {
    $carta = DB::table('Proyecto_doc')
      ->select([
        'archivo AS url',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('categoria', '=', 'carta')
      ->where('nombre', '=', 'Carta de compromiso de confidencialidad')
      ->where('estado', '=', 1)
      ->first();

    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoin('File AS e', function (JoinClause $join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.bucket', '=', 'carta-compromiso')
          ->where('e.recurso', '=', 'CARTA_COMPROMISO')
          ->where('e.estado', '=', 20);
      })
      ->leftJoin('Grupo AS f', 'f.id', '=', 'a.grupo_id')
      ->select([
        'a.id',
        'c.nombre AS tipo_integrante',
        'b.codigo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'd.nombre AS facultad',
        DB::raw("UPPER(f.grupo_nombre_corto) AS grupo_nombre_corto"),
        DB::raw("COALESCE(CONCAT('/minio/carta-compromiso/', e.key), '/minio/proyecto-doc/" . $carta?->url . "') AS url"),
        'a.tipo_tesis',
        'a.titulo_tesis'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('c.id')
      ->get();

    return $miembros;
  }

  public function responsable(Request $request) {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Responsable');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Dependencia AS d', 'd.id', '=', 'c.dependencia_id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'c.facultad_id')
      ->leftJoin('Grupo_integrante AS f', function (JoinClause $join) {
        $join->on('f.investigador_id', '=', 'c.id')
          ->whereNot('f.condicion', 'LIKE', 'Ex%');
      })
      ->leftJoin('Grupo AS g', 'g.id', '=', 'f.grupo_id')
      ->leftJoin('Area AS h', 'h.id', '=', 'e.area_id')
      ->select([
        'c.nombres',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2) AS apellidos"),
        'c.doc_numero',
        'c.telefono_movil',
        'c.telefono_trabajo',
        'c.especialidad',
        'c.titulo_profesional',
        'c.grado',
        'c.tipo',
        DB::raw("CONCAT((CASE
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(c.docente_categoria, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END), ' | ', (CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(c.docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END)) AS docente_categoria"),
        'c.codigo',
        'd.dependencia',
        'e.nombre AS facultad',
        'c.email3',
        'g.grupo_nombre',
        'h.nombre AS area'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    return $responsable;
  }

  public function documentos(Request $request) {
    $docs = DB::table('Proyecto_doc')
      ->select([
        'id',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 25)
      ->where('categoria', '=', 'documento')
      ->where('nombre', '=', 'Documento de colaboración externa')
      ->get();

    return $docs;
  }

  public function descripcion(Request $request) {
    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'estado_arte',
        'planteamiento_problema',
        'justificacion',
        'contribucion_impacto',
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
      ->where('id', '=', $request->query('proyecto_id'))
      ->first();

    $archivo1 = DB::table('Proyecto_doc')
      ->select([
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 27)
      ->where('categoria', '=', 'anexo')
      ->where('nombre', '=', 'Estado del arte')
      ->where('estado', '=', 1)
      ->first();

    $archivo2 = DB::table('Proyecto_doc')
      ->select([
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 26)
      ->where('categoria', '=', 'anexo')
      ->where('nombre', '=', 'Metodología de trabajo')
      ->where('estado', '=', 1)
      ->first();

    return [
      'descripcion' => $descripcion,
      'palabras_clave' => $palabras_clave->palabras_clave ?? "",
      'archivos' => [
        'estado_arte' => $archivo1?->url,
        'metodologia' => $archivo2?->url,
      ]
    ];
  }

  public function actividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.proyecto_integrante_id',
        'a.actividad',
        'a.justificacion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
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
      ->leftJoin('Proyecto_descripcion AS j', function (JoinClause $join) {
        $join->on('j.proyecto_id', '=', 'a.id')
          ->where('j.codigo', '=', 'area_tematica');
      })
      ->leftJoin('Proyecto_descripcion AS k', function (JoinClause $join) {
        $join->on('k.proyecto_id', '=', 'a.id')
          ->where('k.codigo', '=', 'objetivo_ods');
      })
      ->leftJoin('Ods AS l', 'l.id', '=', 'k.detalle')
      ->select([
        'c.grupo_nombre',
        'd.nombre AS facultad',
        'e.nombre AS area',
        'j.detalle AS area_tematica',
        'g.linea AS ocde',
        'a.palabras_clave',
        'a.titulo',
        'f.nombre AS linea',
        'l.descripcion AS ods',
        'a.localizacion',
      ])
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $docs = DB::table('Proyecto_doc')
      ->select([
        'comentario',
        'archivo',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 25)
      ->where('nombre', '=', 'Documento de colaboración externa')
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoin('File AS e', function (JoinClause $join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.bucket', '=', 'carta-compromiso')
          ->where('e.recurso', '=', 'CARTA_COMPROMISO')
          ->where('e.estado', '=', 20);
      })
      ->leftJoin('Grupo_integrante AS f', function (JoinClause $join) {
        $join->on('f.investigador_id', '=', 'b.id')
          ->whereNot('f.condicion', 'LIKE', 'Ex %');
      })
      ->leftJoin('Grupo AS g', 'g.id', '=', 'f.grupo_id')
      ->select([
        'a.id',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.tipo',
        'c.nombre AS tipo_integrante',
        'd.nombre AS facultad',
        'g.grupo_nombre',
        DB::raw("CASE
          WHEN e.key IS NOT NULL THEN 'Sí'
          ELSE 'No' END AS compromiso")
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->groupBy('b.id')
      ->get();

    $actividades = DB::table('Proyecto_actividad AS a')
      ->join('Proyecto_integrante AS b', 'b.id', '=', 'a.proyecto_integrante_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.id',
        'a.actividad',
        'a.justificacion',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS responsable"),
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.partida',
        'a.justificacion',
        'b.tipo',
        'a.monto',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.sin_detalles.pmulti', [
      'proyecto' => $proyecto,
      'docs' => $docs,
      'integrantes' => $integrantes,
      'detalles' => $detalles,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);
    return $pdf->stream();
  }
}

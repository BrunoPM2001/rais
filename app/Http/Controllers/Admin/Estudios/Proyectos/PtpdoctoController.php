<?php

namespace App\Http\Controllers\Admin\Estudios\Proyectos;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtpdoctoController extends Controller {
  public function detalle(Request $request) {
    $detalle = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Facultad_programa AS d', 'd.id', '=', 'a.programa_id')
      ->leftJoin('Geco_proyecto AS e', 'e.proyecto_id', '=', 'a.id')
      ->leftJoin('Grupo AS f', 'f.id', '=', 'a.grupo_id')
      ->select(
        'a.tipo_proyecto',
        'a.estado',
        'a.titulo',
        'a.codigo_proyecto',
        'a.resolucion_rectoral',
        DB::raw("IFNULL(a.resolucion_fecha, '') AS resolucion_fecha"),
        'b.nombre AS facultad',
        'c.nombre AS linea',
        'a.localizacion',
        'f.grupo_nombre',
        'd.programa',
        'a.comentario',
        'a.observaciones_admin',
        'a.dj_aceptada',
        DB::raw("CONCAT('/minio/declaracion-jurada/dj_PTPDOCTO_', a.id, '.pdf') AS url2"),
        'e.id AS geco_proyecto_id'
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    return $detalle;
  }

  public function documentos(Request $request) {
    $documentos = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $documentos;
  }

  public function miembros(Request $request) {
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
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $integrantes;
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
        'planteamiento_problema',
        'hipotesis',
        'justificacion',
        'antecedentes',
        'objetivos_generales',
        'objetivos_especificos',
        'metodologia_trabajo',
        'referencias_bibliograficas',
        'presupuesto_justificacion',
        'presupuesto_otros_fondo_fuente',
        'presupuesto_otros_fondo_monto',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    return $descripcion;
  }

  public function actividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad AS a')
      ->select([
        'a.id',
        'a.actividad',
        'a.fecha_inicio',
        'a.fecha_fin',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return $actividades;
  }

  public function responsableTesista(Request $request) {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Asesor');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'c.doc_numero',
        'c.tipo',
        'c.codigo',
        'c.codigo_orcid',
        'd.nombre AS facultad',
        DB::raw("CASE(c.regina)
          WHEN 1 THEN 'Registrado'
          ELSE 'No registrado'
        END AS regina"),
        DB::raw("CASE(c.dina)
          WHEN 1 THEN 'Registrado'
          ELSE 'No registrado'
        END AS dina"),
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    $tesista = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Tesista');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'c.doc_numero',
        'c.tipo',
        'c.codigo',
        'd.nombre AS facultad',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    return ['responsable' => $responsable, 'tesista' => $tesista];
  }

  public function reporte(Request $request) {
    $proyecto = DB::table('Proyecto AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->join('Ocde AS d', 'd.id', '=', 'a.ocde_id')
      ->leftJoin('Proyecto_doc AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.id')
          ->where('e.nombre', '=', 'Registro de Evaluación de la Tesis en la UI')
          ->where('e.estado', '=', 1);
      })
      ->leftJoin('Proyecto_doc AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'a.id')
          ->where('f.nombre', '=', 'Anexos de tesis')
          ->where('f.estado', '=', 1);
      })
      ->select(
        'a.periodo',
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
        'a.updated_at',
        'a.titulo',
        'c.nombre AS linea',
        'd.linea AS ocde',
        'a.localizacion',
        'e.archivo',
        'f.archivo AS anexo',
        'a.observaciones_admin',
      )
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->first();

    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Asesor');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Grupo_integrante AS e', function (JoinClause $join) {
        $join->on('e.investigador_id', '=', 'c.id')
          ->whereNot('e.condicion', 'LIKE', 'Ex%');
      })
      ->leftJoin('Grupo AS f', 'f.id', '=', 'e.grupo_id')
      ->leftJoin('Proyecto_doc AS g', function (JoinClause $join) {
        $join->on('g.proyecto_id', '=', 'a.proyecto_id')
          ->where('g.nombre', '=', 'Carta de Compromiso del Asesor')
          ->where('g.estado', '=', 1);
      })
      ->select([
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'c.doc_numero',
        'c.fecha_nac',
        'c.codigo',
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
        'c.codigo_orcid',
        DB::raw("CASE(c.regina)
          WHEN 1 THEN 'Registrado'
          ELSE 'No registrado'
        END AS regina"),
        DB::raw("CASE(c.dina)
          WHEN 1 THEN 'Registrado'
          ELSE 'No registrado'
        END AS dina"),
        'c.google_scholar',
        'f.grupo_nombre',
        'g.archivo'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    $tesista = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.proyecto_integrante_tipo_id')
          ->where('b.nombre', '=', 'Tesista');
      })
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Proyecto_doc AS e', function (JoinClause $join) {
        $join->on('e.proyecto_id', '=', 'a.proyecto_id')
          ->where('e.nombre', '=', 'Carta de Compromiso del Postulante')
          ->where('e.estado', '=', 1);
      })
      ->select([
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombres"),
        'c.doc_numero',
        'c.tipo',
        'c.codigo',
        'd.nombre AS facultad',
        'e.archivo'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', [
        'resumen_ejecutivo',
        'planteamiento_problema',
        'hipotesis',
        'justificacion',
        'antecedentes',
        'objetivos_generales',
        'objetivos_especificos',
        'metodologia_trabajo',
        'referencias_bibliograficas',
        'presupuesto_justificacion',
        'presupuesto_otros_fondo_fuente',
        'presupuesto_otros_fondo_monto',
      ])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'fecha_inicio',
        'fecha_fin',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select(
        'b.codigo',
        'b.partida',
        'b.tipo',
        'a.monto',
      )
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('a.tipo')
      ->get();

    $pdf = Pdf::loadView('admin.estudios.proyectos.ptpdocto', [
      'proyecto' => $proyecto,
      'responsable' => $responsable,
      'tesista' => $tesista,
      'descripcion' => $descripcion,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
    ]);

    return $pdf->stream();
  }
}

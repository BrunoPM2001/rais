<?php

namespace App\Http\Controllers\Evaluador\Evaluaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluadorProyectosController extends S3Controller {
  public function listado(Request $request) {
    $date = Carbon::now();

    $proyectos = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Evaluacion_opcion AS e', function (JoinClause $join) {
        $join->on('e.tipo', '=', 'c.tipo_proyecto')
          ->on('e.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'c.id')
          ->whereNotNull('f.evaluacion_opcion_id');
      })
      ->join('Convocatoria AS g', function (JoinClause $join) use ($date) {
        $join->on('g.tipo', '=', 'c.tipo_proyecto')
          ->on('g.periodo', '=', 'c.periodo')
          ->where('g.evento', '=', 'evaluacion')
          ->where('g.fecha_inicial', '<', $date)
          ->where('g.fecha_final', '>', $date);
      })
      ->select([
        'c.id',
        'c.tipo_proyecto',
        'c.titulo',
        'd.nombre AS facultad',
        'c.periodo',
        DB::raw("COUNT(DISTINCT e.id) AS criterios"),
        DB::raw("COUNT(DISTINCT f.id) AS criterios_evaluados"),
        DB::raw("CASE
          WHEN f.cerrado = 1 THEN 'Sí'
          ELSE 'No'
        END as evaluado"),
        DB::raw("CASE
          WHEN a.ficha is not null THEN 'Sí'
          ELSE 'No'
        END as ficha"),
      ])
      ->where('a.evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->where('e.nivel', '=', 1)
      ->groupBy('c.id')
      ->get();

    return $proyectos;
  }

  public function criteriosEvaluacion(Request $request) {
    //  Calculo de criterios
    $this->criteriosAutomaticos($request);
    $total = 0;
    $criterios = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Evaluacion_opcion AS d', function ($join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function ($join) {
        $join->on('e.proyecto_id', '=', 'c.id')
          ->on('e.evaluador_id', '=', 'b.id')
          ->on('e.evaluacion_opcion_id', '=', 'd.id');
      })
      ->select([
        'd.id',
        'd.opcion',
        'd.puntaje_max',
        'd.nivel',
        'd.editable',
        DB::raw("COALESCE(e.puntaje, 0.00) AS puntaje"),
        'e.comentario',
        'e.id AS id_edit'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->where('a.evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->orderBy('d.orden')
      ->get();

    foreach ($criterios as $item) {
      if ($item->nivel == 1) {
        $total = $total + $item->puntaje;
      }
    }

    $estado = DB::table('Evaluacion_proyecto')
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->where('cerrado', '=', 1)
      ->count();

    $comentario = DB::table('Proyecto_evaluacion')
      ->select([
        'comentario',
        'ficha'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->first();

    return ['criterios' => $criterios, 'comentario' => $comentario, 'cerrado' => $estado > 0 ? true : false, 'total' => $total];
  }

  public function updateItem(Request $request) {
    if ($request->input('id_edit') == null) {
      DB::table('Evaluacion_proyecto')
        ->insert([
          'evaluacion_opcion_id' => $request->input('id'),
          'proyecto_id' => $request->input('proyecto_id'),
          'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
          'puntaje' => $request->input('puntaje'),
          'comentario' => $request->input('comentario'),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
    } else {
      DB::table('Evaluacion_proyecto')
        ->where('id', '=', $request->input('id_edit'))
        ->update([
          'puntaje' => $request->input('puntaje'),
          'comentario' => $request->input('comentario'),
          'updated_at' => Carbon::now()
        ]);
    }

    $criterios = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Evaluacion_opcion AS d', function ($join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function ($join) {
        $join->on('e.proyecto_id', '=', 'c.id')
          ->on('e.evaluador_id', '=', 'b.id')
          ->on('e.evaluacion_opcion_id', '=', 'd.id');
      })
      ->select([
        'd.id',
        'd.opcion',
        'd.puntaje_max',
        'd.nivel',
        'd.editable',
        DB::raw("COALESCE(e.puntaje, 0.00) AS puntaje"),
        'e.comentario',
        'e.id AS id_edit'
      ])
      ->where('a.proyecto_id', '=', $request->input('proyecto_id'))
      ->where('a.evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->orderBy('d.orden')
      ->get();

    $suma = 0;
    foreach ($criterios as $item) {
      if ($item->nivel == 2 && $item->opcion == "SUB TOTAL") {
        if ($item->id_edit == null) {
          DB::table('Evaluacion_proyecto')
            ->insert([
              'evaluacion_opcion_id' => $item->id,
              'proyecto_id' => $request->input('proyecto_id'),
              'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
              'puntaje' => $suma,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
        } else {
          DB::table('Evaluacion_proyecto')
            ->where('id', '=', $item->id_edit)
            ->update([
              'puntaje' => $suma,
              'updated_at' => Carbon::now()
            ]);
        }
        $suma = 0;
      }
      if ($item->nivel == 1) {
        $suma = $suma + $item->puntaje;
      }
    }

    return ['message' => 'success', 'detail' => 'Datos actualizados con éxito'];
  }

  public function preFinalizarEvaluacion(Request $request) {
    //  Verificación de puntaje 0 y comentarios
    $verificar1 = true;
    $verificar2 = true;

    $criterios = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Evaluacion_opcion AS d', function ($join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function ($join) {
        $join->on('e.proyecto_id', '=', 'c.id')
          ->on('e.evaluador_id', '=', 'b.id')
          ->on('e.evaluacion_opcion_id', '=', 'd.id');
      })
      ->select([
        'd.id',
        'd.opcion',
        'd.puntaje_max',
        'd.nivel',
        'd.editable',
        DB::raw("COALESCE(e.puntaje, 0.00) AS puntaje"),
        'e.comentario',
        'e.id AS id_edit'
      ])
      ->where('a.proyecto_id', '=', $request->input('proyecto_id'))
      ->where('d.nivel', '=', 1)
      ->where('d.editable', '!=', 0)
      ->where('a.evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->orderBy('d.orden')
      ->get();

    foreach ($criterios as $item) {
      if ($item->puntaje == 0) {
        $verificar1 = false;
      }
      if ($item->comentario == "" || $item->comentario == null) {
        $verificar2 = false;
      }
    }

    return [
      'puntajesValidos' => $verificar1,
      'comentariosValidos' => $verificar2,
    ];
  }

  public function finalizarEvaluacion(Request $request) {
    DB::table('Proyecto_evaluacion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->update([
        'comentario' => $request->input('comentario')
      ]);

    DB::table('Evaluacion_proyecto')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->update([
        'cerrado' => 1
      ]);

    return ['message' => 'success', 'detail' => 'Evaluación finalizada con éxito'];
  }

  public function fichaEvaluacion(Request $request) {
    $total = 0;

    $criterios = DB::table('Proyecto_evaluacion AS a')
      ->leftJoin('Usuario_evaluador AS b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Evaluacion_opcion AS d', function ($join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function ($join) {
        $join->on('e.proyecto_id', '=', 'c.id')
          ->on('e.evaluador_id', '=', 'b.id')
          ->on('e.evaluacion_opcion_id', '=', 'd.id');
      })
      ->select([
        'd.opcion',
        'd.puntaje_max',
        'd.nivel',
        'd.editable',
        DB::raw("COALESCE(e.puntaje, 0.00) AS puntaje"),
        'e.comentario',
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->where('a.evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->orderBy('d.orden')
      ->get();

    foreach ($criterios as $item) {
      if ($item->nivel == 1) {
        $total = $total + $item->puntaje;
      }
    }

    $extra = DB::table('Proyecto_evaluacion AS a')
      ->join('Usuario_evaluador AS b', 'a.evaluador_id', '=', 'b.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->select([
        'a.comentario',
        'c.titulo',
        'c.tipo_proyecto',
        DB::raw("CONCAT(b.apellidos, ' ', b.nombres) AS evaluador")
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->where('a.evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
      ->first();

    $pdf = Pdf::loadView('evaluador.ficha', ['evaluacion' => $criterios, 'extra' => $extra, 'total' => $total]);
    return $pdf->stream();
  }

  public function cargarFicha(Request $request) {
    if ($request->hasFile('file')) {
      $p_id = $request->input('proyecto_id');
      $e_id = $request->attributes->get('token_decoded')->evaluador_id;
      $date = Carbon::now();

      $nameFile = $p_id . "/" . $p_id . "-" . $e_id . "-" . $date->format('Ymd-His') . "." . $request->file('file')->getClientOriginalExtension();

      DB::table('Proyecto_evaluacion')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('evaluador_id', '=', $request->attributes->get('token_decoded')->evaluador_id)
        ->update([
          'ficha' => $nameFile
        ]);

      $this->uploadFile($request->file('file'), "proyecto-evaluacion", $nameFile);

      return ['message' => 'success', 'detail' => 'Ficha cargada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar ficha'];
    }
  }

  public function criteriosAutomaticos(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select(['tipo_proyecto'])
      ->where('id', '=', $request->query('proyecto_id'))
      ->first();

    $utils = new CriteriosUtilsController();

    switch ($proyecto->tipo_proyecto) {
      case "PMULTI":
        $utils->fetchPuntajeFormacionRrhh($request);
        $utils->AddExperienciaResponsable($request);
        $utils->addgiTotal($request);
        $utils->totalpuntajeIntegrantes($request);
        break;
    }
  }

  public function visualizarProyecto(Request $request) {

    $proyecto = DB::table('Proyecto AS p')
      ->leftJoin('Facultad AS f', 'f.id', '=', 'p.facultad_id')
      ->leftJoin('Ocde AS o', 'o.id', '=', 'p.ocde_id')
      ->leftJoin('Linea_investigacion AS li', 'li.id', '=', 'p.linea_investigacion_id')
      ->leftJoin('Grupo AS g', 'g.id', '=', 'p.grupo_id') // Se añadió el prefijo 'AS'
      ->leftJoin('Area AS a', 'a.id', '=', 'f.area_id')
      ->select([
        'p.id',
        'p.tipo_proyecto',
        'p.titulo',
        'f.nombre AS facultad',
        'o.linea AS ocde',
        'li.nombre AS linea_investigacion',
        'g.grupo_nombre AS grupo',
        'p.localizacion',
        'a.nombre AS area'
      ])
      ->where('p.id', '=', $request->query('proyecto_id'))
      ->first();

    $responsable = DB::table('Proyecto AS p')
      ->select([
        'i.id',
        DB::raw('concat(i.apellido1, " ", i.apellido2, " ", i.nombres) AS responsable'),
        'i.codigo_orcid',
        'i.scopus_id',
        'i.google_scholar',
        'pub.editorial',
        'pub.url',
        'pub.tipo_publicacion'
      ])
      ->leftJoin('Proyecto_integrante AS pint', 'pint.proyecto_id', '=', 'p.id')
      ->leftJoin('Usuario_investigador AS i', 'i.id', '=', 'pint.investigador_id')
      ->leftJoin('Publicacion_proyecto AS pp', 'pp.investigador_id', '=', 'i.id')
      ->leftJoin('Publicacion AS pub', 'pub.id', '=', 'pp.publicacion_id')
      ->where('pint.condicion', '=', 'Responsable')
      ->where('p.id', '=', $request->query('proyecto_id'))
      ->distinct() // Agrega esto si hay duplicados
      ->first();



    $descripciones = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->pluck('detalle', 'codigo')
      ->toArray();

    $calendario = DB::table('Proyecto_actividad')
      ->select([
        'actividad',
        'justificacion',
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
        'a.justificacion',
        'a.monto',
        'a.tipo',
      )
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('a.tipo')
      ->get();

    $integrantes = DB::table('Proyecto_integrante AS pint')
      ->select(
        'i.id',
        'ptipo.nombre AS tipo_integrante',
        DB::raw('concat(i.apellido1, " ", i.apellido2, " ", i.nombres) AS integrante'),
        'i.tipo AS tipo',
        'f.nombre AS facultad',
        'gi.condicion AS tipo_integrante_grupo',
        'gi.tesista AS grado_academico',
        'gi.titulo_proyecto_tesis AS titulo_tesis'
      )
      ->join('Proyecto_integrante_tipo AS ptipo', 'ptipo.id', '=', 'pint.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS i', 'i.id', '=', 'pint.investigador_id')
      ->join('Facultad AS f', 'f.id', '=', 'i.facultad_id')
      ->join('Grupo_integrante AS gi', 'gi.investigador_id', '=', 'pint.investigador_id')
      ->where('pint.proyecto_id', '=', $request->query('proyecto_id'))
      ->where(function ($query) {
        $query->where('gi.condicion', 'NOT LIKE', 'Ex%')
          ->orWhere('gi.condicion', '=', 'Externo');
      })
      ->orderBy('ptipo.id', 'asc') // Aquí se agrega el ordenamiento
      ->get();





    return [
      'proyecto' => $proyecto,
      'detalles' => $descripciones,
      'calendario' => $calendario,
      'presupuesto' => $presupuesto,
      'integrantes' => $integrantes,
      'responsable' => $responsable
    ];
  }
}

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

    $proyectos = DB::table('Proyecto_evaluacion as a')
      ->leftJoin('Usuario_evaluador as b', 'b.id', '=', 'a.evaluador_id')
      ->leftJoin('Proyecto as c', 'c.id', '=', 'a.proyecto_id')
      ->leftJoin('Facultad as d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('Evaluacion_opcion as e', function (JoinClause $join) {
        $join->on('e.tipo', '=', 'c.tipo_proyecto')
          ->on('e.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto as f', function (JoinClause $join) {
        $join->on('f.proyecto_id', '=', 'c.id')
          ->whereNotNull('f.evaluacion_opcion_id')
          ->whereColumn('f.evaluador_id', '=', 'a.evaluador_id');
      })
      ->join('Convocatoria as g', function (JoinClause $join) use ($date) {
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
        'd.nombre as facultad',
        'c.periodo',
        DB::raw("COUNT(DISTINCT e.id) as criterios"),
        DB::raw("COUNT(DISTINCT CASE WHEN f.evaluador_id = a.evaluador_id THEN f.id END) as criterios_evaluados"),
        DB::raw("CASE WHEN MAX(f.cerrado) = 1 THEN 'Sí' ELSE 'No' END as evaluado"),
        DB::raw("CASE WHEN a.ficha IS NOT NULL THEN 'Sí' ELSE 'No' END as ficha")
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
      ->leftJoin('Evaluacion_opcion AS d', function (JoinClause $join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function (JoinClause $join) {
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
      ->leftJoin('Evaluacion_opcion AS d', function (JoinClause $join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function (JoinClause $join) {
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
      ->leftJoin('Evaluacion_opcion AS d', function (JoinClause $join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function (JoinClause $join) {
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
      ->leftJoin('Evaluacion_opcion AS d', function (JoinClause $join) {
        $join->on('d.tipo', '=', 'c.tipo_proyecto')
          ->on('d.periodo', '=', 'c.periodo');
      })
      ->leftJoin('Evaluacion_proyecto AS e', function (JoinClause $join) {
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
      ->join('Proyecto_integrante AS d', 'd.proyecto_id', '=', 'a.proyecto_id')
      ->join('Usuario_investigador AS e', 'e.id', '=', 'd.investigador_id')
      ->select(
        'a.comentario',
        'a.id',
        'a.proyecto_id',
        'b.id as evaluador_id',
        'c.titulo',
        'c.tipo_proyecto',
        DB::raw("CONCAT(b.apellidos, ' ', b.nombres) AS evaluador"),
        DB::raw("CONCAT(e.apellido1, ' ', e.apellido2 ,' ', e.nombres) AS responsable")
      )
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
        $utils->puntajeTesistas($request);
        $utils->AddExperienciaResponsable($request);
        $utils->AddExperienciaMiembros($request);
        $utils->addgiTotal($request);
        break;

      case "PSINFINV":
        $utils->totalpuntajeIntegrantesRenacyt($request);
        break;

      case "PSINFIPU":
        $utils->totalpuntajeIntegrantesRenacyt($request);
        break;

      case "PCONFIGI":
        $utils->docenteInvestigador($request);
        $utils->puntaje7UltimosAños($request);
        $utils->giCat($request);
        $utils->DocentesRecienteIngresoRRHH($request);
        break;

      case "ECI":
        // Tu código
        $utils->experiencia_gi($request);
        $utils->presupuesto_eci($request);
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
        'a.nombre AS area',
        'p.palabras_clave'
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
        'b.tipo',
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

    /**  Metodologia*/
    $documentos = DB::table('File as f')
      ->select([
        'f.recurso',
        DB::raw("CONCAT('/minio/', f.bucket, '/', f.key) AS url")
      ])
      ->where('f.tabla_id', '=', $request->query('proyecto_id'))
      ->where('f.bucket', '=', 'proyecto-doc')
      ->whereIn('f.recurso', ['METODOLOGIA_TRABAJO'])
      // ->where('f.estado', '=', 20)
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->recurso => $item->url, 'recurso' => $item->recurso];
      });

    /**Colaboracion Externa */
    $proyectoDoc = DB::table('Proyecto_doc as pdoc')
      ->select([
        'pdoc.comentario',
        'pdoc.categoria',
        'pdoc.nombre',
        DB::raw("CONCAT('/minio/', 'proyecto-doc' , '/' , pdoc.archivo ) AS url")
      ])
      ->where('pdoc.proyecto_id', '=', $request->query('proyecto_id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->categoria => $item->url, 'comentario' => $item->comentario, 'categoria' => $item->categoria, 'nombre' => $item->nombre];
      });

    $colaboracionExterna = DB::table('Proyecto_doc')
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

    $estadoArte = DB::table('Proyecto_doc')
      ->select([
        'id',
        'comentario',
        'nombre',
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url"),

      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 27)
      ->where('categoria', '=', 'anexo')
      ->where('nombre', '=', 'Estado del arte')
      ->where('estado', '=', 1)
      ->first();

    $archivoseci = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        'archivo'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('categoria', ['especificaciones-tecnicas', 'cotizacion-equipo'])
      ->get()
      ->map(function ($item) {
        $item->archivo = "/minio/proyecto-doc/" . $item->archivo;
        return $item;
      });


    $metodologia_trabajo = DB::table('Proyecto_doc')
      ->select([
        'id',
        'comentario',
        'nombre',
        'categoria',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('tipo', '=', 26)
      ->where('categoria', '=', 'anexo')
      ->where('nombre', '=', 'Metodología de trabajo')
      ->where('estado', '=', 1)
      ->first();

    $investigacion_base = isset($descripciones['investigacion_base']) ? $descripciones['investigacion_base'] : null;
    $proyectoId = $investigacion_base ? explode('-', $investigacion_base)[0] : 0;

    $inv_unmsm = DB::table('Proyecto as p')
      ->select([
        'p.id as proyecto_id',
        'p.titulo',
        'p.codigo_proyecto',
        'p.tipo_proyecto',
        'p.periodo',
        'f.nombre as facultad',
        'o.linea as ocde',
        'li.nombre as linea_investigacion',
        'g.grupo_nombre as grupo',
        'p.localizacion',
        'a.nombre as area'
      ])
      ->leftJoin('Facultad as f', 'f.id', '=', 'p.facultad_id')
      ->leftJoin('Ocde as o', 'o.id', '=', 'p.ocde_id')
      ->leftJoin('Linea_investigacion as li', 'li.id', '=', 'p.linea_investigacion_id')
      ->leftJoin('Grupo as g', 'g.id', '=', 'p.grupo_id')
      ->leftJoin('Area as a', 'a.id', '=', 'f.area_id')
      ->where('p.id', '=', $proyectoId)
      ->first();

    $especificaciones = DB::table('Proyecto_descripcion')
      ->select(
        'detalle'
      )
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('codigo', '=', 'desc_equipo')
      ->first();

    foreach ($integrantes as $integrante) {
      $deudas = DB::table('view_deudores as vdeuda')
        ->select([
          'vdeuda.investigador_id',
          'vdeuda.proyecto_integrante_id',
          'vdeuda.categoria',
          'vdeuda.detalle',
          'vdeuda.fecha_deuda',
          'vdeuda.fecha_sub',
          'vdeuda.periodo',
          'vdeuda.ptipo',
          'vdeuda.pcodigo'
        ])
        ->where('vdeuda.proyecto_integrante_id', '=', $integrante->id)
        ->get();

      $integrante->deudas = $deudas;
    }

    $detallesEci = DB::table('Proyecto_descripcion')
      ->select(
        'codigo',
        'detalle'
      )
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('codigo', ['impacto_propuesta', 'plan_manejo'])
      ->get();

    $impacto = [];
    foreach ($detallesEci as $data) {
      $impacto[$data->codigo] = $data->detalle;
    }

    $archivoseci = DB::table('Proyecto_doc')
      ->select([
        'nombre',
        'comentario',
        'archivo'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('categoria', ['impacto', 'sustento'])
      ->get()
      ->map(function ($item) {
        $item->archivo = "/minio/proyecto-doc/" . $item->archivo;
        return $item;
      });

    $archivoscotizacion = DB::table('Proyecto_doc')
      ->select([
        'id',
        'nombre',
        'comentario',
        'archivo'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->whereIn('categoria', ['especificaciones-tecnicas', 'cotizacion-equipo'])
      ->get()
      ->map(function ($item) {
        $item->archivo = "/minio/proyecto-doc/" . $item->archivo;
        return $item;
      });

    $archivos = $this->eciArchivos($request->query('proyecto_id'));

    return [
      'archivos_sustento' => $archivos,
      'archivocotizacion' => $archivoscotizacion,
      'archivoseci' => $archivoseci,
      'detallesEci' => $detallesEci,
      'especificaciones' => $especificaciones,
      'archivoeci' => $archivoseci,
      'proyecto' => $proyecto,
      'detalles' => $descripciones,
      'calendario' => $calendario,
      'presupuesto' => $presupuesto,
      'integrantes' => $integrantes,
      'responsable' => $responsable,
      'documentos' => $documentos,
      'proyectoDoc' => $proyectoDoc,
      'colaboracionExterna' => $colaboracionExterna,
      'estadoArte' => $estadoArte,
      'metodologiaTrabajo' => $metodologia_trabajo,
      'investigacion' => $inv_unmsm
    ];
  }

  public function eciArchivos(int $proyecto_id) {
    $items = DB::table('Proyecto_doc')
      ->select([
        'nombre',
        'comentario',
        DB::raw("CONCAT('/minio/proyecto-doc/', archivo) AS url")
      ])
      ->where('proyecto_id', '=', $proyecto_id)
      ->where('estado', '=', 1)
      ->where('categoria', '=', 'sustento')
      ->get();

    return $items;
  }
}

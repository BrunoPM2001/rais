<?php

namespace App\Http\Controllers\Evaluador\Evaluaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriteriosUtilsController extends Controller {
  public function puntajeTesistas(Request $request) {

    $proyecto = DB::table('Proyecto as p')
      ->select('p.tipo_proyecto', 'p.periodo')
      ->where('p.id', $request->query('proyecto_id'))
      ->first();

    $tipoProyecto = $proyecto->tipo_proyecto;
    $periodo = $proyecto->periodo;

    $evaluacionProyecto = DB::table('Evaluacion_opcion as eopcion')
      ->select('eopcion.id', 'eopcion.puntaje_max')
      ->where('eopcion.tipo', '=', $tipoProyecto)
      ->where('eopcion.periodo', '=', $periodo)
      ->where('eopcion.otipo', '=', 'tesista')
      ->first();

    $evaluacionId = $evaluacionProyecto->id;
    $puntaje_max = $evaluacionProyecto->puntaje_max;


    $tesistas = DB::table('Proyecto_integrante as t1')
      ->select('t1.proyecto_id')
      ->selectRaw("SUM(CASE WHEN t1.tipo_tesis LIKE 'bachillerato' THEN 1 ELSE 0 END) as cantidad_tesis_bachillerato")
      ->selectRaw("SUM(CASE WHEN t1.tipo_tesis LIKE 'licenciatura%' THEN 1 ELSE 0 END) as cantidad_tesis_licenciatura")
      ->selectRaw("SUM(CASE WHEN t1.tipo_tesis LIKE 'maestria' THEN 1 ELSE 0 END) as cantidad_tesis_maestria")
      ->selectRaw("SUM(CASE WHEN t1.tipo_tesis LIKE 'doctorado' THEN 1 ELSE 0 END) as cantidad_tesis_doctorado")
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->where('t1.proyecto_id', $request->query('proyecto_id'))
      ->whereNotNull('t1.tipo_tesis')
      ->where('t2.nombre', 'Tesista')
      ->groupBy('t1.proyecto_id')
      ->first();

    $cantidadTipoTesis = $tesistas ? $tesistas : null;
    $puntaje = 0;
    $puntaje += ($cantidadTipoTesis ? $cantidadTipoTesis->cantidad_tesis_licenciatura : 0) * 1.0;
    $puntaje += ($cantidadTipoTesis ? $cantidadTipoTesis->cantidad_tesis_maestria : 0) * 3.0;
    $puntaje += ($cantidadTipoTesis ? $cantidadTipoTesis->cantidad_tesis_doctorado : 0) * 5.0;

    if ($puntaje > $puntaje_max) {
      $puntaje = $puntaje_max;
    }
    //  Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => $evaluacionId
      ], [
        'puntaje' => $puntaje
      ]);
  }


  public function AddExperienciaResponsable(Request $request) {
    $proyecto = DB::table('Proyecto as p')
      ->select('p.tipo_proyecto', 'p.periodo')
      ->where('p.id', $request->query('proyecto_id'))
      ->first();

    $tipoProyecto = $proyecto->tipo_proyecto;
    $periodo = $proyecto->periodo;


    $evaluacionProyecto = DB::table('Evaluacion_opcion as eopcion')
      ->select('eopcion.id', 'eopcion.puntaje_max')
      ->where('eopcion.tipo', '=', $tipoProyecto)
      ->where('eopcion.periodo', '=', $periodo)
      ->where('eopcion.otipo', '=', 'responsable')
      ->first();

    $evaluacionId = $evaluacionProyecto->id;
    $puntaje_max = $evaluacionProyecto->puntaje_max;

    $proyecto = DB::table('Proyecto as p')
      ->select('pint.investigador_id')
      ->join('Proyecto_integrante as pint', 'pint.proyecto_id', '=', 'p.id')
      ->where('p.id', '=', $request->query('proyecto_id'))
      ->where('pint.condicion', '=', 'Responsable')
      ->first();

    $experienciaResponsable = DB::table('view_puntaje_7u')
      ->select('puntaje as total_puntaje')
      ->where('investigador_id', '=', $proyecto->investigador_id)
      ->first();


    $total = 0;

    if ($experienciaResponsable) {
      if ($experienciaResponsable->total_puntaje > 0) {
        $total = ($experienciaResponsable->total_puntaje * 0.1);
        if ($total >= $puntaje_max) {
          $total = $puntaje_max;
        }
      } else {
        $total = $experienciaResponsable->total_puntaje;
      }
    }


    //  Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => $evaluacionId
      ], [
        'puntaje' => $total
      ]);
  }


  public function AddExperienciaMiembros(Request $request) {

    $proyectoId = $request->query('proyecto_id');
    $puntajeIntegrantes = 0;
    $total = 0;
    $integrantesSum = [];


    $proyecto = DB::table('Proyecto as p')
      ->select('p.tipo_proyecto', 'p.periodo')
      ->where('p.id', $request->query('proyecto_id'))
      ->first();

    $tipoProyecto = $proyecto->tipo_proyecto;
    $periodo = $proyecto->periodo;

    $evaluacionProyecto = DB::table('Evaluacion_opcion as eopcion')
      ->select('eopcion.id', 'eopcion.puntaje_max')
      ->where('eopcion.tipo', '=', $tipoProyecto)
      ->where('eopcion.periodo', '=', $periodo)
      ->where('eopcion.otipo', '=', 'miembros')
      ->first();

    $evaluacionId = $evaluacionProyecto->id;
    $puntaje_max = $evaluacionProyecto->puntaje_max;


    // Capturar los IDs de los integrantes
    $integrantes = DB::table('Proyecto_integrante as t1')
      ->select('t1.investigador_id')
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->leftJoin('Usuario_investigador as t3', 't1.investigador_id', '=', 't3.id')
      ->where('t1.proyecto_id', $proyectoId)
      ->whereIn('t2.nombre', ['Co responsable', 'Miembro docente', 'Autor Corresponsal', 'Co-Autor'])
      ->get();


    foreach ($integrantes as $integrante) {
      if (!in_array($integrante->investigador_id, $integrantesSum)) {
        $integrantesSum[] = $integrante->investigador_id;
      }
    }


    $totalPuntaje = DB::table('view_puntaje_7u')
      ->whereIn('investigador_id', $integrantesSum)
      ->sum('puntaje'); // Suma todos los valores de la columna 'puntaje'

    $puntajeIntegrantes = ($totalPuntaje * 0.1) / count($integrantes);

    $total = $puntajeIntegrantes >= $puntaje_max ? $puntaje_max : $puntajeIntegrantes;

    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => $evaluacionId
      ], [
        'puntaje' => $total,
      ]);
  }

  public function addgiTotal(Request $request) {
    $grupos = [];
    $puntajes = [];
    $puntajeTotal = 0;
    $puntajeGlobal = 0;


    $proyecto = DB::table('Proyecto as p')
      ->select('p.tipo_proyecto', 'p.periodo')
      ->where('p.id', $request->query('proyecto_id'))
      ->first();

    $tipoProyecto = $proyecto->tipo_proyecto;
    $periodo = $proyecto->periodo;

    $evaluacionProyecto = DB::table('Evaluacion_opcion as eopcion')
      ->select('eopcion.id', 'eopcion.puntaje_max')
      ->where('eopcion.tipo', '=', $tipoProyecto)
      ->where('eopcion.periodo', '=', $periodo)
      ->where('eopcion.otipo', '=', 'catgi')
      ->first();

    $evaluacionId = $evaluacionProyecto->id;
    $puntaje_max = $evaluacionProyecto->puntaje_max;


    $grupos = DB::table('view_cat_gi')
      ->select('grupo_categoria')
      ->where('proyecto_id', $request->query('proyecto_id'))
      ->get();

    $i = 0;

    foreach ($grupos as $grupo) {

      switch ($grupo->grupo_categoria) {
        case 'A':
          $puntajegcat = 6;
          break;
        case 'B':
          $puntajegcat = 4;
          break;
        case 'C':
          $puntajegcat = 2;
          break;
        case 'D':
          $puntajegcat = 1;
          break;
        default:
          $puntajegcat = 0;
          break;
      }

      $puntajeTotal += $puntajegcat;
      $i++;
    }

    if ($i != 0) {
      $puntajeGlobal = ($puntajeTotal / $i);
    }


    $topeMaximo = $puntaje_max;

    if ($puntajeGlobal > $topeMaximo) {
      $puntajeGlobal = $topeMaximo;
    }

    //  Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => $evaluacionId
      ], [
        'puntaje' => $puntajeGlobal
      ]);
  }




  public function totalpuntajeIntegrantesRenacyt(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $fechaInicial = date("Y") - 7;
    $fechaFinal = date("Y") - 1;
    $totalPuntajeUltimos = 0;

    // Capturar los IDs de los integrantes
    $integrantes = DB::table('Proyecto_integrante as t1')
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->leftJoin('Usuario_investigador as t3', 't1.investigador_id', '=', 't3.id')
      ->where('t1.proyecto_id', $proyectoId)
      ->whereIn('t2.nombre', ['Responsable', 'Co Responsable', 'Miembro Docente', 'Autor Corresponsal', 'Co-Autor'])
      ->get();

    // Iterar sobre los IDs de los integrantes y calcular el puntaje total
    foreach ($integrantes as $integrante) {
      // Suma del puntaje de la tabla publicacion_autor
      $publicacionPuntaje = DB::table('Publicacion_autor as t1')
        ->select(DB::raw('SUM(t1.puntaje) as total'))
        ->join('Publicacion as t2', 't1.publicacion_id', '=', 't2.id')
        ->where('t1.investigador_id', $integrante->investigador_id)
        ->where('t2.validado', 1)
        ->whereBetween(DB::raw('YEAR(t2.fecha_publicacion)'), [$fechaInicial, $fechaFinal])
        ->first();

      // Suma del puntaje de la tabla patente_autor
      $patentePuntaje = DB::table('Patente_autor')
        ->select(DB::raw('SUM(puntaje) as total'))
        ->where('investigador_id', $integrante->investigador_id)
        ->whereBetween(DB::raw('YEAR(created_at)'), [$fechaInicial, $fechaFinal])
        ->first();

      $renacyt = DB::table('Usuario_investigador')
        ->select('renacyt')
        ->where('id', $integrante->investigador_id)
        ->whereNotNull('renacyt')
        ->where('renacyt', '!=', '')
        ->first();

      // Asegurar que los valores no sean nulos
      $publicacionPuntaje = $publicacionPuntaje->total ?? 0;
      $patentePuntaje = $patentePuntaje->total ?? 0;
      $renacyt = isset($renacyt->renacyt) ? 4 : 0;

      // Suma total de ambos puntajes para el investigador actual
      $totalPuntajeUltimos += (float)$publicacionPuntaje + (float)$patentePuntaje + $renacyt;
    }

    // Evitar la división por cero
    if (count($integrantes) > 0) {
      $puntajeIntegrantes = $totalPuntajeUltimos / count($integrantes);
    } else {
      $puntajeIntegrantes = 0;
    }

    $total = $puntajeIntegrantes * 0.1;
    $total = $total >= 10 ? 10 : $total;

    // Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1184
      ], [
        'puntaje' => $total
      ]);
  }
  public function docenteInvestigador(Request $request) {

    $proyectoId = $request->query('proyecto_id');
    $puntajeDocente = 0;
    $integrantes = DB::table('Proyecto_integrante as t1')
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->leftJoin('Usuario_investigador as t3', 't1.investigador_id', '=', 't3.id')
      ->where('t1.proyecto_id', $proyectoId)
      ->whereIn('t2.nombre', ['Responsable', 'Co Responsable', 'Miembro Docente', 'Autor Corresponsal', 'Co-Autor'])
      ->get();


    foreach ($integrantes as $integrante) {


      $cdi = DB::table('Eval_docente_investigador')
        ->select('estado')
        ->where('investigador_id', $integrante->investigador_id)
        ->where('estado', 'vigente')
        ->where('estado_real', 'VIGENTE')
        ->where('tipo_eval', 'Constancia')
        ->orderBy('fecha_fin', 'desc') // Ordena por fecha_fin de manera descendente
        ->first(); // Obtiene el primer registro

      $renacyt = DB::table('Usuario_investigador')
        ->select('renacyt')
        ->where('id', $integrante->investigador_id)
        ->whereNotNull('renacyt')
        ->where('renacyt', '!=', '')
        ->first();

      if ($cdi) {
        $puntajeDocente += 3;
      } else if ($renacyt) {
        $puntajeDocente += 2;
      } else {
        $puntajeDocente += 0;
      }
    }
    $puntajeDocente = $puntajeDocente >= 9 ? 9 : $puntajeDocente;

    // Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1218
      ], [
        'puntaje' => $puntajeDocente
      ]);
  }
  public function puntaje7UltimosAños(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $totalPuntajeUltimos = 0;
    $puntajeIntegrantes = 0;
    $total = 0;
    $integrantesSum = [];

    // Capturar los IDs de los integrantes
    $integrantes = DB::table('Proyecto_integrante as t1')
      ->select('t1.investigador_id')
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->leftJoin('Usuario_investigador as t3', 't1.investigador_id', '=', 't3.id')
      ->where('t1.proyecto_id', $proyectoId)
      ->whereIn('t2.nombre', ['Responsable', 'Co responsable', 'Miembro docente', 'Autor Corresponsal', 'Co-Autor'])
      ->get();

    foreach ($integrantes as $integrante) {
      if (!in_array($integrante->investigador_id, $integrantesSum)) {
        $integrantesSum[] = $integrante->investigador_id;
      }
    }


    $totalPuntaje = DB::table('view_puntaje_7u')
      ->whereIn('investigador_id', $integrantesSum)
      ->sum('puntaje'); // Suma todos los valores de la columna 'puntaje'

    $puntajeIntegrantes = ($totalPuntaje * 0.1) / count($integrantes);

    $total = $puntajeIntegrantes >= 10 ? 10 : $puntajeIntegrantes;

    // Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1219
      ], [
        'puntaje' => $total,
      ]);
  }

  public function giCat(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $puntajeCat = 0;

    $grupo = DB::table('Proyecto')
      ->select('grupo_id')
      ->where('id', $proyectoId)
      ->first();

    $grupoCat = DB::table('Grupo')
      ->select('grupo_categoria')
      ->where('id', $grupo->grupo_id)
      ->first();

    switch ($grupoCat->grupo_categoria) {
      case 'A':
        $puntajeCat = 6;
        break;
      case 'B':
        $puntajeCat = 4;
        break;
      case 'C':
        $puntajeCat = 2;
        break;
      case 'D':
        $puntajeCat = 1;
        break;
      default:
        $puntajeCat = 0;
        break;
    }

    $total = $puntajeCat >= 6 ? 6 : $puntajeCat;

    // Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1220
      ], [
        'puntaje' => $total
      ]);
  }

  public function DocentesRecienteIngresoRRHH(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $puntajeDocente = 0;
    $integrantes = DB::table('Proyecto_integrante as t1')
      ->select('t1.investigador_id', 't3.doc_numero')
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->leftJoin('Usuario_investigador as t3', 't1.investigador_id', '=', 't3.id')
      ->where('t1.proyecto_id', $proyectoId)
      ->get();

    foreach ($integrantes as $integrante) {

      $docente = DB::table('Repo_rrhh')
        ->where('ser_doc_id_act', $integrante->doc_numero)
        ->where('ser_doc_id_act', '!=', '')
        ->where(DB::raw('YEAR(ser_fech_in_unmsm)'), '>=', 2024)
        ->count();

      $grupo = DB::table('Grupo_integrante')
        ->select('grupo_id')
        ->where('investigador_id', $integrante->investigador_id)
        ->where(DB::raw('YEAR(fecha_inclusion)'), '>=', 2024)
        ->whereNot('condicion', 'like', 'EX %')
        ->count();

      if (($docente + $grupo) == 2) {
        $puntajeDocente += 1;
      } else {
        $puntajeDocente += 0;
      }
    }

    $puntajeDocente = $puntajeDocente >= 5 ? 5 : $puntajeDocente;

    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1221
      ], [
        'puntaje' => $puntajeDocente
      ]);
  }
}

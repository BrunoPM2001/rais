<?php

namespace App\Http\Controllers\Evaluador\Evaluaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriteriosUtilsController extends Controller {
  public function fetchPuntajeFormacionRrhh(Request $request) {
    $rrhhTesistas = DB::table('Proyecto_integrante as t1')
      ->select('t1.proyecto_id')
      ->selectSub(function ($query) {
        $query->from('Proyecto_integrante as pi')
          ->selectRaw('COUNT(*)')
          ->whereColumn('pi.proyecto_id', 't1.proyecto_id')
          ->where('pi.tipo_tesis', 'like', 'bachillerato');
      }, 'cantidad_tesis_bachillerato')
      ->selectSub(function ($query) {
        $query->from('Proyecto_integrante as pi')
          ->selectRaw('COUNT(*)')
          ->whereColumn('pi.proyecto_id', 't1.proyecto_id')
          ->where('pi.tipo_tesis', 'like', 'licenciatura%');
      }, 'cantidad_tesis_licenciatura')
      ->selectSub(function ($query) {
        $query->from('Proyecto_integrante as pi')
          ->selectRaw('COUNT(*)')
          ->whereColumn('pi.proyecto_id', 't1.proyecto_id')
          ->where('pi.tipo_tesis', 'like', 'maestria');
      }, 'cantidad_tesis_maestria')
      ->selectSub(function ($query) {
        $query->from('Proyecto_integrante as pi')
          ->selectRaw('COUNT(*)')
          ->whereColumn('pi.proyecto_id', 't1.proyecto_id')
          ->where('pi.tipo_tesis', 'like', 'doctorado');
      }, 'cantidad_tesis_doctorado')
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->where('t1.proyecto_id', $request->query('proyecto_id'))
      ->whereNotNull('t1.tipo_tesis')
      ->where('t2.nombre', 'Tesista')
      ->groupBy('t1.proyecto_id')
      ->first();


    $cantidadTipoTesis = $rrhhTesistas ? $rrhhTesistas : null;
    $puntaje = 0;
    $puntaje += ($cantidadTipoTesis ? $cantidadTipoTesis->cantidad_tesis_licenciatura : 0) * 1.0;
    $puntaje += ($cantidadTipoTesis ? $cantidadTipoTesis->cantidad_tesis_maestria : 0) * 3.0;
    $puntaje += ($cantidadTipoTesis ? $cantidadTipoTesis->cantidad_tesis_doctorado : 0) * 5.0;

    //  Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1148
      ], [
        'puntaje' => $puntaje
      ]);
  }

  public function AddExperienciaResponsable(Request $request) {
    $anioInicio = 2017;
    $anioFin = 2024;

    $experienciaResponsable = DB::table('Proyecto_integrante as t1')
      ->select(
        DB::raw("SUM(IFNULL((
            SELECT SUM(_t1.puntaje)
            FROM Publicacion_autor AS _t1
            JOIN Publicacion AS _t2 ON _t1.publicacion_id = _t2.id
            WHERE 
                YEAR(_t2.fecha_publicacion) BETWEEN $anioInicio AND $anioFin
                AND _t1.investigador_id = t1.investigador_id
            GROUP BY _t1.investigador_id
        ), 0)) AS total_puntaje"),
        DB::raw("COUNT(*) AS total_dp")
      )
      ->join('Grupo_integrante as t2', 't2.id', '=', 't1.grupo_integrante_id')
      ->where('t2.tipo', 'DOCENTE PERMANENTE')
      ->where('t1.proyecto_id', $request->query('proyecto_id'))
      ->where('t1.condicion', 'Responsable')
      ->first();

    $total = 0;

    if ($experienciaResponsable) {
      if ($experienciaResponsable->total_puntaje > 0) {
        $total = ($experienciaResponsable->total_puntaje * 0.1 / $experienciaResponsable->total_dp);
        if ($total >= 4) {
          $total = 4;
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
        'evaluacion_opcion_id' => 1152
      ], [
        'puntaje' => $total
      ]);
  }

  public function addgiTotal(Request $request) {
    $grupos = [];
    $puntajes = [];
    $puntajeGlobal = 0;


    $ProyectoIntegrantes = DB::table('Proyecto_integrante as t1')
      ->join('Usuario_investigador as t2', 't1.investigador_id', '=', 't2.id')
      ->select(
        't2.id as investigador_id',
        't1.grupo_id',
        DB::raw("CONCAT_WS(', ', UPPER(CONCAT_WS(' ', t2.apellido1, t2.apellido2)), t2.nombres) as fullname"),
        DB::raw("CONCAT_WS(' ', t2.apellido1, t2.apellido2) as apellidos"),
        't2.nombres as nombres'
      )
      ->where('t1.proyecto_id', $request->query('proyecto_id'))
      ->get();

    foreach ($ProyectoIntegrantes as $responsable) {
      if (!isset($grupos[$responsable->grupo_id])) {
        // Si no está, lo añadimos al array y marcamos como que ya está presente
        $grupos[] = $responsable->grupo_id;
      }
    }
    $grupos = array_unique($grupos);

    foreach ($grupos as $grupo) {
      $grupoCat = DB::table('Grupo')
        ->select('grupo_categoria')
        ->where('id', $grupo)
        ->first();

      switch ($grupoCat->grupo_categoria) {
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

      $puntajes[] = $puntajegcat;
    }

    $i = 0;
    $puntajeTotal = 0;

    foreach ($puntajes as $puntaje) {
      $i++;
      $puntajeTotal = $puntajeTotal + $puntaje;
    }

    $puntajeGlobal = $puntajeTotal / $i;

    $topeMaximo = 6;

    if ($puntajeGlobal > $topeMaximo) {
      $puntajeGlobal = $topeMaximo;
    }

    //  Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1154
      ], [
        'puntaje' => $puntajeGlobal
      ]);
  }

  // public function totalpuntajeIntegrantes(Request $request)
  // {
  //   $proyectoId = $request->query('proyecto_id');
  //   $fechaInicial = date("Y") - 7;
  //   $fechaFinal = date("Y") - 1;
  //   $totalPuntajeUltimos = 0;

  //   // Capturar los IDs de los integrantes
  //   $integrantes = DB::table('Proyecto_integrante as t1')
  //     ->join('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
  //     ->where('t1.proyecto_id', $proyectoId)
  //     ->whereIn('t1.proyecto_integrante_tipo_id', [57, 58])
  //     ->get();

  //   // Iterar sobre los IDs de los integrantes y calcular el puntaje total
  //   foreach ($integrantes as $integrante) {
  //     // Suma del puntaje de la tabla publicacion_autor
  //     $publicacionPuntaje = DB::table('Publicacion_autor as t1')
  //       ->select(DB::raw('SUM(t1.puntaje) as total'))
  //       ->join('Publicacion as t2', 't1.publicacion_id', '=', 't2.id')
  //       ->where('t1.investigador_id', $integrante->investigador_id)
  //       ->where('t2.validado', 1)
  //       ->whereBetween(DB::raw('YEAR(t2.fecha_publicacion)'), [$fechaInicial, $fechaFinal])
  //       ->first();

  //     // Suma del puntaje de la tabla patente_autor
  //     $patentePuntaje = DB::table('Patente_autor')
  //       ->select(DB::raw('SUM(puntaje) as total'))
  //       ->where('investigador_id', $integrante->investigador_id)
  //       ->whereBetween(DB::raw('YEAR(created_at)'), [$fechaInicial, $fechaFinal])
  //       ->first();

  //     // Asegurar que los valores no sean nulos
  //     $publicacionPuntaje = $publicacionPuntaje->total ?? 0;
  //     $patentePuntaje = $patentePuntaje->total ?? 0;

  //     // Suma total de ambos puntajes para el investigador actual
  //     $totalPuntajeUltimos += (float)$publicacionPuntaje + (float)$patentePuntaje;
  //   }

  //   $puntajeCat = $totalPuntajeUltimos / count($integrantes);

  //   $total = $puntajeIntegrantes * 0.1;
  //   $total = $total >= 10 ? 10 : $total;

  //   //  Actualizar puntaje
  //   DB::table('Evaluacion_proyecto')
  //     ->updateOrInsert([
  //       'proyecto_id' => $request->query('proyecto_id'),
  //       'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
  //       'evaluacion_opcion_id' => 1153
  //     ], [
  //       'puntaje' => $total
  //     ]);
  // }

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
      ->leftJoin('Proyecto_integrante_tipo as t2', 't1.proyecto_integrante_tipo_id', '=', 't2.id')
      ->leftJoin('Usuario_investigador as t3', 't1.investigador_id', '=', 't3.id')
      ->where('t1.proyecto_id', $proyectoId)
      ->get();

    foreach ($integrantes as $integrante) {

      $docente = DB::table('Repo_rrhh')
        ->where('ser_doc_id_act', $integrante->doc_numero)
        ->where('ser_doc_id_act', '!=', '')
        ->where('ser_fech_in_unmsm', '>=', '2023-01-01')
        ->first();

      if ($docente) {
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

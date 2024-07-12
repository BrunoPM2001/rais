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
      ->firts();


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

  public function experienciaOtros(Request $request) {
    $anioInicio = 2017;
    $anioFin = 2024;

    $puntajeExperienciaOtros = DB::table('Proyecto_integrante as t1')
      ->join('Grupo_integrante as t2', 't2.id', '=', 't1.grupo_integrante_id')
      ->select(
        DB::raw("SUM(
            IFNULL(
                (
                    SELECT SUM(_t1.puntaje)
                    FROM Publicacion_autor AS _t1
                    JOIN Publicacion AS _t2 ON _t1.publicacion_id = _t2.id
                    WHERE 
                        YEAR(_t2.fecha_publicacion) BETWEEN $anioInicio AND $anioFin
                        AND _t1.investigador_id = t1.investigador_id
                    GROUP BY _t1.investigador_id
                ), 0)
            ) AS total_puntaje"),
        DB::raw("COUNT(*) AS total_dp")
      )
      ->where('t2.tipo', 'DOCENTE PERMANENTE')
      ->where('t1.proyecto_id', $request->query('proyecto_id'))
      ->whereNotIn('t1.proyecto_integrante_tipo_id', [56])
      ->first();

    $total = 0;
    if ($puntajeExperienciaOtros) {
      if ($puntajeExperienciaOtros->total_puntaje > 0) {
        $total = ($puntajeExperienciaOtros->total_puntaje * 0.1 / $puntajeExperienciaOtros->total_dp);
      } else {
        $total = $puntajeExperienciaOtros->total_puntaje;
      }
    }

    //  Actualizar puntaje
    DB::table('Evaluacion_proyecto')
      ->updateOrInsert([
        'proyecto_id' => $request->query('proyecto_id'),
        'evaluador_id' => $request->attributes->get('token_decoded')->evaluador_id,
        'evaluacion_opcion_id' => 1153
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
        ->where('id', $grupo->id)
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
}

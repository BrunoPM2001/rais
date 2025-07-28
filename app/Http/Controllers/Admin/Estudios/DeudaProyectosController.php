<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeudaProyectosController extends Controller {
  public function listadoIntegrantes(Request $request) {
    if ($request->query('tabla') == "Nuevo") {
      $integrantes = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
        ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
        ->leftJoin('Licencia AS d', 'd.investigador_id', '=', 'c.id')
        ->leftJoin('Licencia_tipo AS e', 'e.id', '=', 'd.licencia_tipo_id')
        ->leftJoin('Proyecto_integrante_deuda AS f', 'f.proyecto_integrante_id', '=', 'a.id')
        ->select(
          'a.id',
          'c.doc_numero',
          'c.apellido1',
          'c.apellido2',
          'c.nombres',
          'b.nombre AS condicion',
          'e.tipo AS licencia',
          'f.categoria AS tipo_deuda',
          'f.detalle AS comentario',
          'f.fecha_deuda',
          'f.fecha_sub'
        )
        ->where('a.proyecto_id', '=', $request->query('id'))
        ->get();

      return $integrantes;
    } else {
      $integrantes = DB::table('Proyecto_integrante_H AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->leftJoin('Licencia AS c', 'c.investigador_id', '=', 'b.id')
        ->leftJoin('Licencia_tipo AS d', 'd.id', '=', 'c.licencia_tipo_id')
        ->leftJoin('Proyecto_integrante_deuda AS e', 'e.proyecto_integrante_h_id', '=', 'a.id')
        ->select(
          'a.id',
          'b.doc_numero',
          'b.apellido1',
          'b.apellido2',
          'b.nombres',
          'a.condicion',
          'd.tipo AS licencia',
          'e.categoria AS tipo_deuda',
          'e.informe',
          'e.detalle',
          'e.fecha_sub'
        )
        ->where('a.proyecto_id', '=', $request->query('id'))
        ->get();

      return $integrantes;
    }
  }

  public function listadoProyectos() {
    $deudas = DB::table('view_proyectos AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        DB::raw("CONCAT(a.proyecto_origen, '_', a.proyecto_id) AS id"),
        DB::raw("CASE
          WHEN a.proyecto_origen COLLATE utf8mb4_unicode_ci = 'PROYECTO_BASE' THEN 'Nuevo'
          WHEN a.proyecto_origen COLLATE utf8mb4_unicode_ci = 'PROYECTO' THEN 'Antiguo'
        END as proyecto_origen"),
        'a.proyecto_id',
        'a.codigo AS codigo_proyecto',
        'a.tipo AS tipo_proyecto',
        'a.periodo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS responsable"),
        'a.xtitulo AS titulo',
        'a.facultad',
        DB::raw("CASE
          WHEN (a.deuda IS NULL OR a.deuda <= 0) THEN 'NO'
          WHEN a.deuda > 0 AND a.deuda <= 3 THEN 'SI'
          WHEN a.deuda > 3 THEN 'SUBSANADA'
        END as deuda"),
        'a.fecha_inscripcion AS created_at',
        'a.updated_at'
      ])
      ->whereNotIn('a.tipo', ['PFEX', 'FEX', 'SIN-CON'])
      ->orderBy('a.fecha_inscripcion', 'DESC')
      ->orderBy('a.facultad', 'DESC')
      ->get();

    return $deudas;
  }

  public function listadoProyectosNoDeuda() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable')
      )
      ->where('condicion', '=', 'Responsable');

    $lista = DB::table('Proyecto AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($responsable, 'res', 'res.proyecto_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'a.titulo',
        'b.nombre AS facultad',
        'res.responsable',
        'a.deuda',
        'a.periodo'
      )
      ->where(function ($query) {
        $query->orWhere('a.deuda', '<', '1')
          ->orWhere('a.deuda', '=', 2)
          ->orWhere('a.deuda', '=', 8)
          ->orWhereNull('a.deuda');
      })
      ->where('a.estado', '=', 1)
      ->get();

    return $lista;
  }

  public function listadoDeudaAcademica(Request $request) {

    $opciones = [];
    $tipoProyecto = $request->query('tipo_proyecto');

    switch ($tipoProyecto) {
      case 'PCONFIGI':
        $opciones = [
          'Informe académico' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PCONFIGI-INV':
        $opciones = [
          'Informe académico' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PSINFINV':
        $opciones = [
          'Informe académico' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PSINFIPU':
        $opciones = [
          'Resultados de la publicación' => 'Resultados de la publicación',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PTPGRADO':
        $opciones = [
          'Informe académico de avance' => 'Informe académico de avance',
          'Informe académico final' => 'Informe académico final',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PTPMAEST':
        $opciones = [
          'Informe académico de avance' => 'Informe académico de avance',
          'Informe académico final' => 'Informe académico final',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PTPDOCTO':
        $opciones = [
          'Informe académico de avance' => 'Informe académico de avance',
          'Segundo informe académico de avance' => 'Segundo informe académico de avance',
          'Informe académico final' => 'Informe académico final',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PEVENTO':
        $opciones = [
          'Informe académico' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PINVPOS':
        $opciones = [
          'Informe académico' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PTPBACHILLER':
        $opciones = [
          'Informe académico final' => 'Informe académico final',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PMULTI':
        $opciones = [
          'Informe académico final' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      case 'PINTERDIS':
        $opciones = [
          'Informe académico final' => 'Informe académico',
          '0' => 'Sin deuda',
        ];
        break;

      default:
        $opciones = [];
        break;
    }

    return response()->json([
      'opciones' => $opciones
    ]);
  }
  public function getResponsableProyecto($tipoProyecto) {
    $tipoIntegrante = [];

    switch ($tipoProyecto) {
      case 'PCONFIGI':
        $tipoIntegrante = [1];
        break;

      case 'PCONFIGI-INV':
        $tipoIntegrante = [36];
        break;

      case 'PSINFINV':
        $tipoIntegrante = [7];
        break;

      case 'PSINFIPU':
        $tipoIntegrante = [13];
        break;

      case 'PTPGRADO':
        $tipoIntegrante = [15];
        break;

      case 'PTPMAEST':
        $tipoIntegrante = [17];
        break;

      case 'PTPDOCTO':
        $tipoIntegrante = [19];
        break;

      case 'PEVENTO':
        $tipoIntegrante = [21];
        break;

      case 'PINVPOS':
        $tipoIntegrante = [28];
        break;

      case 'PTPBACHILLER':
        $tipoIntegrante = [66];
        break;

      case 'PMULTI':
        $tipoIntegrante = [56];
        break;

      case 'PINTERDIS':
        $tipoIntegrante = [74];
        break;

      case 'PRO-CTIE':
        $tipoIntegrante = [86];
        break;

      case 'ECI':
        $tipoIntegrante = [30];
        break;

      case 'PFEX':
        $tipoIntegrante = [44];
        break;

      case 'RFPLU':
        $tipoIntegrante = [70];
        break;

      case 'SPINOFF':
        $tipoIntegrante = [83];
        break;

      default:
        $tipoIntegrante = [];
        break;
    }

    return $tipoIntegrante;
  }

  public function asignarDeuda(Request $request) {

    $proyectoId = $request->input('proyecto_id');
    $tipoProyecto = $request->input('tipo_proyecto');
    $deudaEconomica = $request->input('deuda_economica')['value'];
    $deudaAcademica = $request->input('deuda_academica')['value'];
    $deudaFecha = $request->input('fecha_deuda');
    $deudaDetalle = $request->input('detalle_deuda');
    $deudaComentario = $request->input('comentario_deuda');
    $tipoIntegrante = [];
    $categoria = "";
    $resultados = [];

    if (!is_numeric($deudaAcademica)) {
      $deudaAcademica = 1;
    } else {
      $deudaAcademica = 0;
    }

    $tipoDeuda = intval($deudaAcademica) + intval($deudaEconomica);

    if ($tipoDeuda == 1) {
      $categoria = 'Deuda Académica';
    } else if ($tipoDeuda == 2) {
      $categoria = 'Deuda Económica';
    } else if ($tipoDeuda == 3) {
      $categoria = 'Deuda Académica y Económica';
    }

    $tipoIntegrante = DB::table('Proyecto_integrante_tipo')
      ->select([
        'id'
      ])
      ->where('tipo_proyecto', '=', $tipoProyecto)
      ->where('aplica_deuda', '=', 1)
      ->pluck('id');

    $integrantes = DB::table('Proyecto_integrante')
      ->where('proyecto_id', $proyectoId)
      ->whereIn('proyecto_integrante_tipo_id', $tipoIntegrante)
      ->get();

    foreach ($integrantes as $integrante) {

      $numIntegrante = DB::table('Proyecto_integrante_deuda')
        ->where('proyecto_integrante_id', $integrante->id)
        ->count();

      /** NO existe el integrante con Deuda */
      if ($numIntegrante == 0) {
        /**Deuda Academica */
        if ($tipoDeuda == 1) {

          $resultado = DB::table('Proyecto_integrante_deuda')->insert([
            'proyecto_integrante_id' => $integrante->id,
            'tipo' => $tipoDeuda,
            'categoria' => $categoria,
            'informe' => $deudaDetalle,
            'detalle' => $deudaComentario,
            'fecha_deuda' => $deudaFecha,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);
          $resultados[] = $resultado;

          /**Deuda Economica */
        } else if ($tipoDeuda == 2) {

          $responsable = $this->getResponsableProyecto($tipoProyecto);

          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {

            $resultado = DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => $tipoDeuda,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
            $resultados[] = $resultado;
          }

          /** Deuda Academica y Economica */
        } else if ($tipoDeuda == 3) {

          $responsable = $this->getResponsableProyecto($tipoProyecto);

          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {

            $tipoDeuda = 3;
            $categoria = 'Deuda Académica y Económica';

            $resultado = DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => $tipoDeuda,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
            $resultados[] = $resultado;
          } else {
            $tipoDeuda = 1;
            $categoria = 'Deuda Académica';

            $resultado = DB::table('Proyecto_integrante_deuda')->insert([

              'proyecto_integrante_id' => $integrante->id,
              'tipo' => $tipoDeuda,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
            $resultados[] = $resultado;
          }
        }

        /** Existe el integrante con Deuda */
      } else {

        $existeIntegrante = DB::table('Proyecto_integrante_deuda')
          ->where('proyecto_integrante_id', $integrante->id)
          ->first();

        if ($existeIntegrante) {

          if ($tipoDeuda == 1) {
            $resultado = DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => $tipoDeuda,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
            $resultados[] = $resultado;
          } else if ($tipoDeuda == 2) {

            $responsable = $this->getResponsableProyecto($tipoProyecto);

            if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
              $resultado =  DB::table('Proyecto_integrante_deuda')
                ->where('proyecto_integrante_id', $integrante->id)
                ->update([
                  'tipo' => $tipoDeuda,
                  'categoria' => $categoria,
                  'informe' => $deudaDetalle,
                  'detalle' => $deudaComentario,
                  'fecha_deuda' => $deudaFecha,
                  'updated_at' => Carbon::now()
                ]);
              $resultados[] = $resultado;
            } else {
              $resultado = DB::table('Proyecto_integrante_deuda')
                ->where('proyecto_integrante_id', $integrante->id)
                ->delete();
              $resultados[] = $resultado;
            }
          } else if ($tipoDeuda == 3) {

            $categoria = 'Deuda Académica y Económica';
            $responsable = $this->getResponsableProyecto($tipoProyecto);

            if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
              $resultado = DB::table('Proyecto_integrante_deuda')
                ->where('proyecto_integrante_id', $integrante->id)
                ->update([
                  'tipo' => $tipoDeuda,
                  'categoria' => $categoria,
                  'informe' => $deudaDetalle,
                  'detalle' => $deudaComentario,
                  'fecha_deuda' => $deudaFecha,
                  'updated_at' => Carbon::now()
                ]);
              $resultados[] = $resultado;
            } else {
              $tipoDeuda = 1;
              $categoria = 'Deuda Académica';

              $resultado = DB::table('Proyecto_integrante_deuda')->insert([
                'proyecto_integrante_id' => $integrante->id,
                'tipo' => $tipoDeuda,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_deuda' => $deudaFecha,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
              ]);
              $resultados[] = $resultado;
            }
          }
        } else {
          return response()->json(['message' => 'Registro no encontrado'], 404);
        }
      }
    }

    // Validar si todos los registros fueron exitosos
    $todosExitosos = !in_array(false, $resultados, true);

    if ($todosExitosos) {
      return response()->json(['message' => 'success', 'detail' => '  Se asigno Deuda a todos los miembros fueron registrados exitosamente'], 200);
    } else {
      return response()->json(['message' => 'error', 'detail' => 'Hubo un problema con el registro de algunos miembros'], 500);
    }
  }

  public function proyectoDeuda(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $proyectoOrigen = $request->query('proyecto_origen');
    $tipoProyecto = $request->query('tipo_proyecto');
    $deudaAcademica = "";
    $deudaEconomica = "";

    $tipoIntegrante = [];

    switch ($tipoProyecto) {
      case 'PCONFIGI':
        $tipoIntegrante = [1];
        break;

      // Responsable
      case 'PCONFIGI-INV':
        $tipoIntegrante = [36];
        break;

      // Responsable
      case 'PSINFINV':
        $tipoIntegrante = [7];
        break;

      // Autor Corresponsal
      case 'PSINFIPU':
        $tipoIntegrante = [13];
        break;

      // Asesor
      case 'PTPGRADO':
        $tipoIntegrante = [15];
        break;

      // Asesor
      case 'PTPMAEST':
        $tipoIntegrante = [17];
        break;

      //Comite
      case 'PTPDOCTO':
        $tipoIntegrante = [19];
        break;

      //Responsable
      case 'PEVENTO':
        $tipoIntegrante = [21];
        break;

      // Responsable
      case 'PINVPOS':
        $tipoIntegrante = [28];
        break;

      // Responsable
      case 'PTPBACHILLER':
        $tipoIntegrante = [66];
        break;

      // Responsable
      case 'PMULTI':
        $tipoIntegrante = [56];
        break;

      // Responsable
      case 'PINTERDIS':
        $tipoIntegrante = [74];
        break;

      // Asesor
      case 'PRO-CTIE':
        $tipoIntegrante = [86];
        break;

      // Coordinador
      case 'ECI':
        $tipoIntegrante = [30];
        break;

      // Coordinador General
      case 'PFEX':
        $tipoIntegrante = [44];
        break;

      // Autor Corresponsal
      case 'RFPLU':
        $tipoIntegrante = [70];
        break;

      // Asesor
      case 'SPINOFF':
        $tipoIntegrante = [83];
        break;

      default:
        $tipoIntegrante = [];
        break;
    }

    // $datosRecibidos = [
    //   'proyecto_id' => $proyectoId,
    //   'proyecto_origen' => $proyectoOrigen,
    //   'tipo_proyecto' => $tipoProyecto,
    //   'tipo_integrante' => $tipoIntegrante
    // ];


    if ($proyectoOrigen == 'Nuevo') {
      $deuda = DB::table('Proyecto_integrante as pint')
        ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_id', '=', 'pint.id')
        ->select('*')
        ->where('pint.proyecto_id', $proyectoId)
        ->whereIn('pint.proyecto_integrante_tipo_id', $tipoIntegrante)
        ->first();
    } else if ($proyectoOrigen == 'Antiguo') {
      $deuda = DB::table('Proyecto_integrante_H as pint')
        ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_h_id', '=', 'pint.id')
        ->select('*')
        ->where('pint.proyecto_id', $proyectoId)
        ->whereIn('pint.proyecto_integrante_tipo_id', $tipoIntegrante)
        ->first();
    }

    if ($deuda->tipo == 1) {
      $deudaAcademica = 'Deuda Académica';
      $deudaEconomica = 'Sin deuda';
    } else if ($deuda->tipo == 2) {
      $deudaAcademica = 'Sin deuda';
      $deudaEconomica = 'Deuda Económica';
    } else if ($deuda->tipo == 3) {
      $deudaAcademica = 'Deuda Académica';
      $deudaEconomica = 'Deuda Económica';
    }
    return response()->json([
      'deuda' => $deuda,
      'deuda_academica' => $deudaAcademica,
      'deuda_economica' => $deudaEconomica
    ]);
  }

  public function getResponsable($tipoProyecto, $proyectoId) {
    $integrantes = DB::table('Proyecto_integrante as pint')
      ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_id', '=', 'pint.id')
      ->select('*')
      ->where('pint.proyecto_id', $proyectoId)
      ->get();

    $responsable = $this->getResponsableProyecto($tipoProyecto);

    foreach ($integrantes as $integrante) {

      if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {

        return $integrante->tipo;
      }
    }
  }
  public function getTipoDeuda(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $proyectoOrigen = $request->query('proyecto_origen');
    $tipoProyecto = $request->query('tipo_proyecto');

    if ($proyectoOrigen == 'Nuevo') {
      return $this->getResponsable($tipoProyecto, $proyectoId);
    } else if ($proyectoOrigen == 'Antiguo') {
      return $this->getResponsable($tipoProyecto, $proyectoId);
    }
  }

  public function subsanarDeuda(Request $request) {
    $proyectoId = $request->input('proyecto_id');
    $tipoProyecto = $request->input('tipo_proyecto');
    $proyectoOrigen = $request->input('proyecto_origen');
    $subsanarEconomica = $request->input('subsanar_economica')['value'] ?? 0;
    $subsanarAcademica = $request->input('subsanar_academica')['value'] ?? 0;
    $fechaSubsanar = $request->input('fecha_subsanar');
    $deudaDetalle = $request->input('detalle_deuda');
    $deudaComentario = $request->input('comentario_deuda');
    $tipoIntegrante = [];
    $tipoDeuda = 0;
    $resultados = [];
    $tipoDeuda = $this->getResponsable($tipoProyecto, $proyectoId);
    $responsable = $this->getResponsableProyecto($tipoProyecto);

    $integrantes = DB::table('Proyecto_integrante as pint')
      ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_id', '=', 'pint.id')
      ->select('*')
      ->where('pint.proyecto_id', $proyectoId)
      ->get();

    switch ($subsanarAcademica) {
      case 4:
        $categoria = 'Presentó informe académico';
        break;
      case 5:
        $categoria = 'Presentó informe académico de avance';
        break;
      case 7:
        $categoria = 'Presentó informe académico final';
        break;
    }

    switch ($subsanarEconomica) {
      case 8:
        $categoria = 'Deuda económica subsanada';
        break;
    }

    if ($proyectoOrigen == 'Nuevo') {

      foreach ($integrantes as $integrante) {

        /** Subsanar Deuda Academica */
        if ($tipoDeuda == 1) {

          $resultado = DB::table('Proyecto_integrante_deuda')
            ->where('proyecto_integrante_id', $integrante->proyecto_integrante_id)
            ->update([
              'tipo' => $subsanarAcademica,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_sub' => $fechaSubsanar,
              'updated_at' => Carbon::now()
            ]);
          $resultados[] = $resultado;
          /** Subsanar Deuda Economica */
        } else if ($tipoDeuda == 2) {

          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            $resultado = DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->proyecto_integrante_id)
              ->update([
                'tipo' => $subsanarEconomica,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_sub' => $fechaSubsanar,
                'updated_at' => Carbon::now()
              ]);
            $resultados[] = $resultado;
          }
          /** Subsanar Deuda Academica y Economica */
        } else if ($tipoDeuda == 3) {

          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {

            switch ($subsanarEconomica) {
              case 8:
                $categoria = 'Deuda económica subsanada';
                break;
            }

            $resultado = DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->proyecto_integrante_id)
              ->update([
                'tipo' => $subsanarEconomica,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_sub' => $fechaSubsanar,
                'updated_at' => Carbon::now()
              ]);
            $resultados[] = $resultado;
          } else {

            switch ($subsanarAcademica) {
              case 4:
                $categoria = 'Presentó informe académico';
                break;
              case 5:
                $categoria = 'Presentó informe académico de avance';
                break;
              case 7:
                $categoria = 'Presentó informe académico final';
                break;
            }

            $resultado = DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->proyecto_integrante_id)
              ->update([
                'tipo' => $subsanarAcademica,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_sub' => $fechaSubsanar,
                'updated_at' => Carbon::now()
              ]);
            $resultados[] = $resultado;
          }
        }
      }
    } else if ($proyectoOrigen == 'Antiguo') {

      foreach ($integrantes as $integrante) {

        /** Subsanar Deuda Academica */
        if ($integrante->tipo == 1) {

          DB::table('Proyecto_integrante_deuda')
            ->where('proyecto_integrante_h_id', $integrante->proyecto_integrante_h_id)
            ->update([
              'tipo' => $subsanarAcademica,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_sub' => $fechaSubsanar,
              'updated_at' => Carbon::now()
            ]);
          /** Subsanar Deuda Economica */
        } else if ($integrante->tipo == 2) {

          DB::table('Proyecto_integrante_deuda')
            ->where('proyecto_integrante_h_id', $integrante->proyecto_integrante_h_id)
            ->update([
              'tipo' => $subsanarEconomica,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_sub' => $fechaSubsanar,
              'updated_at' => Carbon::now()
            ]);
          /** Subsanar Deuda Academica y Economica */
        } else if ($integrante->tipo == 3) {

          $responsable = $this->getResponsableProyecto($tipoProyecto);

          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {

            switch ($subsanarEconomica) {
              case 8:
                $categoria = 'Deuda económica subsanada';
                break;
            }

            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_h_id', $integrante->proyecto_integrante_h_id)
              ->update([
                'tipo' => $subsanarEconomica,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_sub' => $fechaSubsanar,
                'updated_at' => Carbon::now()
              ]);
          } else {

            switch ($subsanarAcademica) {
              case 4:
                $categoria = 'Presentó informe académico';
                break;
              case 5:
                $categoria = 'Presentó informe académico de avance';
                break;
              case 7:
                $categoria = 'Presentó informe académico final';
                break;
            }
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_h_id', $integrante->proyecto_integrante_h_id)
              ->update([
                'tipo' => $subsanarAcademica,
                'categoria' => $categoria,
                'informe' => $deudaDetalle,
                'detalle' => $deudaComentario,
                'fecha_sub' => $fechaSubsanar,
                'updated_at' => Carbon::now()
              ]);
          }
        }
      }
    }
    // Validar si todos los registros fueron exitosos
    $todosExitosos = !in_array(false, $resultados, true);

    if ($todosExitosos) {
      return response()->json(['message' => 'success', 'detail' => '  Se subsano deuda a  todos los miembros'], 200);
    } else {
      return response()->json(['message' => 'error', 'detail' => 'Hubo un problema con la subsanacion de algunos miembros'], 500);
    }
  }
}

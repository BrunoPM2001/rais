<?php

namespace App\Http\Controllers\Admin\Estudios;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
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
          'f.informe AS detalle',
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
          'e.informe AS detalle',
          'e.detalle AS comentario',
          'e.fecha_sub'
        )
        ->where('a.proyecto_id', '=', $request->query('id'))
        ->get();
      return $integrantes;
    }
  }

  public function listadoProyectos(Request $request) {
    $investigadorId = $request->query('investigador_id');
    $investigadores = DB::table('Proyecto_integrante as pi')
      ->join('Usuario_investigador as ui', 'ui.id', '=', 'pi.investigador_id')
      ->select(
        'pi.proyecto_id',
        DB::raw("JSON_ARRAYAGG(CONCAT(ui.apellido1, ' ', ui.apellido2, ', ', ui.nombres)) as investigadores")
      )
      ->groupBy('pi.proyecto_id');

    $estadoDeudaAntiguos = DB::table('Proyecto_integrante_H as pi')
      ->join('Proyecto_integrante_deuda as pd', 'pd.proyecto_integrante_h_id', '=', 'pi.id')
      ->select(
          'pi.proyecto_id',
          DB::raw("
            MAX(
              CASE
                WHEN pd.tipo IN (1,2,3) THEN 1
                WHEN pd.tipo BETWEEN 4 AND 8 THEN 2
                ELSE 0
              END
            ) as estado_deuda
          ")
      )
      ->groupBy('pi.proyecto_id');

    $deudas = DB::table('Proyecto AS a')
      ->leftJoinSub($investigadores, 'inv', function($join){
        $join->on('inv.proyecto_id', '=', 'a.id');})
      ->leftJoin('Proyecto_integrante AS b', function (JoinClause $join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.condicion', '=', 'Responsable');})
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->select([
        DB::raw("CONCAT('PROYECTO_BASE', '_', a.id) AS id"),
        DB::raw("'Nuevo' AS proyecto_origen"),
        'a.id AS proyecto_id',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.periodo',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS responsable"),
        'a.titulo',
        'c.nombre AS facultad',
        'inv.investigadores',
        DB::raw("CASE
          WHEN (a.deuda IS NULL OR a.deuda <= 0) THEN 'NO'
          WHEN a.deuda > 0 AND a.deuda <= 3 THEN 'SI'
          WHEN a.deuda > 3 THEN 'SUBSANADA'
        END as deuda"),
        'a.created_at',
        'a.updated_at'
      ])
      ->whereNotIn('a.tipo_proyecto', ['PFEX', 'FEX'])
      ->whereIn('a.estado', [1,8]);

      if ($investigadorId) {
        $deudas->whereExists(function ($query) use ($investigadorId) {
        $query->select(DB::raw(1))
          ->from('Proyecto_integrante as pi')
          ->whereColumn('pi.proyecto_id', 'a.id')
          ->where('pi.investigador_id', $investigadorId);
        });
      }
  
    // PROYECTOS ANTIGUOS
    $deudaAntiguos = DB::table('Proyecto_H AS a')
      ->whereNotNull('a.codigo')
      ->whereRaw("TRIM(a.codigo) <> ''")
      ->where('a.status', 1)
      ->leftJoin('Proyecto_integrante_H AS b', function ($join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->whereIn('b.condicion', ['Responsable', 'Asesor']);})
      ->leftJoin('Facultad AS c', 'c.id', '=', 'a.facultad_id')
      ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'b.investigador_id')
      ->leftJoinSub($estadoDeudaAntiguos, 'ed', function ($join) {
        $join->on('ed.proyecto_id', '=', 'a.id');})
      ->select([
        DB::raw("CONCAT('PROYECTO_H_', a.id) AS id"),
        DB::raw("'Antiguo' AS proyecto_origen"),
        'a.id AS proyecto_id',
        'a.codigo AS codigo_proyecto',
        'a.tipo AS tipo_proyecto',
        'a.periodo',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS responsable"),
        'a.titulo',
        'c.nombre AS facultad',
        DB::raw("JSON_ARRAY() AS investigadores"),
        DB::raw("CASE
              WHEN ed.estado_deuda = 1 THEN 'SI'
              WHEN ed.estado_deuda = 2 THEN 'SUBSANADA'
              ELSE 'NO'
          END AS deuda
        "),
        'a.created_at',
        'a.updated_at'
      ]);

    if ($investigadorId) {
      $deudaAntiguos->whereExists(function ($query) use ($investigadorId) {
        $query->select(DB::raw(1))
          ->from('Proyecto_integrante_H as pi')
          ->whereColumn('pi.proyecto_id', 'a.id')
          ->where('pi.investigador_id', $investigadorId);
      });
    }

    $deudas = $deudas
    ->unionAll($deudaAntiguos)
    ->orderBy('created_at', 'DESC')
    ->get();

    $deudas = $deudas->map(function ($proyecto) {
      $proyecto->investigadores = $proyecto->investigadores
        ? json_decode($proyecto->investigadores)
        : [];
      return $proyecto;
    });

    return $deudas;
  }

  public function listadoProyectosNoDeuda() {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.proyecto_id',
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2, ", ", b.nombres) AS responsable'))
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
      ->whereNotIn('a.tipo_proyecto', ['PFEX', 'FEX'])
      ->get();

    return $lista;
  }

  public function listadoDeudaAcademica(Request $request) {
    $opciones = [];
    $tipoProyecto = $request->query('tipo_proyecto');

    switch ($tipoProyecto) {
      case 'PCONFIGI':
      case 'PCONFIGI-INV':
      case 'PSINFINV':
      case 'PEVENTO':
      case 'PINVPOS':
      case 'PMULTI':
      case 'PINTERDIS':
        $opciones = [['value' => 'Informe académico'],];
        break;
      case 'PSINFIPU':
        $opciones = [['value' => 'Resultados de la publicación'],];
        break;
      case 'PTPGRADO':
      case 'PTPMAEST':
        $opciones = [['value' => 'Informe académico de avance'],['value' => 'Informe académico final'],];
        break;
      case 'PTPDOCTO':
        $opciones = [['value' => 'Informe académico de avance'],['value' => 'Segundo informe académico de avance'],['value' => 'Informe académico final'],];
        break;
      case 'PTPBACHILLER':
        $opciones = [['value' => 'Informe académico final'],];
        break;
      default:
        $opciones = [['value' => 'Informe académico'],];
        break;
    }

    return $opciones;
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

  public function getRolesAcademicos($tipoProyecto) {
    switch ($tipoProyecto) {

      case 'PCONFIGI':
        return [1, 2, 3];

      case 'PSINFINV':
        return [7, 8, 9];

      case 'PSINFIPU':
        return [13];

      case 'PTPGRADO':
        return [15];

      case 'PTPMAEST':
        return [17, 18];

      case 'PTPDOCTO':
        return [19, 20];

      case 'PEVENTO':
        return [21, 24, 26];

      case 'PINVPOS':
        return [28, 29];

      case 'ECI':
        return [30];

      case 'PCONFIGI-INV':
        return [36, 37, 38];

      case 'PMULTI':
        return [56, 57, 58];

      case 'PTPBACHILLER':
        return [66];

      case 'RFPLU':
        return [71];

      case 'PINTERDIS':
        return [74, 75, 76];

      case 'SPINOFF':
        return [83];

      case 'PRO-CTIE':
        return [86];

      case 'PICV':
        return [92];

      default:
        return [];
    }
  }

  private function aplicarDeudaAProyecto(int $proyectoId, string $tipoProyecto, array $form) 
  {
    $deudaEconomica = $form['deuda_economica']['value'] ?? "Sin deuda";
    $deudaAcademica = $form['deuda_academica']['value'] ?? "Sin deuda";
    $deudaFecha = $form['fecha_deuda'] ?? null;
    $deudaDetalle = $form['detalle_deuda'] ?? null;
    $deudaComentario = $form['comentario_deuda'] ?? null;

    // No se registran deudas
    if ($deudaEconomica == "Sin deuda" && $deudaAcademica == "Sin deuda") {
      return ['ok' => false, 'message' => 'warning', 'detail' => 'No ha escogido ningún tipo de deuda'];
    }

    $categoria = "";
    $tipoDeuda = 0;
    if ($deudaAcademica != "Sin deuda" && $deudaEconomica == "Sin deuda") {
      $tipoDeuda = 1;
      $categoria = 'Deuda Académica';
    } else if ($deudaAcademica == "Sin deuda" && $deudaEconomica != "Sin deuda") {
      $tipoDeuda = 2;
      $categoria = 'Deuda Económica';
    } else if ($deudaAcademica != "Sin deuda" && $deudaEconomica != "Sin deuda") {
      $tipoDeuda = 3;
      $categoria = 'Deuda Académica y Económica';
    }

    $tipoIntegrante = DB::table('Proyecto_integrante_tipo')
      ->where('tipo_proyecto', '=', $tipoProyecto)
      ->pluck('id');

    $integrantes = DB::table('Proyecto_integrante')
      ->where('proyecto_id', $proyectoId)
      ->whereIn('proyecto_integrante_tipo_id', $tipoIntegrante)
      ->get();

    if ($integrantes->count() == 0) {
      return ['ok' => false, 'message' => 'warning', 'detail' => 'No hay integrantes a los que se les pueda asignar deuda'];
    }

    DB::table('Proyecto')
      ->where('id', '=', $proyectoId)
      ->update([
        'deuda' => 1,
        'updated_at' => Carbon::now()
      ]);

    $rolesAcademicos = $this->getRolesAcademicos($tipoProyecto);
    $responsable = $this->getResponsableProyecto($tipoProyecto);

    foreach ($integrantes as $integrante) {
      $integranteDeuda = DB::table('Proyecto_integrante_deuda')
        ->where('proyecto_integrante_id', $integrante->id)
        ->first();

      if (!$integranteDeuda) {
        if ($tipoDeuda == 1) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 1,
              'categoria' => 'Deuda Académica',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          }
        } else if ($tipoDeuda == 2) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 2,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          }
        } else if ($tipoDeuda == 3) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 3,
              'categoria' => 'Deuda Académica y Económica',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          } else if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 1,
              'categoria' => 'Deuda Académica',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          }
        }

      } else {
        if ($tipoDeuda == 1) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => 1,
                'categoria' => $categoria,
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          }
        } else if ($tipoDeuda == 2) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => 2,
                'categoria' => $categoria,
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          } else {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->delete();
          }
        } else if ($tipoDeuda == 3) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => 3,
                'categoria' => 'Deuda Académica y Económica',
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          } else if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => 1,
                'categoria' => 'Deuda Académica',
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          }
        }
      }
    }

    return ['ok' => true];
  }

  public function asignarDeuda(Request $request) {
    $partes = explode('_', $request->input('proyecto_id'));
    $proyectoId = end($partes);
    $tipoProyecto = $request->input('tipo_proyecto');
    $deudaEconomica = $request->input('deuda_economica')['value'] ?? "Sin deuda";
    $deudaAcademica = $request->input('deuda_academica')['value'] ?? "Sin deuda";
    $deudaFecha = $request->input('fecha_deuda');
    $deudaDetalle = $request->input('detalle_deuda');
    $deudaComentario = $request->input('comentario_deuda');
    $tipoIntegrante = [];
    $categoria = "";
    $tipoDeuda = 0;

    $resultado = $this->aplicarDeudaAProyecto($proyectoId, $tipoProyecto, $request->all());
    if (!$resultado['ok']) {
      return ['message' => $resultado['message'], 'detail' => $resultado['detail']];
    }

    //  No se registran deudas
    if ($deudaEconomica == "Sin deuda" && $deudaAcademica == "Sin deuda") {
      return ['message' => 'warning', 'detail' => 'No ha escogido ningún tipo de deuda'];
    }

    if ($deudaAcademica != "Sin deuda" && $deudaEconomica == "Sin deuda") {
      $tipoDeuda = 1;
      $categoria = 'Deuda Académica';
    } else if ($deudaAcademica == "Sin deuda" && $deudaEconomica != "Sin deuda") {
      $tipoDeuda = 2;
      $categoria = 'Deuda Económica';
    } else if ($deudaAcademica != "Sin deuda" && $deudaEconomica != "Sin deuda") {
      $tipoDeuda = 3;
      $categoria = 'Deuda Académica y Económica';
    }

    $tipoIntegrante = DB::table('Proyecto_integrante_tipo')
      ->select([
        'id'
      ])
      ->where('tipo_proyecto', '=', $tipoProyecto)
      ->pluck('id');

    $integrantes = DB::table('Proyecto_integrante')
      ->where('proyecto_id', $proyectoId)
      ->whereIn('proyecto_integrante_tipo_id', $tipoIntegrante)
      ->get();

    if (sizeof($integrantes) == 0) {
      return ['message' => 'warning', 'detail' => 'No hay integrantes a los que se les pueda asignar deuda'];
    }

    DB::table('Proyecto')
      ->where('id', '=', $proyectoId)
      ->update([
        'deuda' => 1,
        'updated_at' => Carbon::now()
      ]);
    
    $rolesAcademicos = $this->getRolesAcademicos($tipoProyecto);
    $responsable = $this->getResponsableProyecto($tipoProyecto);

    foreach ($integrantes as $integrante) {

      $integranteDeuda = DB::table('Proyecto_integrante_deuda')
        ->where('proyecto_integrante_id', $integrante->id)
        ->first();

      /** NO existe el integrante con Deuda */
      if (!$integranteDeuda) {

        if ($tipoDeuda == 1) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 1,
              'categoria' => 'Deuda Académica',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          }
        } else if ($tipoDeuda == 2) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => $tipoDeuda,
              'categoria' => $categoria,
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          }
        } else if ($tipoDeuda == 3) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 3,
              'categoria' => 'Deuda Académica y Económica',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          } else if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')->insert([
              'proyecto_integrante_id' => $integrante->id,
              'tipo' => 1,
              'categoria' => 'Deuda Académica',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_deuda' => $deudaFecha,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
            ]);
          }
        }

        /** Existe el integrante con Deuda */
      } else {
        if ($tipoDeuda == 1) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
          DB::table('Proyecto_integrante_deuda')
            ->where('proyecto_integrante_id', $integrante->id)
            ->update([
              'tipo' => $tipoDeuda,
              'categoria' => $categoria,
              'informe' => $deudaDetalle ?: $integranteDeuda->informe,
              'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
              'fecha_deuda' => $deudaFecha,
              'updated_at' => Carbon::now()
            ]);
          }
        } else if ($tipoDeuda == 2) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => $tipoDeuda,
                'categoria' => $categoria,
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          } else {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->delete();
          }
        } else if ($tipoDeuda == 3) {
          if (in_array($integrante->proyecto_integrante_tipo_id, $responsable)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => $tipoDeuda,
                'categoria' => 'Deuda Académica y Económica',
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          } else if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->update([
                'tipo' => 1,
                'categoria' => 'Deuda Académica',
                'informe' => $deudaDetalle ?: $integranteDeuda->informe,
                'detalle' => $deudaComentario ?: $integranteDeuda->detalle,
                'fecha_deuda' => $deudaFecha,
                'updated_at' => Carbon::now()
              ]);
          }
        }
      }
    }
    return ['message' => 'success', 'detail' => 'Se asigno deuda a todos los miembros correspondientes exitosamente'];
  }

  public function asignarMasivo(Request $request)
  {
    // Validación mínima
    $request->validate([
      'ids' => 'required|array|min:1',
      'ids.*' => 'integer|exists:Proyecto,id',
    ]);

    $ids = $request->ids;

    $proyectos = DB::table('Proyecto')
        ->select('id', 'tipo_proyecto', 'periodo', 'deuda', 'estado')
        ->whereIn('id', $ids)
        ->where(function ($q) {
          $q->orWhere('deuda', '<', 1)
            ->orWhere('deuda', '=', 2)
            ->orWhere('deuda', '=', 8)
            ->orWhereNull('deuda');
        })
        ->where('estado', 1)
        ->whereNotIn('tipo_proyecto', ['PFEX', 'FEX'])
        ->get();
      if ($proyectos->isEmpty()) {
        return response()->json([
        'message' => 'warning',
        'detail' => 'No hay proyectos válidos para asignar deuda'
      ], 200);
    }

    $tipos = $proyectos->pluck('tipo_proyecto')->unique();
      if ($tipos->count() > 1) {
        return response()->json([
          'message' => 'error',
          'detail' => 'Los proyectos seleccionados tienen diferentes tipos de proyecto'
      ], 422);
    }

    $periodos = $proyectos->pluck('periodo')->unique();
      if ($periodos->count() > 1) {
        return response()->json([
        'message' => 'error',
        'detail' => 'Los proyectos seleccionados pertenecen a diferentes periodos'
      ], 422);
    }

    // ---- Aplicar filtros (PropertyFilter tokens) ----
    // Cloudscape manda: filtros.tokens = [{propertyKey, operator, value}, ...]
    DB::beginTransaction();
    $okCount = 0;
    $failCount = 0;

    foreach ($proyectos as $p) {
        $r = $this->aplicarDeudaAProyecto(
            (int)$p->id,
            $p->tipo_proyecto,
            $request->all()
        );

        if ($r['ok']) {
            $okCount++;
        } else {
            $failCount++;
        }
    }

    DB::commit();

      return response()->json([
          'message' => 'success',
          'detail' => "Asignación masiva completada. OK: {$okCount} | Fallidos: {$failCount}",
          'total' => $proyectos->count()
      ], 200);
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
        ->select('pind.*', 'pint.condicion')
        ->where('pint.proyecto_id', $proyectoId)
        ->first();
    }

    if (!$deuda) {
      return response()->json([
        'deuda' => null,
        'deuda_academica' => 'Sin deuda',
        'deuda_economica' => 'Sin deuda'
      ]);
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

  public function getResponsable($tipoProyecto, $proyectoId, $proyectoOrigen) {
    if ($proyectoOrigen == 'Nuevo') {

    $integrantes = DB::table('Proyecto_integrante as pint')
      ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_id', '=', 'pint.id')
      ->select('pint.proyecto_integrante_tipo_id', 'pind.tipo as tipo_deuda')
      ->where('pint.proyecto_id', $proyectoId)
      ->get();

    } else { // Antiguo

    $integrantes = DB::table('Proyecto_integrante_H as pint')
      ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_h_id', '=', 'pint.id')
      ->select('pint.condicion', 'pind.tipo as tipo_deuda')
      ->where('pint.proyecto_id', $proyectoId)
      ->get();
    }

    if ($integrantes->isEmpty()) {
      return 0;
    }

    foreach ($integrantes as $integrante) {
      if ($integrante->tipo_deuda) {
        return $integrante->tipo_deuda;
      }
    }

    return 0;
  }
  
  public function getTipoDeuda(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $tipoProyecto = $request->query('tipo_proyecto');
    $proyectoOrigen = $request->query('proyecto_origen');

    return $this->getResponsable($tipoProyecto, $proyectoId, $proyectoOrigen);
  }

  private function getIntegrantesConDeuda($proyectoId, $proyectoOrigen)
  {
    if ($proyectoOrigen == 'Nuevo') {
      return DB::table('Proyecto_integrante as pint')
        ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_id', '=', 'pint.id')
        ->select('pind.*', 'pint.proyecto_integrante_tipo_id')
        ->where('pint.proyecto_id', $proyectoId)
        ->get();
    } else {
      return DB::table('Proyecto_integrante_H as pint')
        ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_h_id', '=', 'pint.id')
        ->select('pind.*', 'pind.proyecto_integrante_h_id', 'pint.condicion')
        ->where('pint.proyecto_id', $proyectoId)
        ->get();
    }
  }

  private function getConfiguracionSubsanacion($proyectoOrigen)
  {
    return [
    'campoId' => $proyectoOrigen == 'Nuevo'
      ? 'proyecto_integrante_id'
      : 'proyecto_integrante_h_id',

    'categoriaAcademicaFija' => $proyectoOrigen == 'Antiguo'
      ? 'Deuda técnica subsanada'
      : null,

    'actualizaProyecto' => $proyectoOrigen == 'Nuevo'
    ];
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
    $tipoDeuda = 0;
    $resultados = [];
    $tipoDeuda = $this->getResponsable($tipoProyecto, $proyectoId, $proyectoOrigen);
    $responsable = $this->getResponsableProyecto($tipoProyecto);
    $rolesAcademicos = $this->getRolesAcademicos($tipoProyecto);

    if ($tipoDeuda === 0) {
      return response()->json([
        'message' => 'warning',
        'detail' => 'Este proyecto no tiene deuda registrada para subsanar'
      ], 400);
    }

    $integrantes = $this->getIntegrantesConDeuda($proyectoId, $proyectoOrigen);
    $config = $this->getConfiguracionSubsanacion($proyectoOrigen);
    $campoId = $config['campoId'];
    $categoria = null;

    // Académica
    if ($subsanarAcademica) {
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

      // Para proyectos antiguos
      if ($config['categoriaAcademicaFija']) {
        $categoria = $config['categoriaAcademicaFija'];
      }
    }

    // Económica (solo si aplica)
    if ($subsanarEconomica == 8) {
      $categoria = 'Deuda económica subsanada';
    }

    if ($proyectoOrigen == 'Nuevo') {

      foreach ($integrantes as $integrante) {

        /** Subsanar Deuda Academica */
        if ($tipoDeuda == 1) {

          if (in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos)) {
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
              ->where($campoId, $integrante->$campoId)
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
              ->where($campoId, $integrante->$campoId)
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
            ->where($campoId, $integrante->$campoId)
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
            ->where($campoId, $integrante->$campoId)
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
          if ($subsanarEconomica) {
            DB::table('Proyecto_integrante_deuda')
            ->where($campoId, $integrante->$campoId)
            ->update([
              'tipo' => $subsanarEconomica,
              'categoria' => 'Deuda económica subsanada',
              'informe' => $deudaDetalle,
              'detalle' => $deudaComentario,
              'fecha_sub' => $fechaSubsanar,
              'updated_at' => Carbon::now()
            ]);
          }
          if ($subsanarAcademica) {
            DB::table('Proyecto_integrante_deuda')
            ->where($campoId, $integrante->$campoId)
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
    if ($proyectoOrigen == 'Nuevo') {
      DB::table('Proyecto')
      ->where('id', $proyectoId)
      ->update([
        'deuda' => 5,
        'updated_at' => Carbon::now()
      ]);
    } else if ($proyectoOrigen == 'Antiguo') {
      DB::table('Proyecto_H')
      ->where('id', $proyectoId)
      ->update([
        'updated_at' => Carbon::now()
      ]);
    }

    if ($todosExitosos) {
      return response()->json(['message' => 'success', 'detail' => 'Se subsano deuda a  todos los miembros'], 200);
    } else {
      return response()->json(['message' => 'error', 'detail' => 'Hubo un problema con la subsanacion de algunos miembros'], 500);
    }
  }
}

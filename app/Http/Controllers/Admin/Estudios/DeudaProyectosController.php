<?php

namespace App\Http\Controllers\Admin\Estudios;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeudaProyectosController extends Controller {

  private function ordenJerarquicoIntegrantes($campo = 'nombre') {
    return "
      CASE
        WHEN (
          LOWER($campo) LIKE '%responsable%'
          AND LOWER($campo) NOT LIKE '%co%'
        )
          OR LOWER($campo) LIKE '%asesor%'
          OR LOWER($campo) LIKE '%autor corresponsal%'
        THEN 1
        WHEN LOWER($campo) LIKE '%co responsable%'
        THEN 2
        WHEN LOWER($campo) LIKE '%miembro docente%'
          OR LOWER($campo) LIKE '%docente%'
          OR LOWER($campo) LIKE '%co-autor%'
        THEN 3
        WHEN LOWER($campo) LIKE '%tesista%'
        THEN 4
        WHEN (
          LOWER($campo) LIKE '%colaborador%'
          OR LOWER($campo) LIKE '%estudiante%'
        ) 
          AND LOWER($campo) NOT LIKE '%externo%'
        THEN 5
        WHEN LOWER($campo) LIKE '%miembro externo%'
        THEN 6
        ELSE 99

      END
    ";
  }

  public function listadoIntegrantes(Request $request) {
    if ($request->query('tabla') == "Nuevo") {
      $integrantes = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
        ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
        ->leftJoin('Licencia AS d', function ($join) {
          $join->on('d.investigador_id', '=', 'c.id')
          ->whereRaw('d.id = (
            SELECT l2.id
            FROM Licencia AS l2
            WHERE l2.investigador_id = c.id
              AND DATE(l2.fecha_fin) >= CURDATE()
            ORDER BY
              CASE
                WHEN l2.licencia_tipo_id = 7 THEN 1
                WHEN l2.licencia_tipo_id = 6 THEN 2
                WHEN l2.licencia_tipo_id = 4 THEN 3
                ELSE 4
              END,
              l2.fecha_fin DESC,
              l2.id DESC
            LIMIT 1
          )');
        })
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
        ->orderByRaw($this->ordenJerarquicoIntegrantes('b.nombre'))
        ->orderBy('c.apellido1')
        ->get();
      return $integrantes;

    } else {
      $integrantes = DB::table('Proyecto_integrante_H AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->leftJoin('Licencia AS c', function ($join) {
          $join->on('c.investigador_id', '=', 'b.id')
          ->whereRaw('c.id = (
            SELECT l2.id
            FROM Licencia AS l2
            WHERE l2.investigador_id = b.id
              AND DATE(l2.fecha_fin) >= CURDATE()
            ORDER BY
              CASE
                WHEN l2.licencia_tipo_id = 7 THEN 1
                WHEN l2.licencia_tipo_id = 6 THEN 2
                WHEN l2.licencia_tipo_id = 4 THEN 3
                ELSE 4
              END,
              l2.fecha_fin DESC,
              l2.id DESC
            LIMIT 1
          )');
        })
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
        ->orderByRaw($this->ordenJerarquicoIntegrantes('a.condicion'))
        ->orderBy('b.apellido1')
        ->get();

      return $integrantes;
    }
  }

  public function listadoProyectos(Request $request) {
    $investigadorId = $request->query('investigador_id');

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
      ->whereIn('a.estado', [1,8])
      ->whereNotIn('a.tipo_proyecto', ['PFEX', 'FEX'])
      ->get();

    return $lista;
  }

  private function estadoDeuda($tipo = null) {
    $estados = [
      # Deudas
      1 => 'Deuda académica',
      2 => 'Deuda económica',
      3 => 'Deuda académica y económica',
      
      #Subsanaciones
      4 => 'Deuda académica subsanada',
      5 => 'Presentó informe académico de avance',
      6 => 'Presentó informe técnico en extenso',
      7 => 'Presentó informe académico final',
      8 => 'Deuda económica subsanada',
      9 => 'Presentó informe técnico final',
    ];

    if ($tipo !== null) {
      return $estados[$tipo] ?? 'Estado desconocido';
    }

    return $estados;
  }

  private function opcionesSubsanacionAcademica($tipoProyecto, $periodo = null, $proyectoOrigen = 'Nuevo') {
    $tiposTesisNuevo = ['PTPBACHILLER', 'PTPDOCTO', 'PTPMAEST', 'PTPGRADO'];

    if ($proyectoOrigen == 'Antiguo') {
      if ($tipoProyecto == 'Tesis') {
        return [
          ['label' => $this->estadoDeuda(9), 'value' => 9],
        ];
      }

      return [
        ['label' => $this->estadoDeuda(6), 'value' => 6],
      ];
    }

    if (in_array($tipoProyecto, $tiposTesisNuevo)) {
      return [
        ['label' => $this->estadoDeuda(5), 'value' => 5],
        ['label' => $this->estadoDeuda(7), 'value' => 7],
      ];
    }

    if ((int) $periodo >= 2017) {
      return [
        ['label' => $this->estadoDeuda(4), 'value' => 4],
      ];
    }

    return [];
  }

  public function listadoSubsanacionAcademica(Request $request) {
    return $this->opcionesSubsanacionAcademica(
      $request->query('tipo_proyecto'),
      $request->query('periodo'),
      $request->query('proyecto_origen')
    );
  }

  private function configuracionRolesProyecto($tipoProyecto) {
    $config = [
      'PCONFIGI' => [
        'responsable' => [1],
        'academicos' => [1, 2, 3],
      ],
      'PCONFIGI-INV' => [
        'responsable' => [36],
        'academicos' => [36, 37, 38],
      ],
      'PSINFINV' => [
        'responsable' => [7],
        'academicos' => [7, 8, 9],
      ],
      'PSINFIPU' => [
        'responsable' => [13],
        'academicos' => [13],
      ],
      'PTPGRADO' => [
        'responsable' => [15],
        'academicos' => [15],
      ],
      'PTPMAEST' => [
        'responsable' => [17],
        'academicos' => [17, 18],
      ],
      'PTPDOCTO' => [
        'responsable' => [19],
        'academicos' => [19, 20],
      ],
      'PEVENTO' => [
        'responsable' => [21],
        'academicos' => [21, 24, 26],
      ],
      'PINVPOS' => [
        'responsable' => [28],
        'academicos' => [28, 29],
      ],
      'PTPBACHILLER' => [
        'responsable' => [66],
        'academicos' => [66],
      ],
      'PMULTI' => [
        'responsable' => [56],
        'academicos' => [56, 57, 58],
      ],
      'PINTERDIS' => [
        'responsable' => [74],
        'academicos' => [74, 75, 76],
      ],
      'PRO-CTIE' => [
        'responsable' => [86],
        'academicos' => [86],
      ],
      'ECI' => [
        'responsable' => [30],
        'academicos' => [30],
      ],
      'RFPLU' => [
        'responsable' => [70],
        'academicos' => [70, 71],
      ],
      'SPINOFF' => [
        'responsable' => [83],
        'academicos' => [83],
      ],
      'PICV' => [
        'responsable' => [92],
        'academicos' => [92],
      ],
    ];

    return $config[$tipoProyecto] ?? [
      'responsable' => [],
      'academicos' => [],
    ];
  }

  public function getResponsableProyecto($tipoProyecto) {
    return $this->configuracionRolesProyecto($tipoProyecto)['responsable'];
  }

  public function getRolesAcademicos($tipoProyecto, $investigador = null) {
    $roles = $this->configuracionRolesProyecto($tipoProyecto)['academicos'];

    if ($tipoProyecto === 'PSINFIPU' && $investigador &&
      strtolower(trim($investigador->tipo ?? '')) === 'docente permanente'
    ) {
      $roles[] = 14;
    }

    return array_unique($roles);
  }

  private function aplicarDeudaAProyecto(int $proyectoId, string $tipoProyecto, array $form) {
    $deudaEconomica = $form['deuda_economica']['value'] ?? "Sin deuda";
    $deudaAcademica = $form['deuda_academica']['value'] ?? "Sin deuda";
    $deudaFecha = $form['fecha_deuda'] ?? null;
    $deudaDetalle = $form['detalle_deuda'] ?? null;
    $deudaComentario = $form['comentario_deuda'] ?? null;

    // No se registran deudas
    if ($deudaEconomica == "Sin deuda" && $deudaAcademica == "Sin deuda") {
      return ['ok' => false, 'message' => 'warning', 'detail' => 'No ha escogido ningún tipo de deuda'];
    }

    $tipoDeuda = 0;
    if ($deudaAcademica != "Sin deuda" && $deudaEconomica == "Sin deuda") {
      $tipoDeuda = 1;
    } else if ($deudaAcademica == "Sin deuda" && $deudaEconomica != "Sin deuda") {
      $tipoDeuda = 2;
    } else if ($deudaAcademica != "Sin deuda" && $deudaEconomica != "Sin deuda") {
      $tipoDeuda = 3;
    }

    $tipoIntegrante = DB::table('Proyecto_integrante_tipo')
      ->where('tipo_proyecto', '=', $tipoProyecto)
      ->pluck('id')
      ->toArray();

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.id',
        'a.proyecto_integrante_tipo_id',
        'a.investigador_id',
        'b.tipo'
      )
      ->where('a.proyecto_id', '=', $proyectoId)
      ->whereIn('a.proyecto_integrante_tipo_id', $tipoIntegrante)
      ->get();

    if ($integrantes->count() == 0) {
      return ['ok' => false, 'message' => 'warning', 'detail' => 'No hay integrantes a los que se les pueda asignar deuda'];
    }

    DB::table('Proyecto')
      ->where('id', '=', $proyectoId)
      ->update([
        'deuda' => $tipoDeuda,
        'updated_at' => Carbon::now()
      ]);
    
    $responsable = $this->getResponsableProyecto($tipoProyecto);

    foreach ($integrantes as $integrante) {
      $rolesAcademicos = $this->getRolesAcademicos($tipoProyecto, $integrante);
      
      $integranteDeuda = DB::table('Proyecto_integrante_deuda')
        ->where('proyecto_integrante_id', $integrante->id)
        ->first();
      
      $esResponsable = in_array($integrante->proyecto_integrante_tipo_id, $responsable);
      $esAcademico = in_array($integrante->proyecto_integrante_tipo_id, $rolesAcademicos);
      $data = null;

      switch ($tipoDeuda) {
        case 1:
          if ($esAcademico) {
            $data = ['tipo' => 1, 'categoria' => 'Deuda Académica',];
          }
          break;

        case 2:
          if ($esResponsable) {
            $data = ['tipo' => 2, 'categoria' => 'Deuda Económica',];
          } else if ($integranteDeuda) {
            DB::table('Proyecto_integrante_deuda')
              ->where('proyecto_integrante_id', $integrante->id)
              ->delete();
          }
          break;

        case 3:
          if ($esResponsable) {
            $data = ['tipo' => 3, 'categoria' => 'Deuda Académica y Económica',];
          } else if ($esAcademico) {
            $data = ['tipo' => 1, 'categoria' => 'Deuda Académica',];
          }
          break;
      }

      if (!$data) {
        continue;
      }

      $data = array_merge($data, [
        'informe' => $integranteDeuda ? ($deudaDetalle ?: $integranteDeuda->informe) : $deudaDetalle,
        'detalle' => $integranteDeuda ? ($deudaComentario ?: $integranteDeuda->detalle) : $deudaComentario,
        'fecha_deuda' => $deudaFecha,
        'updated_at' => Carbon::now(),
      ]);

      if ($integranteDeuda) {
        DB::table('Proyecto_integrante_deuda')
          ->where('proyecto_integrante_id', $integrante->id)
          ->update($data);
      } else {
        $data['proyecto_integrante_id'] = $integrante->id;
        $data['created_at'] = Carbon::now();

        DB::table('Proyecto_integrante_deuda')->insert($data);
      }
    }

    return ['ok' => true];
  }

  public function asignarDeuda(Request $request) {
    $partes = explode('_', $request->input('proyecto_id'));
    $proyectoId = end($partes);
    $tipoProyecto = $request->input('tipo_proyecto');

    $resultado = $this->aplicarDeudaAProyecto(
      (int) $proyectoId,
      $tipoProyecto,
      $request->all()
    );

    if (!$resultado['ok']) {
      return [
        'message' => $resultado['message'],
        'detail' => $resultado['detail']
      ];
    }

    return ['message' => 'success', 'detail' => 'Se asignó deuda a todos los miembros correspondientes exitosamente'];
  }

  public function proyectoDeuda(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $proyectoOrigen = $request->query('proyecto_origen');
    $tipoProyecto = $request->query('tipo_proyecto');
    $deudaAcademica = "";
    $deudaEconomica = "";
    $deuda = null;

    $tipoIntegrante = $this->getResponsableProyecto($tipoProyecto);
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

  public function getTipoDeudaProyecto($tipoProyecto, $proyectoId, $proyectoOrigen) {
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

    return $integrantes->first()->tipo_deuda ?? 0;
  }
  
  public function getTipoDeuda(Request $request) {
    $proyectoId = $request->query('proyecto_id');
    $tipoProyecto = $request->query('tipo_proyecto');
    $proyectoOrigen = $request->query('proyecto_origen');

    return $this->getTipoDeudaProyecto($tipoProyecto, $proyectoId, $proyectoOrigen);
  }

  private function getIntegrantesConDeuda($proyectoId, $proyectoOrigen) {
    if ($proyectoOrigen == 'Nuevo') {
      return DB::table('Proyecto_integrante as pint')
        ->join('Proyecto_integrante_deuda as pind', 'pind.proyecto_integrante_id', '=', 'pint.id')
        ->join('Usuario_investigador as ui', 'ui.id', '=', 'pint.investigador_id')
        ->select(
          'pind.*',
          'pint.proyecto_integrante_tipo_id',
          'ui.tipo'
        )
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

  private function getConfiguracionSubsanacion($proyectoOrigen) {
    return [
    'campoId' => $proyectoOrigen == 'Nuevo'
      ? 'proyecto_integrante_id'
      : 'proyecto_integrante_h_id',

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
    $tipoDeuda = $this->getTipoDeudaProyecto($tipoProyecto, $proyectoId, $proyectoOrigen);
    $responsable = $this->getResponsableProyecto($tipoProyecto);

    if ($tipoDeuda === 0) {
      return ['message' => 'warning', 'detail' => 'Este proyecto no tiene deuda registrada para subsanar'];
    }

    $integrantes = $this->getIntegrantesConDeuda($proyectoId, $proyectoOrigen);
    $config = $this->getConfiguracionSubsanacion($proyectoOrigen);
    $campoId = $config['campoId'];
    $categoria = null;

    // Académica
    if ($subsanarAcademica) {
      $categoria = $this->estadoDeuda($subsanarAcademica);
    }

    // Económica (solo si aplica)
    if ($subsanarEconomica == 8) {
      $categoria = $this->estadoDeuda(8);
    }

    if ($proyectoOrigen == 'Nuevo') {

      foreach ($integrantes as $integrante) {
        $rolesAcademicos = $this->getRolesAcademicos($tipoProyecto, $integrante);

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
                $categoria = $this->estadoDeuda(8);
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

          $categoria = $this->estadoDeuda($subsanarAcademica);

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
              'categoria' => $this->estadoDeuda(8),
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
    $nuevoEstadoDeuda = $subsanarAcademica ?: $subsanarEconomica;

    if ($proyectoOrigen == 'Nuevo') {
      DB::table('Proyecto')
        ->where('id', $proyectoId)
        ->update([
          'deuda' => $nuevoEstadoDeuda,
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
      return ['message' => 'success', 'detail' => 'Se subsanó la deuda de todos los miembros'];
    } else {
      return ['message' => 'error', 'detail' => 'Hubo un problema con la subsanación de algunos miembros'];
    }
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use App\Mail\Admin\Estudios\DocenteInvestigador\ConstanciaCdi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DocenteInvestigadorController extends S3Controller {

  public function listado() {

    $evalSubQuery = DB::table('Eval_declaracion_jurada AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'DECLARACION_JURADA');
      })
      ->select([
        'b.key',
        'a.investigador_id'
      ])
      ->orderByDesc('a.fecha_inicio')
      ->groupBy('a.id');

    // Define la consulta principal
    $evaluaciones = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->joinSub($evalSubQuery, 'c', 'c.investigador_id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        'a.estado',
        'a.tipo_eval',
        DB::raw("CONCAT('/minio/declaracion-jurada/', c.key) AS url"),
        'b.tipo',
        'd.nombre AS facultad',
        'b.codigo_orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_tipo',
        'a.doc_numero',
        'b.telefono_movil',
        'b.email3',
        'a.created_at'
      ])
      ->where('a.tipo_eval', '=', 'Solicitud')
      ->whereIn('a.estado', ['Enviado', 'En trámite', 'No aprobado', 'Observado'])
      ->groupBy('a.id')
      ->get();

    return $evaluaciones;
  }

  public function constancias() {
    $constancias = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        'a.estado',
        DB::raw("DATE(a.fecha_constancia) AS fecha_constancia"),
        DB::raw("DATE(a.fecha_fin) AS fecha_fin"),
        'b.tipo',
        'c.nombre AS facultad',
        'b.codigo_orcid',
        'b.apellido1',
        'b.apellido2',
        'b.nombres',
        'b.doc_tipo',
        'a.doc_numero',
        'b.telefono_movil',
        'b.email3'
      ])
      ->where('a.tipo_eval', '=', 'Constancia')
      ->orderByDesc('a.fecha_fin')
      ->get();

    return $constancias;
  }

  public function evaluarData(Request $request) {
    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $detalles = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Repo_rrhh AS c', 'c.ser_cod_ant', '=', 'b.codigo')
      ->leftJoin('File AS d', function (JoinClause $join) {
        $join->on('d.tabla_id', '=', 'a.id')
          ->where('d.tabla', '=', 'Eval_docente_investigador')
          ->where('d.recurso', '=', 'CONSTANCIA_FIRMADA');
      })
      ->select([
        DB::raw("CONCAT('/minio/', d.bucket, '/', d.key) AS url"),
        'a.nombres',
        'a.estado',
        'a.doc_numero',
        'c.ser_fech_in_unmsm AS fecha',
        'b.id AS investigador_id',
        'b.email3',
        'a.cti_vitae',
        'a.renacyt',
        'a.renacyt_nivel',
        'a.orcid',
        'b.scopus_id',
        'a.google_scholar',
        'a.created_at',
        'a.tipo_docente',
        'a.docente_categoria',
        'a.clase',
        'a.horas',
        'a.confirmar',
        'a.d1',
        'a.d2',
        'a.d3',
        'a.d4',
        'a.d6',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $grupo = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.grupo_id')
          ->where('b.tipo', '=', 'grupo');
      })
      ->select([
        'b.grupo_nombre',
        'b.grupo_nombre_corto',
        'a.condicion',
      ])
      ->where('a.investigador_id', '=', $detalles->investigador_id)
      ->where('b.id', '=', $detalles->d2)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $d3 = json_decode($detalles->d3, true);

    $d3Extra = DB::table('Actividad_investigador AS a')
      ->select([
        'a.id',
        'b.tipo AS categoria',
        'a.periodo'
      ])
      ->join('Eval_docente_actividad AS b', 'b.id', '=', 'a.eval_docente_actividad_id')
      ->where('b.eval_docente_investigador_id', '=', $request->query('id'))
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    $d3Complete = [];
    foreach ($d3 as $element) {
      $d3Complete[] = $element;
    }

    foreach ($d3Extra as $element) {
      $d3Complete[] = [
        'id' => $element->id,
        'categoria' => $element->categoria,
        'periodo' => $element->periodo,
      ];
    }

    // Inicializar un array para verificar la presencia de los años
    $yearsFound = array_fill_keys($lastTwoYears, false);

    // Recorrer el array y marcar los años encontrados
    foreach ($d3Complete as $element) {
      if (in_array((int)$element["periodo"], $lastTwoYears)) {
        $yearsFound[(int)$element["periodo"]] = true;
      }
    }

    // Verificar si se encontraron los dos años
    $allYearsFound = !in_array(false, $yearsFound);

    $d4 = DB::table('Publicacion AS a')
      ->join('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_index AS c', 'c.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_db_indexada AS d', 'd.id', '=', 'c.publicacion_db_indexada_id')
      ->select([
        'a.titulo',
        DB::raw("CASE (a.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE tipo_publicacion END AS tipo_publicacion"),
        DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
        'a.codigo_registro',
        DB::raw("GROUP_CONCAT(d.nombre SEPARATOR ', ') AS indexada"),
        DB::raw("CASE(b.filiacion)
          WHEN 1 THEN 'Sí'
          WHEN 0 THEN 'No'
          ELSE '-'
        END AS filiacion"),
        DB::raw("CASE(b.filiacion_unica)
          WHEN 1 THEN 'Sí'
          WHEN 0 THEN 'No'
          ELSE '-'
        END AS filiacion_unica"),
      ])
      ->where('b.investigador_id', '=', $detalles->investigador_id)
      ->where('a.estado', '=', 1)
      ->whereIn(DB::raw("YEAR(a.fecha_publicacion)"), $lastTwoYears)
      ->whereNotIn('a.tipo_publicacion', ['tesis-asesoria', 'tesis'])
      ->groupBy('a.id')
      ->get();

    $filiacion = 0;
    foreach ($d4 as $item) {
      if ($item->filiacion == "Sí") {
        $filiacion++;
      }
    }

    $d5 = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->select([
        'c.titulo',
        'c.periodo',
        'c.tipo_proyecto',
        'b.id AS id_deuda',
        'b.detalle'
      ])
      ->where('a.investigador_id', '=', $detalles->investigador_id)
      ->whereIn('b.tipo', [1, 2, 3])
      ->whereNull('b.fecha_sub')
      ->get();

    $d6 = json_decode($detalles->d6, true);
    $d6_valid = $d6["fecha_fin"] > Carbon::now() ? true : false;

    $req6 = DB::table('Eval_declaracion_jurada AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'DECLARACION_JURADA');
      })
      ->select([
        DB::raw("CONCAT('/minio/declaracion-jurada/', b.key) AS url"),
        'a.fecha_inicio',
        'a.fecha_fin'
      ])
      ->where('a.investigador_id', '=', $detalles->investigador_id)
      ->orderByDesc('b.created_at')
      ->limit(1)
      ->get();

    $actExtra = $this->actividadesExtra($detalles->investigador_id, $request->query('id'));

    return [
      'detalles' => $detalles,
      'd1' => [
        'cumple' => $detalles->d1 != "",
        'renacyt' => $detalles->d1
      ],
      'd2' => [
        'cumple' => $grupo,
        'grupo_nombre' => $grupo?->grupo_nombre,
        'condicion' => $grupo?->condicion,
      ],
      'd3' => [
        'cumple' => $allYearsFound,
        'lista' => $d3Complete
      ],
      'd4' => [
        'cumple' => $filiacion,
        'lista' => $d4
      ],
      'd5' => [
        'cumple' => $d5 == null || sizeof($d5) == 0 ? true : false,
        'lista' => $d5
      ],
      'd6' => [
        'cumple' => $d6_valid,
        'lista' => $req6
      ],
      'actividades' => $actExtra
    ];
  }

  public function actividadesExtra($investigador_id, $eval_id) {
    $actividades = DB::table('Eval_docente_actividad AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Eval_docente_actividad');
      })
      ->leftJoin('Actividad_investigador AS c', function (JoinClause $join) {
        $join->on('c.eval_docente_actividad_id', '=', 'a.id');
      })
      ->select([
        'a.id',
        'a.investigador_id',
        'a.tipo',
        'c.id AS registrado',
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('a.eval_docente_investigador_id', '=', $eval_id)
      ->get();

    return $actividades;
  }

  public function opcionesSubCategorias() {
    $opciones = DB::table('Eval_docente_actividad_tipo')
      ->select([
        'categoria AS value',
        DB::raw("CONCAT(codigo, '. ', descripcion) AS label"),
        DB::raw("CASE
          WHEN nivel = 0 THEN true
          ELSE false
        END AS disabled")
      ])
      ->get();

    return $opciones;
  }

  public function aprobarActividad(Request $request) {
    $inves = DB::table('Usuario_investigador')
      ->select([
        DB::raw("CONCAT(apellido1, ' ', apellido2, ', ', nombres) AS nombre"),
        'doc_numero'
      ])
      ->where('id', '=', $request->input('investigador_id'))
      ->first();

    DB::table('Actividad_investigador')
      ->insert([
        'eval_docente_actividad_id' => $request->input('id'),
        'nombre' => $inves->nombre,
        'dni' => $inves->doc_numero,
        'categoria' => $request->input('categoria')["value"],
        'periodo' => $request->input('periodo'),
        'tipo' => $request->input('tipo'),
        'revista' => $request->input('revista'),
        'rol' => $request->input('rol'),
        'condicion' => $request->input('condicion'),
        'fecha' => $request->input('fecha'),
        'autor' => $request->input('autor'),
        'estado' => $request->input('estado'),
        'titulo' => $request->input('titulo'),
        'url' => $request->input('url'),
        'lugar_act' => $request->input('lugar_act'),
        'tipo_transf' => $request->input('tipo_transf'),
        'aplicacion' => $request->input('aplicacion'),
        'beneficiario' => $request->input('beneficiario'),
        'num_documento' => $request->input('num_documento'),
      ]);
  }

  //  Observar
  public function observar(Request $request) {
    $count = DB::table('Eval_docente_investigador')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 'Observado')
      ->count();

    if ($count == 1) {
      return ['message' => 'warning', 'detail' => 'Esta solicitud ya figura como observada, no puede añadir observaciones mientras el docente no subsane la última observación ingresada'];
    } else {
      $date = Carbon::now();

      DB::table('Eval_docente_investigador_obs')
        ->insert([
          'eval_investigador_id' => $request->input('id'),
          'observacion' => $request->input('observacion'),
          'created_at' => $date,
          'updated_at' => $date
        ]);

      DB::table('Eval_docente_investigador')
        ->where('id', '=', $request->input('id'))
        ->update([
          'estado' => 'Observado',
          'updated_at' => $date
        ]);
      return ['message' => 'info', 'detail' => 'Solicitud observada correctamente'];
    }
  }

  public function observaciones(Request $request) {
    $observaciones = DB::table('Eval_docente_investigador_obs')
      ->select([
        'observacion',
        'created_at'
      ])
      ->where('eval_investigador_id', '=', $request->query('id'))
      ->get();

    return $observaciones;
  }

  //  Iniciar evaluación
  public function evaluar(Request $request) {
    DB::table('Eval_docente_investigador')
      ->where('id', '=', $request->input('id'))
      ->update([
        'tipo_investigador' => 'DOCENTE INVESTIGADOR',
        'fecha_tramite' => Carbon::now(),
        'estado' => $request->input('estado_tecnico')["value"],
        'updated_at' => Carbon::now()
      ]);
  }

  //  Trámite
  public function tramite(Request $request) {
    $date = Carbon::now();
    $dateFin = Carbon::now()->addYears(2);

    if ($request->input('confirmar')["value"] == 1) {
      DB::table('Eval_docente_investigador')
        ->where('id', '=', $request->input('id'))
        ->update([
          'tipo_eval' => 'Constancia',
          'fecha_constancia' => $date,
          'fecha_fin' => $dateFin,
          'estado' => 'Pendiente',
          'directiva_evaluacion' => $request->input('norma')["value"],
          'confirmar' => $request->input('confirmar')["value"],
          'confirmar_descripcion' => $request->input('descripcion'),
          'updated_at' => $date
        ]);
    } else if ($request->input('confirmar')["value"] == 0) {
      DB::table('Eval_docente_investigador')
        ->where('id', '=', $request->input('id'))
        ->update([
          'estado' => 'No aprobado',
          'confirmar' => $request->input('confirmar')["value"],
          'confirmar_descripcion' => $request->input('descripcion'),
          'updated_at' => $date
        ]);
    }
  }

  //  Subir constancia y aprobar
  public function subirCDI(Request $request) {
    if ($request->hasFile('file')) {

      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "constancia-firmada", $name);

      DB::table('File')
        ->insert([
          'tabla' => 'Eval_docente_investigador',
          'tabla_id' => $request->input('id'),
          'bucket' => 'constancia-firmada',
          'key' => $name,
          'recurso' => 'CONSTANCIA_FIRMADA',
          'estado' => 20,
          'created_at' => $date,
          'updated_at' => $date
        ]);

      DB::table('Eval_docente_investigador')
        ->where('id', '=', $request->input('id'))
        ->update([
          'estado' => 'Vigente',
          'updated_at' => $date
        ]);
      return ['message' => 'success', 'detail' => 'Constancia cargada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function fichaEvaluacion(Request $request) {
    $currentYear = (int)date("Y");
    $lastTwoYears = [$currentYear - 2, $currentYear - 1];

    $detalles = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Repo_rrhh AS c', 'c.ser_cod_ant', '=', 'b.codigo')
      ->leftJoin('Grupo AS d', 'd.id', '=', 'a.d2')
      ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select([
        'b.id AS investigador_id',
        'a.nombres',
        'c.ser_cod_ant',
        'd.grupo_nombre_corto',
        'a.cti_vitae',
        'a.google_scholar',
        'a.orcid',
        'e.nombre AS facultad',
        'c.des_dep_cesantes',
        'a.confirmar',
        'a.d1',
        'a.d2',
        'a.d3',
        'a.d4',
        'a.d6',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $grupo = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', function (JoinClause $join) {
        $join->on('b.id', '=', 'a.grupo_id')
          ->where('b.tipo', '=', 'grupo');
      })
      ->select([
        'b.grupo_nombre',
        'b.grupo_nombre_corto',
        'a.condicion',
      ])
      ->where('a.investigador_id', '=', $detalles->investigador_id)
      ->where('b.id', '=', $detalles->d2)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $d3 = json_decode($detalles->d3, true);

    $d3Extra = DB::table('Actividad_investigador AS a')
      ->select([
        'a.id',
        'b.tipo AS categoria',
        'a.periodo'
      ])
      ->join('Eval_docente_actividad AS b', 'b.id', '=', 'a.eval_docente_actividad_id')
      ->where('b.eval_docente_investigador_id', '=', $request->query('id'))
      ->whereIn('a.periodo', $lastTwoYears)
      ->get();

    $d3Complete = [];
    foreach ($d3 as $element) {
      $d3Complete[] = $element;
    }

    foreach ($d3Extra as $element) {
      $d3Complete[] = [
        'id' => $element->id,
        'categoria' => $element->categoria,
        'periodo' => $element->periodo,
      ];
    }

    // Inicializar un array para verificar la presencia de los años
    $yearsFound = array_fill_keys($lastTwoYears, false);

    // Recorrer el array y marcar los años encontrados
    foreach ($d3Complete as $element) {
      if (in_array((int)$element["periodo"], $lastTwoYears)) {
        $yearsFound[(int)$element["periodo"]] = true;
      }
    }

    // Verificar si se encontraron los dos años
    $allYearsFound = !in_array(false, $yearsFound);

    $d4 = json_decode($detalles->d4, true);
    $filiacion = 0;
    foreach ($d4 as $item) {
      if ($item["filiacion"] == 1) {
        $filiacion++;
      }
    }

    $d5 = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_deuda AS b', 'b.proyecto_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'a.proyecto_id')
      ->select([
        'c.titulo',
        'c.periodo',
        'c.tipo_proyecto',
        'b.id AS id_deuda',
        'b.detalle'
      ])
      ->where('a.investigador_id', '=', $detalles->investigador_id)
      ->whereIn('b.tipo', [1, 2, 3])
      ->whereNull('b.fecha_sub')
      ->get();

    $d6 = json_decode($detalles->d6, true);
    $d6_valid = $d6["fecha_fin"] > Carbon::now() ? true : false;

    $pdf = Pdf::loadView('admin.estudios.docentes.ficha_evaluacion', [
      'detalles' => $detalles,
      'd1' => [
        'cumple' => $detalles->d1 != "",
      ],
      'd2' => [
        'cumple' => $grupo->grupo_nombre != null,
      ],
      'd3' => [
        'cumple' => $allYearsFound,
      ],
      'd4' => [
        'cumple' => $filiacion,
      ],
      'd5' => [
        'cumple' => $d5 == null || sizeof($d5) == 0 ? true : false,
      ],
      'd6' => [
        'cumple' => $d6_valid,
      ],
    ]);
    return $pdf->stream();
  }

  public function constanciaCDI(Request $request) {
    $detalles = DB::table('Eval_docente_investigador AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Repo_rrhh AS c', 'c.ser_cod_ant', '=', 'b.codigo')
      ->select([
        DB::raw("CONCAT(c.ser_ape_pat, ' ', c.ser_ape_mat, ', ', c.ser_nom) AS nombres"),
        'c.ser_cod_ant',
        DB::raw("DATE(a.fecha_constancia) AS fecha_constancia"),
        DB::raw("DATE(a.fecha_fin) AS fecha_fin"),
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $pdf = Pdf::loadView('admin.estudios.docentes.constancia_no_firmada', ['detalles' => $detalles]);
    return $pdf->stream();
  }

  public function constanciaCDIFirmada(Request $request) {
    $constancia = DB::table('Eval_docente_investigador AS a')
      ->join('File AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.tabla_id')
          ->where('b.tabla', '=', 'Eval_docente_investigador')
          ->where('b.recurso', '=', 'CONSTANCIA_FIRMADA');
      })
      ->select([
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $constancia;
  }

  // public function enviarCorreo(Request $request) {
  //   //  Generar constancia
  //   $detalles = DB::table('Eval_docente_investigador AS a')
  //     ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
  //     ->join('Repo_rrhh AS c', 'c.ser_cod_ant', '=', 'b.codigo')
  //     ->join('Grupo AS d', 'd.id', '=', 'a.d2')
  //     ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
  //     ->select([
  //       DB::raw("CONCAT(c.ser_ape_pat, ' ', c.ser_ape_mat, ', ', c.ser_nom) AS nombres"),
  //       'c.ser_cod_ant',
  //       'a.fecha_constancia',
  //       'a.fecha_fin',
  //     ])
  //     ->where('a.id', '=', $request->input('id'))
  //     ->first();

  //   $pdf = Pdf::loadView('admin.estudios.docentes.constancia_no_firmada', ['detalles' => $detalles]);
  //   $adjunto = $pdf->output();

  //   // Mail::to('alefran2020@gmail.com')->send(new ConstanciaCdi($adjunto));

  //   return ['message' => 'info', 'detail' => 'Correo enviado exitosamente'];
  // }

  public function enviarCdiCorreo(Request $request) {
    $constancia = DB::table('File AS a')
      ->join('Eval_docente_investigador AS b', 'b.id', '=', 'a.tabla_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select([
        'a.bucket',
        'a.key',
        'b.nombres',
        'c.email3 AS email'
      ])
      ->where('a.tabla', '=', 'Eval_docente_investigador')
      ->where('a.tabla_id', '=', $request->query('id'))
      ->where('a.recurso', '=', 'CONSTANCIA_FIRMADA')
      ->where('a.estado', '=', 20)
      ->first();

    $file = $this->getFile($constancia->bucket, $constancia->key);

    Mail::to($constancia->email)->send(new ConstanciaCdi($constancia->nombres, $file));

    return ['message' => 'info', 'detail' => 'Correo enviado exitosamente'];
  }
}

<?php

namespace App\Http\Controllers\Investigador\Convocatorias;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProCTIController extends S3Controller {
  public function listado(Request $request) {
    $listado = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'b.id',
        'b.titulo',
        'b.step',
        DB::raw("CASE(b.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 0 THEN 'No aprobado'
            WHEN 1 THEN 'Aprobado'
            WHEN 3 THEN 'En evaluacion'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'Sustentado'
            WHEN 9 THEN 'En ejecución'
            WHEN 10 THEN 'Ejecutado'
            WHEN 11 THEN 'Concluído'
          ELSE 'Sin estado' END AS estado"),
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('a.condicion', '=', 'Responsable')
      ->where('b.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('b.periodo', '=', 2026)
      ->get();

    return $listado;
  }

  public function eliminarPropuesta(Request $request) {
    $proyectoId = $request->query('id');

    if ($proyectoId) {
      DB::table('Proyecto_descripcion')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_presupuesto')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto_integrante')
        ->where('proyecto_id', '=', $proyectoId)
        ->delete();

      DB::table('Proyecto')
        ->where('id', '=', $proyectoId)
        ->delete();

      return ['message' => 'info', 'detail' => 'Propuesta eliminada correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'No se pudo eliminar la propuesta'];
    }
  }

  public function verificar(Request $request) {
    $errores = [];

    $req1 = DB::table('Grupo_integrante')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->count();

    if ($req1 == 0) {
      $errores[] = 'Tiene que pertenecer a algún grupo de investigación.';
    }

    $req2 = DB::table('view_deudores AS vdeuda')
      ->where('vdeuda.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($req2 != 0) {
      $errores[] = "Usted tiene registradas deudas pendientes que deben ser resueltas para participar en el concurso";
    }

    $req3_1 = DB::table('Grupo_integrante')
      ->select([
        'grupo_id'
      ])
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->first();

    $req3 = DB::table('Proyecto')
      ->where('tipo_proyecto', '=', 'PRO-CTIE')
      ->where('periodo', '=', 2026)
      ->where('grupo_id', '=', $req3_1->grupo_id)
      ->count();

    if ($req3 >= 5) {
      $errores[] = "Su grupo en conjunto ya ha registro 5 proyectos, no se permiten más";
    }

    $proyecto = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->select([
        'a.proyecto_id',
        'b.estado',
        'b.step'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('b.periodo', '=', '2026')
      ->first();

    if ($proyecto != null) {
      if ($proyecto->estado != 6) {
        $errores[] = 'Su solicitud para esta convocatoria ya ha sido enviada.';
      } else {
        return ['estado' => true, 'paso' => 'paso' . $proyecto->step . '?proyecto_id=' . $proyecto->proyecto_id];
      }
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      return ['estado' => true, 'paso' => 'paso1'];
    }
  }

  public function datosPaso1(Request $request) {
    $esIntegrante = DB::table('Proyecto_integrante')
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esIntegrante > 0) {
      $proyecto = DB::table('Proyecto')
        ->select([
          'id',
          'titulo',
          'linea_investigacion_id',
          'ocde_id',
          'localizacion'
        ])
        ->where('id', '=', $request->query('proyecto_id'))
        ->first();

      $ods = DB::table('Proyecto_descripcion')
        ->select([
          'detalle'
        ])
        ->where('proyecto_id', '=', $request->query('proyecto_id'))
        ->where('codigo', '=', 'objetivo_ods')
        ->first();

      $tipo_investigacion = DB::table('Proyecto_descripcion')
        ->select([
          'detalle'
        ])
        ->where('proyecto_id', '=', $request->query('proyecto_id'))
        ->where('codigo', '=', 'tipo_investigacion')
        ->first();

      $listOds =  DB::table('Ods AS a')
        ->join('Linea_investigacion_ods AS b', 'b.ods_id', '=', 'a.id')
        ->select([
          'a.descripcion AS value'
        ])
        ->where('b.linea_investigacion_id', '=', $proyecto->linea_investigacion_id)
        ->get();

      $data = $this->getDataToPaso1($request);

      $ocde_1 = DB::table('Ocde')
        ->select([
          'parent_id',
        ])
        ->where('id', '=', $proyecto->ocde_id)
        ->first();

      $ocde_3er = DB::table('Ocde')
        ->select([
          'id AS value',
          DB::raw("CONCAT(codigo, ' ', linea) AS label"),
          'parent_id'
        ])
        ->where('parent_id', '=', $ocde_1->parent_id)
        ->get();

      $ocde_2 = DB::table('Ocde')
        ->select([
          'parent_id',
        ])
        ->where('id', '=', $ocde_3er[0]->parent_id)
        ->first();

      $ocde_2do = DB::table('Ocde')
        ->select([
          'id AS value',
          DB::raw("CONCAT(codigo, ' ', linea) AS label"),
          'parent_id'
        ])
        ->where('parent_id', '=', $ocde_2->parent_id)
        ->get();

      return [
        'proyecto' => $proyecto,
        'ods' => $ods,
        'tipo_investigacion' => $tipo_investigacion,
        'listOds' => $listOds,
        'data' => $data,
        'ocde_2' => $ocde_2do,
        'ocde_3' => $ocde_3er,
      ];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function getDataToPaso1(Request $request) {
    $data = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->join('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->select([
        'd.nombre AS facultad',
        'b.grupo_nombre',
        'b.id AS grupo_id'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->first();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'b.id AS value',
        'b.nombre AS label'
      ])
      ->where('a.grupo_id', '=', $data->grupo_id)
      ->where('b.estado', '=', 1)
      ->get();

    $ocdeLevel1 = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label")
      ])
      ->whereNull('parent_id')
      ->get();

    return ['data' => $data, 'lineas' => $lineas, 'ocde1' => $ocdeLevel1];
  }

  public function getOcde(Request $request) {
    $ocde = DB::table('Ocde')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' ', linea) AS label")
      ])
      ->where('parent_id', '=', $request->query('parent_id'))
      ->get();

    return $ocde;
  }

  public function getOds(Request $request) {
    $ods = DB::table('Ods AS a')
      ->join('Linea_investigacion_ods AS b', 'b.ods_id', '=', 'a.id')
      ->select([
        'a.descripcion AS value'
      ])
      ->where('b.linea_investigacion_id', '=', $request->query('linea_investigacion_id'))
      ->get();

    return $ods;
  }

  public function registrarPaso1(Request $request) {
    if ($request->input('proyecto_id') == null) {

      $data = DB::table('Grupo_integrante AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.id AS grupo_integrante_id',
          'a.facultad_id',
          'a.grupo_id',
          'a.condicion AS condicion_grupo',
          'b.codigo',
          'b.tipo',
        ])
        ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->whereNot('condicion', 'LIKE', 'Ex%')
        ->first();

      $id = DB::table('Proyecto')
        ->insertGetId([
          'facultad_id' => $data->facultad_id,
          'grupo_id' => $data->grupo_id,
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"],
          'ocde_id' => $request->input('ocde_3')["value"],
          'titulo' => $request->input('titulo'),
          'tipo_proyecto' => 'PRO-CTIE',
          'fecha_inscripcion' => Carbon::now(),
          'localizacion' => $request->input('localizacion')["value"],
          'periodo' => 2026,
          'convocatoria' => 1,
          'step' => 2,
          'estado' => 6,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      DB::table('Proyecto_descripcion')
        ->insert([
          'proyecto_id' => $id,
          'codigo' => 'objetivo_ods',
          'detalle' => $request->input('ods')["value"]
        ]);

      DB::table('Proyecto_descripcion')
        ->insert([
          'proyecto_id' => $id,
          'codigo' => 'tipo_investigacion',
          'detalle' => $request->input('tipo_investigacion')["value"]
        ]);

      DB::table('Proyecto_integrante')
        ->insert([
          'proyecto_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'condicion' => 'Responsable',
          'proyecto_integrante_tipo_id' => 86,
          'codigo' => $data->codigo ?? null,
          'tipo_investigador' => $data->tipo ?? null,
          'condicion_grupo' => $data->condicion_grupo ?? null,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      return ['message' => 'success', 'detail' => 'Datos del proyecto registrados', 'proyecto_id' => $id];
    } else {
      DB::table('Proyecto')
        ->where('id', '=', $request->input('proyecto_id'))
        ->update([
          'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"],
          'ocde_id' => $request->input('ocde_3')["value"],
          'titulo' => $request->input('titulo'),
          'localizacion' => $request->input('localizacion')["value"],
          'step' => 2,
          'updated_at' => Carbon::now(),
        ]);

      DB::table('Proyecto_descripcion')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('codigo', '=', 'objetivo_ods',)
        ->update([
          'detalle' => $request->input('ods')["value"]
        ]);

      DB::table('Proyecto_descripcion')
        ->where('proyecto_id', '=', $request->input('proyecto_id'))
        ->where('codigo', '=', 'tipo_investigacion')
        ->update([
          'detalle' => $request->input('tipo_investigacion')["value"]
        ]);
    }
  }

  //  Paso 2
  public function getDataPaso2(Request $request) {

    $data = DB::table('Usuario_investigador')
      ->select([
        DB::raw("CONCAT(apellido1, ' ', apellido2, ', ', nombres) AS nombres"),
        'doc_numero',
        'doc_numero',
        'fecha_nac',
        'codigo',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(docente_categoria, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS docente_categoria"),
        'codigo_orcid',
        'renacyt',
        'cti_vitae'
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $key = DB::table('Proyecto_doc')
      ->select([
        'archivo',
        'comentario'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('categoria', '=', 'carta')
      ->where('nombre', '=', 'Carta de compromiso del asesor')
      ->where('estado', '=', 1)
      ->first();

    if ($key != null) {
      $data->url = "/minio/proyecto-doc/" . $key->archivo;
      $data->comentario = $key->comentario;
    }

    return $data;
  }

  public function registrarPaso2(Request $request) {
    $id = $request->input('proyecto_id');
    $date = Carbon::now();
    $name = $id . "-" . $date->format('Ymd-His');

    if ($request->hasFile('file')) {
      $nameFile = $id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

      DB::table('Proyecto')
        ->where('id', '=', $id)
        ->update([
          'step' => 3,
        ]);

      DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $id)
        ->where('categoria', '=', 'carta')
        ->where('nombre', '=', 'Carta de compromiso del asesor')
        ->update([
          'estado' => 0
        ]);

      DB::table('Proyecto_doc')
        ->insert([
          'proyecto_id' => $id,
          'categoria' => 'carta',
          'tipo' => 29,
          'nombre' => 'Carta de compromiso del asesor',
          'comentario' => $date,
          'archivo' => $nameFile,
          'estado' => 1
        ]);

      return ['message' => 'success', 'detail' => 'Archivo actualizado correctamente'];
    } else {
      $count = DB::table('Proyecto_doc')
        ->where('proyecto_id', '=', $id)
        ->where('categoria', '=', 'carta')
        ->where('nombre', '=', 'Carta de compromiso del asesor')
        ->where('estado', '=', 1)
        ->count();


      if ($count > 0) {
        DB::table('Proyecto')
          ->where('id', '=', $id)
          ->update([
            'step' => 3,
          ]);
        return ['message' => 'success', 'detail' => 'Se ha verificado que ya ha cargado la carta de compromiso'];
      } else {
        return ['message' => 'error', 'detail' => 'Necesita cargar la carta de compromiso'];
      }
    }
  }

  //  Paso 3
  public function listarIntegrantes(Request $request) {

    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
      ->leftJoin('File AS e', function ($join) {
        $join->on('e.tabla_id', '=', 'a.id')
          ->where('e.tabla', '=', 'Proyecto_integrante')
          ->where('e.estado', '=', 1);
      })
      ->select([
        'a.id',
        'b.nombre AS tipo_integrante',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS nombre"),
        'c.tipo',
        'd.nombre AS facultad',
        'a.condicion',
        'a.condicion',
        DB::raw("CONCAT('/minio/', e.bucket, '/', e.key) AS url")
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $key = DB::table('Proyecto_doc')
      ->select([
        'archivo',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->where('categoria', '=', 'carta')
      ->where('nombre', '=', 'Carta de compromiso del asesor')
      ->where('estado', '=', 1)
      ->first();

    $integrantes[0]->url = "/minio/proyecto-doc/" . $key->archivo;

    return $integrantes;
  }

  public function searchEstudiante(Request $request) {
    $estudiantes = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno',
        'a.dni',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.facultad',
        'a.programa',
        'a.permanencia',
        'a.correo_electronico'
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula'])
      ->where('a.programa', 'LIKE', 'E.P.%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function verificarEstudiante(Request $request) {
    $errores = [];
    $codigo = trim($request->query('codigo'));
    $investigador_id = $request->query('investigador_id');

    if ($investigador_id == 'null' || $investigador_id == 'undefined' || $investigador_id == '') {
      $investigador_id = null;
    }

    if (empty($investigador_id) && !empty($codigo)) {
      $usuarioInvestigador = DB::table('Usuario_investigador')
        ->select('id')
        ->where('codigo', '=', $codigo)
        ->first();

      $investigador_id = $usuarioInvestigador->id ?? null;
    }

    $req1 = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->where(function ($query) use ($codigo, $investigador_id) {
        $query->where('c.codigo', '=', $codigo)
          ->orWhere('a.codigo', '=', $codigo);
        if (!empty($investigador_id)) {
          $query->orWhere('a.investigador_id', '=', $investigador_id);
        }
       })
      ->where('b.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('b.periodo', '=', 2026)
      ->count();

    if ($req1 > 0) {
      $errores[] = 'Ya es participante en otro proyecto PRO-CTIE de este año.';
    }

    $req2 = DB::table('Proyecto_integrante AS a')
        ->join('Proyecto AS b', 'b.id', '=', 'a.proyecto_id')
        ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
        ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'a.investigador_id')
        ->where(function ($query) use ($codigo, $investigador_id) {
          $query->where('d.codigo', '=', $codigo)
            ->orWhere('a.codigo', '=', $codigo);

          if (!empty($investigador_id)) {
            $query->orWhere('a.investigador_id', '=', $investigador_id);
          }
        })
        ->whereIn('b.estado', [1, 8])
        ->where('c.nombre', 'LIKE', '%Tesista%')
        ->count();   

    if ($req2 > 0) {
      $errores[] = 'Participo o esta participando en otro proyecto aprobado';
    } 

    if (!empty($errores)) {
      return ['message' => 'error', 'detail' => $errores[0]];
    } else {
      return ['message' => 'success', 'detail' => 'Cumple con los requisitos para ser incluído'];
    }
  }

  public function listarAdherentes(Request $request) {

    $grupo = DB::table('Proyecto')
      ->join('Grupo', 'Grupo.id', '=', 'Proyecto.grupo_id')
      ->select('Grupo.id as grupo_id')
      ->where('Proyecto.id', '=', $request->query('proyecto_id'))
      ->first();

    if (!$grupo) {
      return [];
    }

    $adherentes = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS d', function (JoinClause $join) use ($request) {
        $join->on('d.investigador_id', '=', 'b.id')
          ->where('d.proyecto_id', '=', $request->query('proyecto_id'));
      })
      ->leftjoin('Repo_sum AS e', 'e.codigo_alumno', '=', 'b.codigo')
      ->select(
        'a.id',
        'a.investigador_id',
        DB::raw("CONCAT(e.apellido_paterno, ' ', e.apellido_materno, ', ', e.nombres, ' | ', e.programa) AS value"),
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2) as apellidos"),
        'b.nombres',
        'b.doc_numero',
        'b.codigo',
        'a.permanencia',
        'b.email1',
        'c.nombre as facultad'
      )
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('a.condicion', '=', 'Adherente')
      ->where('e.programa', 'LIKE', 'E.P.%')
      ->whereNull('d.id')
      ->get();

    return $adherentes;
  }

  public function agregarAdherente(Request $request) {
    if (!$request->hasFile('file')) {
      return ['message' => 'error', 'detail' => 'Debe adjuntar la carta de compromiso.'];
    }

    $date = Carbon::now();
    $proyectoId = $request->input('proyecto_id');
    $investigadorId = $request->input('investigador_id');

    $existe = DB::table('Proyecto_integrante')
      ->where('proyecto_id', $proyectoId)
      ->where('investigador_id', $investigadorId)
      ->exists();

    if ($existe) {
      return ['message' => 'warning', 'detail' => 'Este adherente ya está registrado en el proyecto.'];
    }

    $grupo = DB::table('Proyecto')
      ->select([
        'grupo_id'
      ])
      ->where('id', '=', $request->input('proyecto_id'))
      ->first();

    $grupoIntegrante = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.id AS grupo_integrante_id',
        'a.condicion AS condicion_grupo',
        'b.codigo',
        'b.tipo',
      ])
      ->where('a.grupo_id', '=', $grupo->grupo_id)
      ->where('a.investigador_id', '=', $request->input('investigador_id'))
      ->where('a.condicion', 'NOT LIKE', 'Ex%')
      ->first();

    $id = DB::table('Proyecto_integrante')->
      insertGetId([
        'proyecto_id' => $request->input('proyecto_id'),
        'investigador_id' => $request->input('investigador_id'),
        'grupo_id' => $grupo->grupo_id ?? null,
        'grupo_integrante_id' => $grupoIntegrante->id ?? null,
        'condicion' => 'Adherente',
        'proyecto_integrante_tipo_id' => 88,
        'codigo' => $grupoIntegrante->codigo ?? null,
        'tipo_investigador' => $grupoIntegrante->tipo ?? null,
        'condicion_grupo' => $grupoIntegrante->condicion_grupo ?? null,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    $ext = $request->file('file')->getClientOriginalExtension();
    $nameFile = $proyectoId . '-' . $date->format('Ymd-His') . "-" . $id . '.' . $ext;

    DB::table('File')->insert([
      'tabla_id' => $id,
      'tabla' => 'Proyecto_integrante',
      'bucket' => 'proyecto-doc',
      'key' => $nameFile,
      'recurso' => 'CARTA_COMPROMISO',
      'estado' => 1,
      'created_at' => $date,
      'updated_at' => $date,
    ]);

    $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

    return ['message' => 'success', 'detail' => 'Adherente agregado correctamente al proyecto.'];
  }

  public function agregarIntegrante(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();

      DB::table('Proyecto')
        ->where('id', '=', $request->input('proyecto_id'))
        ->update([
          'step' => 3,
        ]);

      $id_investigador = $request->input('investigador_id');

      if ($id_investigador == "null") {

        $sumData = DB::table('Repo_sum')
          ->select([
            'id_facultad',
            'codigo_alumno',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'dni',
            'sexo',
            'correo_electronico',
            'programa',
            'permanencia',
          ])
          ->where('id', '=', $request->input('sum_id'))
          ->first();

        $id_investigador = DB::table('Usuario_investigador')
          ->insertGetId([
            'facultad_id' => $sumData->id_facultad,
            'codigo' => $sumData->codigo_alumno,
            'nombres' => $sumData->nombres,
            'apellido1' => $sumData->apellido_paterno,
            'apellido2' => $sumData->apellido_materno,
            'doc_tipo' => 'DNI',
            'doc_numero' => $sumData->dni,
            'tipo' => 'Estudiante pregrado',
            'sexo' => $sumData->sexo,
            'email3' => $sumData->correo_electronico,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'tipo_investigador' => 'Estudiante'
          ]);
      }

      $proyecto = DB::table('Proyecto')
        ->select([
          'grupo_id'
        ])
        ->where('id', '=', $request->input('proyecto_id'))
        ->first();

      $datosIntegrante = DB::table('Usuario_investigador AS b')
        ->leftJoin('Grupo_integrante AS a', function ($join) use ($proyecto) {
          $join->on('a.investigador_id', '=', 'b.id');

          if ($proyecto && $proyecto->grupo_id) {
            $join->where('a.grupo_id', '=', $proyecto->grupo_id);
          }

          $join->where('a.condicion', 'NOT LIKE', 'Ex%');
        })
        ->select([
          'a.id AS grupo_integrante_id',
          'a.grupo_id',
          'a.condicion AS condicion_grupo',
          'b.codigo',
          'b.tipo',
        ])
        ->where('b.id', '=', $id_investigador)
        ->first();

      $id = DB::table('Proyecto_integrante')
        ->insertGetId([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $id_investigador,
          'condicion' => 'Colaborador',
          'proyecto_integrante_tipo_id' => 88,
          'codigo' => $datosIntegrante->codigo ?? null,
          'tipo_investigador' => $datosIntegrante->tipo ?? null,
          'condicion_grupo' => $datosIntegrante->condicion_grupo ?? null,
          'created_at' => $date,
          'updated_at' => $date
        ]);

      $name = $date->format('Ymd-His') . "-" . $id;

      $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

      DB::table('File')
        ->insert([
          'tabla_id' => $id,
          'tabla' => 'Proyecto_integrante',
          'bucket' => 'carta-compromiso',
          'key' => $nameFile,
          'recurso' => 'CARTA_COMPROMISO',
          'estado' => 1,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      $this->uploadFile($request->file('file'), "carta-compromiso", $nameFile);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function agregarIntegranteExterno(Request $request) {
    if ($request->hasFile('file')) {
      $date = Carbon::now();

      DB::table('Proyecto')
        ->where('id', '=', $request->input('proyecto_id'))
        ->update([
          'step' => 3,
        ]);

      $id_investigador = DB::table('Usuario_investigador')
        ->insertGetId([
          'codigo_orcid' => $request->input('codigo_orcid'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'nombres' => $request->input('nombres'),
          'sexo' => $request->input('sexo'),
          'institucion' => $request->input('institucion'),
          'tipo' => 'Externo',
          'pais' => $request->input('pais'),
          'direccion1' => $request->input('direccion1'),
          'doc_tipo' => $request->input('doc_tipo'),
          'doc_numero' => $request->input('doc_numero'),
          'telefono_movil' => $request->input('telefono_movil'),
        ]);

      $id = DB::table('Proyecto_integrante')
        ->insertGetId([
          'proyecto_id' => $request->input('proyecto_id'),
          'investigador_id' => $id_investigador,
          'condicion' => 'Colaborador',
          'proyecto_integrante_tipo_id' => 88,
          'created_at' => $date,
          'updated_at' => $date
        ]);

      $name = $date->format('Ymd-His') . "-" . $id;

      $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

      DB::table('File')
        ->insert([
          'tabla_id' => $id,
          'tabla' => 'Proyecto_integrante',
          'bucket' => 'carta-compromiso',
          'key' => $nameFile,
          'recurso' => 'CARTA_COMPROMISO',
          'estado' => 1,
          'created_at' => $date,
          'updated_at' => $date,
        ]);

      $this->uploadFile($request->file('file'), "carta-compromiso", $nameFile);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function eliminarIntegrante(Request $request) {
    DB::table('Proyecto_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    DB::table('File')
      ->where('tabla_id', '=', $request->query('id'))
      ->where('tabla', '=', 'Proyecto_integrante')
      ->where('estado', '=', 1)
      ->update([
        'estado' => -1,
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Integrante eliminado correctamente'];
  }

  //  Paso 4
  public function getDataPaso4(Request $request) {
    $palabras = DB::table('Proyecto')
      ->select([
        'palabras_clave'
      ])
      ->where('id', '=', $request->query('proyecto_id'))
      ->first();

    $detalles = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    return ['palabras_claves' => $palabras->palabras_clave, 'detalles' => $detalles];
  }

  public function registrarPaso4(Request $request) {

    $palabrasConcatenadas = "";
    foreach ($request->input('palabras_clave') as $palabra) {
      $palabrasConcatenadas = $palabrasConcatenadas . $palabra["label"] . ",";
    }
    $palabrasConcatenadas = rtrim($palabrasConcatenadas, ',');

    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'palabras_clave' => $palabrasConcatenadas,
        'step' => 5,
      ]);

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'resumen_ejecutivo')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'resumen_ejecutivo',
        ],
        [
          'detalle' => $request->input('resumen')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'antecedentes')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'antecedentes'
        ],
        [
          'detalle' => $request->input('antecedentes')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'justificacion')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'justificacion'
        ],
        [
          'detalle' => $request->input('justificacion')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'contribucion_impacto')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'contribucion_impacto'
        ],
        [
          'detalle' => $request->input('contribucion')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'objetivos')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'objetivos'
        ],
        [
          'detalle' => $request->input('objetivos')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'metodologia_trabajo')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'metodologia_trabajo'
        ],
        [
          'detalle' => $request->input('metodologia')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'hipotesis')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'hipotesis'
        ],
        [
          'detalle' => $request->input('hipotesis')
        ]
      );

    DB::table('Proyecto_descripcion')
      ->where('proyecto_id', '=', $request->input('proyecto_id'))
      ->where('codigo', '=', 'referencias_bibliograficas')
      ->updateOrInsert(
        [
          'proyecto_id' => $request->input('proyecto_id'),
          'codigo' => 'referencias_bibliograficas'
        ],
        [
          'detalle' => $request->input('referencias')
        ]
      );

    return ['message' => 'success', 'detail' => 'Datos cargados correctamente'];
  }

  //  Paso 5
  public function listarActividades(Request $request) {
    $actividades = DB::table('Proyecto_actividad')
      ->select([
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin',
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    $rango_fechas = DB::table('Convocatoria')
      ->select([
        'fecha_inicial',
        'fecha_final'
      ])
      ->where('tipo', '=', 'PRO-CTIE')
      ->where('periodo', '=', 2026)
      ->where('evento', '=', 'calendario')
      ->where('estado', '=', 1)
      ->first();

    return [
      'actividades' => $actividades,
      'rango' => $rango_fechas
    ];
  }

  public function agregarActividad(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'step' => 5,
      ]);

    DB::table('Proyecto_actividad')
      ->insert([
        'proyecto_id' => $request->input('proyecto_id'),
        'actividad' => $request->input('actividad'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
      ]);

    return ['message' => 'success', 'detail' => 'Actividad registrada correctamente'];
  }

  public function eliminarActividad(Request $request) {
    DB::table('Proyecto_actividad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Actividad eliminada correctamente'];
  }

  public function listarPartidas(Request $request) {
    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'a.id',
        'b.id AS partida_id',
        'b.codigo',
        'b.tipo',
        'b.partida',
        'a.monto'
      ])
      ->where('a.proyecto_id', '=', $request->query('proyecto_id'))
      ->orderBy('b.tipo')
      ->orderBy('b.codigo')
      ->get();

    $partidaIds = $presupuesto->pluck('partida_id');

    $partidas = DB::table('Partida_proyecto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select([
        'b.id AS value',
        DB::raw("CONCAT(b.codigo, ' - ', b.partida) AS label"),
        'b.tipo',
      ])
      ->where('a.tipo_proyecto', '=', 'PRO-CTIE')
      ->where('a.postulacion', '=', 1)
      ->where('periodo', '=', 2026)
      ->whereNotIn('b.id', $partidaIds)
      ->orderBy('b.tipo')
      ->orderBy('b.codigo')
      ->get();

    $monto_disponible = 25000;
    foreach ($presupuesto as $partida) {
      $monto_disponible = $monto_disponible - $partida->monto;
    }

    return ['presupuesto' => $presupuesto, 'partidas' => $partidas, 'monto_disponible' => $monto_disponible];
  }

  public function agregarPartida(Request $request) {
    $date = Carbon::now();

    $proyectoId = $request->input('proyecto_id');
    $partidaId = $request->input('partida_id');
    $montoNuevo = floatval($request->input('monto'));
    $periodo = 2026;
    $tipoProyecto = 'PRO-CTIE';

    if ($montoNuevo <= 0) {
      return [
        'message' => 'warning',
        'detail' => 'El monto debe ser mayor a 0.'
      ];
    }

    $totalActualProyecto = DB::table('Proyecto_presupuesto')
      ->where('proyecto_id', '=', $proyectoId)
      ->sum('monto');

    $nuevoTotalProyecto = floatval($totalActualProyecto) + $montoNuevo;

    if ($nuevoTotalProyecto > 25000) {
      return [
        'message' => 'warning',
        'detail' => 'El presupuesto total del proyecto no puede superar S/. 25,000.00. Actualmente ya tiene registrado S/. ' . number_format($totalActualProyecto, 2) . '.'
      ];
    }

    $grupo = DB::table('Partida_proyecto AS pp')
      ->join('Partida_proyecto_grupo AS ppg', 'ppg.partida_proyecto_id', '=', 'pp.id')
      ->join('Partida_grupo AS pg', function ($join) use ($tipoProyecto) {
        $join->on('pg.id', '=', 'ppg.partida_grupo_id')
          ->where('pg.tipo_proyecto', '=', $tipoProyecto);
      })
      ->select([
        'pg.id AS grupo_id',
        'pg.nombre AS grupo_nombre',
        'pg.monto_max',
      ])
      ->where('pp.partida_id', '=', $partidaId)
      ->where('pp.tipo_proyecto', '=', $tipoProyecto)
      ->where('pp.postulacion', '=', 1)
      ->where('pp.periodo', '=', $periodo)
      ->first();

      if (!$grupo) {
        return [
          'message' => 'warning',
          'detail' => 'No se encontró grupo/límite para la partida seleccionada. Partida ID: ' . $partidaId
        ];
      }

    $totalActualGrupo = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida_proyecto AS pp', function ($join) use ($tipoProyecto, $periodo) {
        $join->on('pp.partida_id', '=', 'a.partida_id')
          ->where('pp.tipo_proyecto', '=', $tipoProyecto)
          ->where('pp.postulacion', '=', 1)
          ->where('pp.periodo', '=', $periodo);
      })
      ->join('Partida_proyecto_grupo AS ppg', 'ppg.partida_proyecto_id', '=', 'pp.id')
      ->where('a.proyecto_id', '=', $proyectoId)
      ->where('ppg.partida_grupo_id', '=', $grupo->grupo_id)
      ->sum('a.monto');

    $nuevoTotalGrupo = floatval($totalActualGrupo) + $montoNuevo;

    if ($nuevoTotalGrupo > floatval($grupo->monto_max)) {
      return [
        'message' => 'warning',
        'detail' => 'La partida pertenece al grupo "' . $grupo->grupo_nombre . '" y supera el límite permitido de S/. ' . number_format($grupo->monto_max, 2) . '. Actualmente ya tiene registrado S/. ' . number_format($totalActualGrupo, 2) . '.'
      ];
    }

    DB::table('Proyecto')
      ->where('id', '=', $proyectoId)
      ->update([
        'step' => 6,
        'updated_at' => $date,
      ]);

    $cuenta = DB::table('Proyecto_presupuesto')
      ->select([
        'id',
        'monto'
      ])
      ->where('partida_id', '=', $partidaId)
      ->where('proyecto_id', '=', $proyectoId)
      ->first();

    if ($cuenta) {
      DB::table('Proyecto_presupuesto')
        ->where('id', '=', $cuenta->id)
        ->update([
          'monto' => floatval($cuenta->monto) + $montoNuevo,
          'updated_at' => $date
        ]);
    } else {
      DB::table('Proyecto_presupuesto')
        ->insert([
          'partida_id' => $partidaId,
          'proyecto_id' => $proyectoId,
          'monto' => $montoNuevo,
          'created_at' => $date,
          'updated_at' => $date
        ]);
    }

    return ['message' => 'success', 'detail' => 'Partida registrada correctamente'];
  }

  public function eliminarPartida(Request $request) {
    DB::table('Proyecto_presupuesto')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Partida eliminada correctamente'];
  }


  public function validarPresupuesto(Request $request) {
    $alerta = [];
    $periodo = 2026;
    $tipoProyecto = 'PRO-CTIE';

    $partidas = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida_proyecto AS b', function (JoinClause $join) use ($periodo, $tipoProyecto) {
        $join->on('b.partida_id', '=', 'a.partida_id')
          ->where('b.tipo_proyecto', '=', $tipoProyecto)
          ->where('b.postulacion', '=', 1)  
          ->where('b.periodo', '=', $periodo);
      })
      ->leftJoin('Partida_proyecto_grupo AS c', 'c.partida_proyecto_id', '=', 'b.id')
      ->leftJoin('Partida_grupo AS d', function ($join) use ($tipoProyecto) {
          $join->on('d.id', '=', 'c.partida_grupo_id')
            ->where('d.tipo_proyecto', '=', $tipoProyecto);
        })
      ->select([
        'd.id',
        'd.nombre',
        'd.monto_max',
        DB::raw("SUM(a.monto) AS total")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->whereNotNull('d.id')
      ->groupBy('d.id', 'd.nombre', 'd.monto_max')
      ->get();

    foreach ($partidas as $item) {
      if (floatval($item->monto_max) < floatval($item->total)) {
        $alerta[] = $item->nombre . ": " . number_format($item->monto_max, 2);
      }
    };

    if (sizeof($alerta) == 0) {
      return ['message' => 'info', 'detail' => 'Su proyecto respeta los límites de la directiva'];
    } else {
      return ['message' => 'warning', 'detail' => 'El presupuesto presenta excesos en la(s) siguiente(s) categoría(s). ' . implode(',', $alerta) . '; para mayor detalle revisar la directiva correspondiente.', $alerta];
    }
  }

  public function enviarProyecto(Request $request) {
    //  Verificar autorización de grupo
    $req1 = DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->where('estado', '=', 6)
      ->where('autorizacion_grupo', '=', 1)
      ->count();

    if ($req1 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita que el coordinador de su grupo autorice la propuesta de proyecto'];
    }

    //  Verificar autorización de grupo
    $req1 = DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->where('estado', '=', 6)
      ->where('autorizacion_grupo', '=', 1)
      ->count();

    if ($req1 == 0) {
      return ['message' => 'error', 'detail' => 'Necesita que el coordinador de su grupo autorice la propuesta de proyecto'];
    }

    DB::table('Proyecto')
      ->where('id', '=', $request->input('proyecto_id'))
      ->update([
        'estado' => 5
      ]);

    return ['message' => 'info', 'detail' => 'Proyecto enviado para evaluación'];
  }

  public function reportePDF(Request $request) {

    //  Proyecto
    $proyecto = DB::table('Proyecto_integrante AS pint')
      ->join('Proyecto AS p', 'p.id', '=', 'pint.proyecto_id')
      ->join('Grupo AS g', 'g.id', '=', 'p.grupo_id')
      ->join('Facultad AS f', 'f.id', '=', 'g.facultad_id')
      ->join('Area AS a', 'a.id', '=', 'f.area_id')
      ->join('Linea_investigacion AS l', 'l.id', '=', 'p.linea_investigacion_id')
      ->join('Linea_investigacion_ods AS lo', 'lo.linea_investigacion_id', '=', 'l.id')
      ->join('Ods AS o', 'o.id', '=', 'lo.ods_id')
      ->leftJoin('Ocde AS oc', 'oc.id', '=', 'p.ocde_id')
      ->select([
        'g.grupo_nombre',
        'f.nombre AS facultad_nombre',
        'a.nombre AS area_nombre',
        'p.codigo_proyecto',
        'p.titulo',
        'l.nombre AS linea_nombre',
        'o.objetivo',
        'oc.linea',
        'p.palabras_clave',
        'p.periodo',
        'p.localizacion',
        DB::raw("CASE(p.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 1 THEN 'Aprobado'
          WHEN 3 THEN 'En evaluación'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 7 THEN 'Anulado'
          WHEN 8 THEN 'Sustentado'
          WHEN 9 THEN 'En ejecucion'
          WHEN 10 THEN 'Ejecutado'
          WHEN 11 THEN 'Concluido'
          ELSE 'Sin estado'
        END AS estado"),
        'p.updated_at'
      ])
      ->where('pint.condicion', '=', 'Responsable')
      ->where('pint.proyecto_id', '=', $request->query('proyecto_id'))
      ->first();

    //  Descripcion
    $descripcion = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('proyecto_id'))
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    //  Actividades
    $actividades = DB::table('Proyecto AS p')
      ->join('Proyecto_actividad AS pa', 'p.id', '=', 'pa.proyecto_id')
      ->select([
        'pa.actividad',
        'pa.fecha_inicio',
        'pa.fecha_fin'
      ])
      ->where('pa.proyecto_id', '=', $request->query('proyecto_id'))
      ->get();

    //  Presupuesto
    $presupuesto = DB::table('Proyecto AS p')
      ->join('Proyecto_presupuesto AS pp', 'pp.proyecto_id', '=', 'p.id')
      ->join('Partida AS pt', 'pt.id', '=', 'pp.partida_id')
      ->select([
        'pt.partida',
        'pp.monto',
        'pt.tipo'
      ])
      ->where('p.id', '=', $request->query('proyecto_id'))
      ->get();

    //  Integrantes
    $integrantes = DB::table('Proyecto_integrante AS pint')
      ->join('Usuario_investigador AS i', 'i.id', '=', 'pint.investigador_id')
      ->join('Proyecto_integrante_tipo AS pt', 'pt.id', '=', 'pint.proyecto_integrante_tipo_id')
      ->join('Facultad AS f', 'f.id', '=', 'i.facultad_id')
      ->leftJoin('Grupo_integrante AS gi', 'gi.investigador_id', '=', 'i.id')
      ->select([
        'pt.nombre AS tipo_integrante',
        DB::raw("CONCAT(i.apellido1, ' ', i.apellido2, ' ', i.nombres) AS integrante"),
        'f.nombre AS facultad',
        'pint.tipo_investigador as tipo',
        'pint.condicion'
      ])
      ->where('pint.proyecto_id', '=', $request->query('proyecto_id'))
      ->where(function ($query) {
        $query->where('gi.condicion', 'NOT LIKE', 'Ex%')
          ->orWhereNull('gi.condicion');
      })
      ->get();

    $pdf = Pdf::loadView('investigador.convocatorias.proctie', [
      'proyecto' => $proyecto,
      'descripcion' => $descripcion,
      'actividades' => $actividades,
      'presupuesto' => $presupuesto,
      'integrantes' => $integrantes
    ]);
    return $pdf->stream();
  }
}

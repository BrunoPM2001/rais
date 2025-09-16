<?php

namespace App\Http\Controllers\Investigador\Grupo;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrupoController extends S3Controller {
  //  Grupos
  public function listadoGrupos(Request $request) {
    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select(
        'b.id',
        'b.grupo_nombre',
        'b.grupo_categoria',
        'a.condicion',
        'a.cargo',
        'b.resolucion_fecha',
        DB::raw("CASE(b.estado)
          WHEN -2 THEN 'Disuelto'
          WHEN 4 THEN 'Registrado'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado")
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo', '=', 'grupo')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    return $grupos;
  }

  public function listadoSolicitudes(Request $request) {
    $grupos = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select(
        'b.id',
        'b.grupo_nombre',
        'b.grupo_categoria',
        'a.condicion',
        'a.cargo',
        'b.resolucion_fecha',
        'b.step',
        DB::raw("CASE(b.estado)
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          ELSE 'Estado desconocido'
        END AS estado")
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.tipo', '=', 'solicitud')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->get();

    return $grupos;
  }

  //  Solicitar grupo
  public function verificar1(Request $request) {
    $errores = [];

    $miembroGrupo = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('b.tipo', '=', 'grupo')
      ->count();

    if ($miembroGrupo > 0) {
      $errores[] = 'Ya pertenece a un grupo de investigación';
    }

    $miembroSolicitud = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('b.id', '=', $request->query('id'))
      ->where('b.tipo', '=', 'solicitud')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->count();

    if ($miembroSolicitud > 0) {
      $errores[] = 'Ya está siendo incluído en la solicitud de grupo de alguien más';
    }

    if (!empty($errores)) {
      return ['estado' => false, 'message' => $errores];
    } else {
      $datos = DB::table('Grupo')
        ->select([
          'grupo_nombre',
          'grupo_nombre_corto'
        ])
        ->where('id', '=', $request->query('id'))
        ->first();

      return ['estado' => true, 'datos' => $datos];
    }
  }

  public function registrar1(Request $request) {
    if ($request->input('id')) {
      $rep = DB::table('Grupo')
        ->where('id', '!=', $request->input('id'))
        ->where(function ($query) use ($request) {
          $query->orWhere('grupo_nombre', '=', $request->input('grupo_nombre'))
            ->orWhere('grupo_nombre_corto', '=', $request->input('grupo_nombre_corto'));
        })
        ->count();

      if ($rep > 0) {
        return ['message' => 'error', 'detail' => 'El nombre o el nombre corto que ha colocado ya lo tiene otro grupo'];
      }

      DB::table('Grupo')
        ->where('id', '=',  $request->input('id'))
        ->update([
          'grupo_nombre' => $request->input('grupo_nombre'),
          'grupo_nombre_corto' => $request->input('grupo_nombre_corto'),
        ]);

      return ['message' => 'success', 'detail' => 'Información actualizada', 'id' => $request->input('id')];
    } else {
      $rep = DB::table('Grupo')
        ->where('grupo_nombre', '=', $request->input('grupo_nombre'))
        ->orWhere('grupo_nombre_corto', '=', $request->input('grupo_nombre_corto'))
        ->count();

      if ($rep > 0) {
        return ['message' => 'error', 'detail' => 'El nombre o el nombre corto que ha colocado ya lo tiene otro grupo'];
      }

      $now = Carbon::now();

      $investigador = DB::table('Usuario_investigador')
        ->select([
          'facultad_id',
          'email3'
        ])
        ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
        ->first();

      $id = DB::table('Grupo')
        ->insertGetId([
          'grupo_nombre' => $request->input('grupo_nombre'),
          'grupo_nombre_corto' => $request->input('grupo_nombre_corto'),
          'facultad_id' => $investigador->facultad_id,
          'email' => $investigador->email3,
          'coorddatos' => $request->attributes->get('token_decoded')->investigador_id,
          'tipo' => 'solicitud',
          'step' => 2,
          'estado' => 6,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

      DB::table('Grupo_integrante')
        ->insert([
          'grupo_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'cargo' => 'Coordinador',
          'condicion' => 'Titular',
          'estado' => 1,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

      return ['message' => 'success', 'detail' => 'Datos registrados correctamente', 'id' => $id];
    }
  }

  public function validarSol(Request $request, $id) {
    $errores = [];

    $miembroGrupo = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->where('b.tipo', '=', 'grupo')
      ->count();

    if ($miembroGrupo > 0) {
      $errores[] = 'Ya pertenece a un grupo de investigación';
    }

    $miembroSolicitud = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereNot('b.id', '=', $id)
      ->where('b.tipo', '=', 'solicitud')
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->count();

    if ($miembroSolicitud > 0) {
      $errores[] = 'Ya está siendo incluído en la solicitud de grupo de alguien más';
    }

    $coordinadorSolicitud = DB::table('Grupo_integrante AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->select([
        'b.id'
      ])
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.id', '=', $id)
      ->where('a.cargo', '=', 'Coordinador')
      ->where('b.tipo', '=', 'solicitud')
      ->count();

    if ($coordinadorSolicitud == 0) {
      $errores[] = 'No tienen ninguna solicitud en curso para este grupo';
    }
    return $errores;
  }

  //  Paso 2
  public function verificar2(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $docente = DB::table('Usuario_investigador AS a')
      ->join('Dependencia AS b', 'b.id', '=', 'a.dependencia_id')
      ->leftJoin('view_puntaje_7u AS c', 'c.investigador_id', '=', 'a.id')
      ->select([
        DB::raw("CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres) AS nombre"),
        'a.doc_numero',
        'a.codigo',
        'a.tipo',
        'b.dependencia',
        'a.cti_vitae',
        'a.google_scholar',
        'a.codigo_orcid',
        //  Editable
        'a.grado',
        'a.titulo_profesional',
        'a.especialidad',
        'a.instituto_id',
        'a.email3',
        'a.telefono_casa',
        'a.telefono_trabajo',
        'a.telefono_movil',
        'c.puntaje'
      ])
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $institutos = DB::table('Instituto')
      ->select([
        'id AS value',
        'instituto AS label'
      ])
      ->get();

    return ['estado' => true, 'datos' => $docente, 'institutos' => $institutos];
  }

  public function registrar2(Request $request) {
    $sol = $this->validarSol($request, $request->input('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $now = Carbon::now();
    DB::table('Usuario_investigador')
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->update([
        'grado' => $request->input('grado')["value"],
        'titulo_profesional' => $request->input('titulo_profesional'),
        'especialidad' => $request->input('especialidad'),
        'instituto_id' => $request->input('instituto_id')["value"],
        'email3' => $request->input('email3'),
        'telefono_casa' => $request->input('telefono_casa'),
        'telefono_trabajo' => $request->input('telefono_trabajo'),
        'telefono_movil' => $request->input('telefono_movil'),
        'updated_at' => $now
      ]);

    DB::table('Grupo')
      ->where('id', '=', $request->input('id'))
      ->update([
        'step' => 3
      ]);

    return ['message' => 'success', 'detail' => 'Datos registrados correctamente', 'id' => $request->input('id')];
  }

  //  Paso 3
  public function verificar3(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $integrantes = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        'a.condicion',
        'a.cargo',
        'b.doc_numero',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        'b.codigo_orcid',
        'b.google_scholar',
        'b.cti_vitae',
        'b.tipo',
        'c.nombre AS facultad'
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    return ['estado' => true, 'integrantes' => $integrantes];
  }

  //  Paso 4
  public function verificar4(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $datos = DB::table('Grupo')
      ->select([
        'presentacion',
        'objetivos',
        'servicios',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.nombre',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $listado = DB::table('Linea_investigacion')
      ->select([
        'id AS value',
        DB::raw("CONCAT(codigo, ' - ', nombre) AS label")
      ])
      ->get();

    return ['estado' => true, 'datos' => $datos, 'lineas' => $lineas, 'listado' => $listado];
  }

  public function registrar4(Request $request) {
    $sol = $this->validarSol($request, $request->input('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    DB::table('Grupo')
      ->where('id', '=', $request->input('id'))
      ->update([
        'presentacion' => $request->input('presentacion'),
        'objetivos' => $request->input('objetivos'),
        'servicios' => $request->input('servicios'),
        'step' => 5,
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'success', 'detail' => 'Datos guardados correctamente'];
  }

  //  Paso 5
  public function verificar5(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $proyectos = DB::table('Grupo_integrante AS a')
      ->join('Proyecto_integrante AS b', 'b.investigador_id', '=', 'a.investigador_id')
      ->join('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->join('Usuario_investigador AS d', 'd.id', '=', 'a.investigador_id')
      ->select([
        'c.id',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS nombre"),
        'c.codigo_proyecto',
        'c.titulo',
        'c.tipo_proyecto',
        'c.periodo',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->where('c.estado', '=', 1)
      ->where('a.condicion', '=', 'Titular')
      ->get();

    return ['estado' => true, 'proyectos' => $proyectos];
  }

  //  Paso 6
  public function verificar6(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $publicaciones = DB::table('Grupo_integrante AS a')
      ->join('Publicacion_autor AS b', 'b.investigador_id', '=', 'a.investigador_id')
      ->join('Publicacion AS c', 'c.id', '=', 'b.publicacion_id')
      ->join('Usuario_investigador AS d', 'd.id', '=', 'a.investigador_id')
      ->select([
        'c.id',
        DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS nombre"),
        'c.titulo',
        DB::raw("YEAR(c.fecha_publicacion) AS periodo"),
        'c.tipo_publicacion',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->where('c.estado', '=', 1)
      ->where('a.condicion', '=', 'Titular')
      ->get();

    return ['estado' => true, 'publicaciones' => $publicaciones];
  }

  //  Paso 7
  public function verificar7(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $datos = DB::table('Grupo')
      ->select([
        'infraestructura_ambientes',
        DB::raw("CONCAT('/minio/grupo-infraestructura-sgestion/', infraestructura_sgestion) AS url")
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $laboratorios = DB::table('Grupo_infraestructura AS a')
      ->join('Laboratorio AS b', 'b.id', '=', 'a.laboratorio_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.laboratorio',
        'b.responsable',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    return ['estado' => true, 'datos' => $datos, 'laboratorios' => $laboratorios];
  }

  public function registrar7(Request $request) {
    $sol = $this->validarSol($request, $request->input('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    if ($request->hasFile('file')) {

      $nameFile = Str::random(32) . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "grupo-infraestructura-sgestion", $nameFile);

      DB::table('Grupo')
        ->where('id', '=', $request->input('id'))
        ->update([
          'infraestructura_ambientes' => $request->input('infraestructura_ambientes'),
          'infraestructura_sgestion' => $nameFile,
          'step' => 8,
          'updated_at' => Carbon::now()
        ]);

      return ['message' => 'success', 'detail' => 'Datos guardados correctamente'];
    } else {
      $cuenta = DB::table('Grupo')
        ->where('id', '=', $request->input('id'))
        ->whereNull('infraestructura_sgestion')
        ->count();

      if ($cuenta > 0) {
        return ['message' => 'error', 'detail' => 'Necesita cargar un archivo'];
      } else {
        DB::table('Grupo')
          ->where('id', '=', $request->input('id'))
          ->update([
            'infraestructura_ambientes' => $request->input('infraestructura_ambientes'),
            'step' => 8,
            'updated_at' => Carbon::now()
          ]);

        return ['message' => 'success', 'detail' => 'Datos guardados correctamente'];
      }
    }
  }

  //  Paso 8
  public function verificar8(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $datos = DB::table('Grupo')
      ->select([
        'telefono',
        'anexo',
        'oficina',
        'direccion',
        'email',
        'web',
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    return ['estado' => true, 'datos' => $datos];
  }

  public function registrar8(Request $request) {
    $sol = $this->validarSol($request, $request->input('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    DB::table('Grupo')
      ->where('id', '=', $request->input('id'))
      ->update([
        'telefono' => $request->input('telefono'),
        'anexo' => $request->input('anexo'),
        'oficina' => $request->input('oficina'),
        'direccion' => $request->input('direccion'),
        'web' => $request->input('web'),
        'step' => 9,
      ]);

    return ['message' => 'success', 'detail' => 'Datos guardados correctamente'];
  }

  //  Paso 9
  public function verificar9(Request $request) {
    return ['estado' => true];
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }
  }

  public function registrar9(Request $request) {
    $count = DB::table('Grupo')
      ->where('id', '=', $request->input('id'))
      ->update([
        'estado' => 5
      ]);

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta solicitud ya ha sido registrada'];
    } else {
      return ['message' => 'success', 'detail' => 'Solicitud de creación de grupo enviada'];
    }
  }

  public function reporteSolicitud(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    } else {
      $grupo = DB::table('Grupo')
        ->select([
          'grupo_nombre',
          'grupo_nombre_corto',
          'telefono',
          'anexo',
          'oficina',
          'direccion',
          'web',
          'email',
          'presentacion',
          'objetivos',
          'servicios',
          'infraestructura_ambientes',
          DB::raw("CASE 
            WHEN infraestructura_sgestion IS NULL THEN 'No'
            ELSE 'Sí'
          END AS anexo"),
        ])
        ->where('id', '=', $request->query('id'))
        ->first();

      $integrantes = DB::table('Grupo_integrante AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
        ->select([
          'b.doc_numero',
          DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
          DB::raw("CASE 
          WHEN a.cargo IS NOT NULL THEN CONCAT(a.condicion, '(', a.cargo, ')')
          ELSE a.condicion
          END AS condicion"),
          'b.tipo',
          'c.nombre AS facultad'
        ])
        ->where('a.grupo_id', '=', $request->query('id'))
        ->get();

      $lineas = DB::table('Grupo_linea AS a')
        ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
        ->select([
          'a.id',
          'b.codigo',
          'b.nombre',
        ])
        ->where('a.grupo_id', '=', $request->query('id'))
        ->get();

      $laboratorios = DB::table('Grupo_infraestructura AS a')
        ->join('Laboratorio AS b', 'b.id', '=', 'a.laboratorio_id')
        ->select([
          'a.id',
          'b.codigo',
          'b.laboratorio',
          'b.responsable',
        ])
        ->where('a.grupo_id', '=', $request->query('id'))
        ->get();

      $pdf = Pdf::loadView('investigador.grupo.reporte', [
        'grupo' => $grupo,
        'integrantes' => $integrantes,
        'lineas' => $lineas,
        'laboratorios' => $laboratorios,
      ]);

      return $pdf->stream();
    }
  }

  public function reporteGrupo(Request $request) {
    $grupo = DB::table('Grupo')
      ->select([
        'grupo_nombre',
        'grupo_nombre_corto',
        'telefono',
        'anexo',
        'oficina',
        'direccion',
        'web',
        'email',
        'presentacion',
        'objetivos',
        'servicios',
        'infraestructura_ambientes',
        DB::raw("CASE 
            WHEN infraestructura_sgestion IS NULL THEN 'No'
            ELSE 'Sí'
          END AS anexo"),
        DB::raw("CASE (estado)
          WHEN -2 THEN 'Disuelto'
          WHEN -1 THEN 'Eliminado'
          WHEN 0 THEN 'No aprobado'
          WHEN 2 THEN 'Observado'
          WHEN 4 THEN 'Registrado'
          WHEN 5 THEN 'Enviado'
          WHEN 6 THEN 'En proceso'
          WHEN 12 THEN 'Reg. observado'
          ELSE 'Estado desconocido'
        END AS estado")
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $integrantes = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->select([
        'b.doc_numero',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
        DB::raw("CASE 
          WHEN a.cargo IS NOT NULL THEN CONCAT(a.condicion, '(', a.cargo, ')')
          ELSE a.condicion
          END AS condicion"),
        'b.tipo',
        'c.nombre AS facultad'
      ])
      ->whereNot('condicion', 'LIKE', 'Ex%')
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $lineas = DB::table('Grupo_linea AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.nombre',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $laboratorios = DB::table('Grupo_infraestructura AS a')
      ->join('Laboratorio AS b', 'b.id', '=', 'a.laboratorio_id')
      ->select([
        'a.id',
        'b.codigo',
        'b.laboratorio',
        'b.responsable',
      ])
      ->where('a.grupo_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.grupo.reporte_grupo', [
      'grupo' => $grupo,
      'integrantes' => $integrantes,
      'lineas' => $lineas,
      'laboratorios' => $laboratorios,
    ]);

    return $pdf->stream();
  }

  /**
   *  Otros
   */

  public function detalle(Request $request) {
    $detalle = DB::table('Grupo AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        'a.estado',
        'b.nombre AS facultad',
        'a.telefono',
        'a.anexo',
        'a.oficina',
        'a.direccion',
        'a.email',
        'a.web',
        'a.presentacion',
        'a.objetivos',
        'a.servicios',
      )
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $detalle;
  }

  public function listarMiembros(Request $request) {
    $miembros = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.grupo_integrante_id', '=', 'a.id')
      ->leftJoin('Proyecto_integrante_tipo AS e', 'e.id', '=', 'd.proyecto_integrante_tipo_id')
      ->select(
        'a.id',
        'a.investigador_id',
        'a.condicion',
        'a.cargo',
        'b.doc_numero',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombres'),
        'b.codigo_orcid',
        'b.google_scholar',
        'b.cti_vitae',
        'b.tipo',
        'c.nombre AS facultad',
        DB::raw('SUM(IF(e.nombre = "Tesista", 1, 0)) AS tesista'),
        DB::raw('COUNT(d.id) AS proyectos'),
        'a.fecha_inclusion',
        'a.fecha_exclusion'
      )
      ->where('a.grupo_id', '=', $request->query('grupo_id'));

    //  Tipo de miembro
    $miembros = $request->query('estado') == 1 ? $miembros->whereNot('a.condicion', 'LIKE', 'Ex%') : $miembros->where('a.condicion', 'LIKE', 'Ex%');

    $miembros = $miembros->groupBy('a.id')
      ->get();

    return ['data' => $miembros];
  }

  //  Miembros
  public function incluirMiembroData(Request $request) {

    switch ($request->query('tipo')) {
      case "estudiante":
      case "egresado":
        $investigador = DB::table('Usuario_investigador AS a')
          ->leftJoin('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
          ->leftJoin('Grupo AS c', function ($join) {
            $join->on('c.id', '=', 'b.grupo_id')
              ->where('b.condicion', 'NOT LIKE', 'Ex%');
          })
          ->select(
            'a.id',
            DB::raw('IFNULL(SUM(b.condicion NOT LIKE "Ex%"), 0) AS grupos'),
            DB::raw('GROUP_CONCAT(DISTINCT IF(b.condicion NOT LIKE "Ex%", c.grupo_nombre, NULL)) AS grupo_nombre')
          )
          ->where('a.codigo', '=', $request->query('codigo'))
          ->groupBy('a.codigo')
          ->first();

        if ($investigador == null) {
          return [
            'message' => 'success',
            'detail' => 'No pertenece a ningún grupo'
          ];
        } else if ($investigador->grupos > 0) {
          return [
            'message' => 'error',
            'detail' => 'Esta persona ya pertenece a un grupo de investigación: '
          ];
        } else {
          return [
            'message' => 'success',
            'detail' => 'No pertenece a ningún grupo'
          ];
        }
        break;
      case "titular":
        if ($request->query('investigador_id') == null) {
          return [
            'message' => 'warning',
            'detail' => 'No está registrado como investigador'
          ];
        } else {

          $investigador = DB::table('Usuario_investigador AS a')
            ->leftJoin('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
            ->leftJoin('Dependencia AS c', 'c.id', '=', 'a.dependencia_id')
            ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
            ->leftJoin('Instituto AS e', 'e.id', '=', 'a.instituto_id')
            ->leftJoin('Grupo AS f', function ($join) {
              $join->on('f.id', '=', 'b.grupo_id')
                ->where('b.condicion', 'NOT LIKE', 'Ex%');
            })
            ->select(
              'a.codigo_orcid',
              'c.dependencia',
              'c.id AS dependencia_id',
              'd.nombre AS facultad',
              'd.id AS facultad_id',
              'e.instituto',
              'e.id AS instituto_id',
              DB::raw('IFNULL(SUM(b.condicion NOT LIKE "Ex%"), 0) AS grupos'),
              DB::raw('GROUP_CONCAT(DISTINCT IF(b.condicion NOT LIKE "Ex%", f.grupo_nombre, NULL)) AS grupo_nombre')
            )
            ->where('a.id', '=', $request->query('investigador_id'))
            ->groupBy('a.id')
            ->first();

          if ($investigador->grupos > 0) {
            return [
              'message' => 'error',
              'detail' => 'Esta persona ya pertenece a un grupo de investigación: ' . $investigador->grupo_nombre
            ];
          } else if (in_array(null, [$investigador->codigo_orcid, $investigador->dependencia, $investigador->facultad, $investigador->instituto])) {
            return [
              'message' => 'warning',
              'detail' => 'Registro de investigador incompleto (necesita tener orcid, dependencia, facultad e instituto)'
            ];
          } else {
            return [
              'message' => 'success',
              'detail' => 'No pertenece a ningún grupo y tiene los datos de investigador completos',
              'codigo_orcid' => $investigador->codigo_orcid,
              'dependencia' => $investigador->dependencia,
              'facultad' => $investigador->facultad,
              'instituto' => $investigador->instituto,
              'dependencia_id' => $investigador->dependencia_id,
              'facultad_id' => $investigador->facultad_id,
              'instituto_id' => $investigador->instituto_id,
            ];
          }
        }
        break;
      default:
        return [
          'message' => 'error',
          'detail' => 'Error al solicitar información'
        ];
        break;
    }
  }

  public function agregarMiembro(Request $request) {
    $id = $request->input('grupo_id');
    $date = Carbon::now();
    $name = $id . "-formato_adhesion-" . $date->format('Ymd-His');

    if ($request->hasFile('file')) {
      $nameFile = $id . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "grupo-integrante-doc", $nameFile);

      switch ($request->input('tipo_registro')) {
        case 'externo':
          $investigador_id = DB::table('Usuario_investigador')
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
              'titulo_profesional' => $request->input('titulo_profesional'),
              'grado' => $request->input('grado'),
              'especialidad' => $request->input('especialidad'),
              'researcher_id' => $request->input('researcher_id'),
              'scopus_id' => $request->input('scopus_id'),
              'link' => $request->input('link'),
              'posicion_unmsm' => $request->input('posicion_unmsm'),
              'biografia' => $request->input('biografia'),
            ]);

          DB::table('Grupo_integrante')
            ->insertGetId([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $investigador_id,
              'tipo' => 'Externo',
              'condicion' => 'Adherente',
              'estado' => '1',
              'created_at' => $date,
              'updated_at' => $date
            ]);

          DB::table('Grupo_integrante_doc')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $investigador_id,
              'nombre' => 'Formato de adhesión',
              'key' => $name,
              'fecha' => $date,
              'estado' => 1
            ]);

          return [
            'message' => 'success',
            'detail' => 'Miembro registrado exitosamente'
          ];
          break;
        case 'estudiante':
        case 'egresado':

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
                'sexo' => $sumData->sexo,
                'email3' => $sumData->correo_electronico,
                'tipo' => $request->input('tipo'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tipo_investigador' => 'Estudiante'
              ]);
          }

          DB::table('Grupo_integrante')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $id_investigador,
              'condicion' => $request->input('condicion'),
              'estado' => '1',
              'created_at' => $date,
              'updated_at' => $date
            ]);

          DB::table('Grupo_integrante_doc')
            ->insert([
              'grupo_id' => $request->input('grupo_id'),
              'investigador_id' => $id_investigador,
              'nombre' => 'Formato de adhesión',
              'key' => $name,
              'fecha' => $date,
              'estado' => 1
            ]);

          return [
            'message' => 'success',
            'detail' => 'Miembro registrado exitosamente'
          ];
          break;
      }
    } else {
      if ($request->input('tipo_registro') == 'Titular' || $request->input('tipo_registro') == 'Colaborador') {
        $investigador = DB::table('Usuario_investigador AS a')
          ->select(
            'a.codigo',
            'a.dependencia_id',
            'a.facultad_id',
            'a.instituto_id',
          )
          ->where('a.id', '=', $request->input('investigador_id'))
          ->first();

        DB::table('Grupo_integrante')
          ->insert([
            'grupo_id' => $request->input('grupo_id'),
            'facultad_id' => $investigador->facultad_id,
            'dependencia_id' => $investigador->dependencia_id,
            'instituto_id' => $investigador->instituto_id,
            'investigador_id' => $request->input('investigador_id'),
            'codigo' => $investigador->codigo,
            'tipo' => 'DOCENTE PERMANENTE',
            'condicion' => $request->input('tipo_registro'),
            'estado' => '1',
            'created_at' => $date,
            'updated_at' => $date
          ]);

        return [
          'message' => 'success',
          'detail' => 'Miembro registrado exitosamente'
        ];
      } else {
        return [
          'message' => 'error',
          'detail' => 'Error cargando formato de adhesión'
        ];
      }
    }
  }

  public function eliminarMiembro(Request $request) {
    DB::table('Grupo_integrante')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return [
      'message' => 'info',
      'detail' => 'Miembro eliminado exitosamente'
    ];
  }

  public function getPaises() {
    $paises = DB::table('Pais')
      ->select([
        'name AS value'
      ])->get();

    return $paises;
  }

  //  Search
  public function searchDocenteRrhh(Request $request) {
    $investigadores = DB::table('Repo_rrhh AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.doc_numero', '=', 'a.ser_doc_id_act')
      ->leftJoin('Licencia AS c', 'c.investigador_id', '=', 'b.id')
      ->leftJoin('Licencia_tipo AS d', 'c.licencia_tipo_id', '=', 'd.id')
      ->leftJoin('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->select(
        DB::raw("CONCAT(TRIM(a.ser_cod_ant), ' | ', a.ser_doc_id_act, ' | ', a.ser_ape_pat, ' ', a.ser_ape_mat, ' ', a.ser_nom) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'ser_ape_pat',
        'ser_ape_mat',
        'ser_nom',
        'ser_cod_ant',
        'ser_doc_id_act',
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '1' THEN 'Principal'
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '2' THEN 'Asociado'
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '3' THEN 'Auxiliar'
          WHEN SUBSTRING_INDEX(ser_cat_act, '-', 1) = '4' THEN 'Jefe de Práctica'
          ELSE 'Sin categoría'
        END AS categoria"),
        DB::raw("CASE
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ser_cat_act, '-', 2), '-', -1) = '1' THEN 'Dedicación Exclusiva'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ser_cat_act, '-', 2), '-', -1) = '2' THEN 'Tiempo Completo'
          WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ser_cat_act, '-', 2), '-', -1) = '3' THEN 'Tiempo Parcial'
          ELSE 'Sin clase'
        END AS clase"),
        DB::raw("SUBSTRING_INDEX(ser_cat_act, '-', -1) AS horas"),
        'des_dep_cesantes',
        'e.nombre AS facultad',
        'e.id AS facultad_id'
      )
      ->where('des_tip_ser', 'LIKE', 'DOCENTE%')
      ->where(function ($query) {
        $query->where('c.fecha_fin', '<', date('Y-m-d'))
          ->orWhere('d.id', '=', 9)
          ->orWhereNull('d.tipo');
      })
      ->groupBy('ser_cod_ant')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
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
        DB::raw("CASE
          WHEN a.programa LIKE 'E.P.%' THEN 'Estudiante pregrado'
          ELSE 'Estudiante posgrado'
        END AS tipo"),
        'a.permanencia',
        'b.email3'
      )
      ->whereIn('a.permanencia', ['Activo', 'Reserva de Matricula'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $estudiantes;
  }

  public function searchEgresado(Request $request) {
    $egresados = DB::table('Repo_sum AS a')
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
        DB::raw("CASE
          WHEN a.programa LIKE 'E.P.%' THEN 'Estudiante pregrado'
          ELSE 'Estudiante posgrado'
        END AS tipo"),
        'a.permanencia',
        'a.ultimo_periodo_matriculado'
      )
      ->whereIn('a.permanencia', ['Egresado'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $egresados;
  }

  //  Líneas
  public function agregarLinea(Request $request) {
    $sol = $this->validarSol($request, $request->input('grupo_id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    DB::table('Grupo_linea')
      ->insert([
        'grupo_id' => $request->input('grupo_id'),
        'linea_investigacion_id' => $request->input('linea_investigacion_id')["value"],
      ]);

    return ['message' => 'success', 'detail' => 'Línea agregada correctamente'];
  }

  public function eliminarLinea(Request $request) {
    $sol = $this->validarSol($request, $request->query('grupo_id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    DB::table('Grupo_linea')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Línea eliminada correctamente'];
  }

  //  Laboratorios
  public function searchLaboratorio(Request $request) {
    $laboratorios = DB::table('Laboratorio AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo), ' | ', a.laboratorio, ' | ', b.nombre) AS value"),
        'a.id',
        'a.codigo',
        'a.laboratorio',
        'a.responsable',
        'a.categoria_uso',
        'a.ubicacion',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $laboratorios;
  }

  public function agregarLaboratorio(Request $request) {
    $sol = $this->validarSol($request, $request->input('grupo_id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    $count = DB::table('Grupo_infraestructura')
      ->where('grupo_id', '=', $request->input('grupo_id'))
      ->where('laboratorio_id', '=', $request->input('id'))
      ->count();

    if ($count == 0) {
      DB::table('Grupo_infraestructura')
        ->insert([
          'grupo_id' => $request->input('grupo_id'),
          'laboratorio_id' => $request->input('id'),
          'categoria' => 'laboratorio',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

      return ['message' => 'success', 'detail' => 'Laboratorio agregado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'El laboratorio seleccionado ya figura en su grupo'];
    }
  }

  public function eliminarLaboratorio(Request $request) {
    $sol = $this->validarSol($request, $request->query('id'));
    if (sizeof($sol) > 0) {
      return ['estado' => false, 'message' => $sol];
    }

    DB::table('Grupo_infraestructura')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Laboratorio eliminado correctamente'];
  }

  public function excluirMiembro(Request $request) {
    DB::table('Grupo_integrante')
      ->where('id', '=', $request->input('id'))
      ->update([
        'condicion' => "Ex " . $request->input('condicion'),
        'fecha_exclusion' => $request->input('fecha_exclusion'),
        'resolucion_exclusion' => $request->input('resolucion_exclusion'),
        'resolucion_exclusion_fecha' => $request->input('resolucion_exclusion_fecha'),
        'resolucion_oficina_exclusion' => $request->input('resolucion_oficina_exclusion'),
        'observacion_excluir' => $request->input('observacion_excluir'),
        'estado' => "-2"
      ]);

    return [
      'message' => 'info',
      'detail' => 'Miembro excluído exitosamente'
    ];
  }

  public function visualizarMiembro(Request $request) {
    $informacion = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Dependencia AS c', 'c.id', '=', 'b.dependencia_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.codigo',
        'b.doc_numero',
        'b.docente_categoria',
        'c.dependencia',
        'd.nombre AS facultad',
        'b.codigo_orcid',
        'b.researcher_id',
        'b.scopus_id',
        'b.biografia',
        'a.resolucion_oficina',
        'a.fecha_inclusion',
        'a.resolucion',
        'a.resolucion_fecha',
        'a.observacion',
        //  Exclusion
        'a.resolucion_oficina_exclusion',
        'a.fecha_exclusion',
        'a.resolucion_exclusion',
        'a.resolucion_exclusion_fecha',
        'a.observacion_excluir',
        //  Para adherente
        'b.tipo_investigador_estado',
        'b.tipo',
        'b.telefono_movil',
        'b.telefono_casa',
        'b.telefono_trabajo'
      ])
      ->where('a.id', '=', $request->query('grupo_integrante_id'))
      ->first();

    $proyectos = DB::table('Grupo_integrante AS a')
      ->join('Proyecto_integrante AS b', 'b.grupo_integrante_id', '=', 'a.id')
      ->join('Proyecto AS c', 'c.id', '=', 'b.proyecto_id')
      ->leftJoin('Proyecto_integrante_tipo AS d', 'd.id', '=', 'b.proyecto_integrante_tipo_id')
      ->select([
        'c.codigo_proyecto',
        'c.tipo_proyecto',
        'c.titulo',
        'd.nombre AS condicion',
        'c.periodo',
        'c.estado'
      ])
      ->where('a.id', '=', $request->query('grupo_integrante_id'))
      ->where('c.estado', '=', 1)
      ->groupBy('c.id')
      ->get();

    return ['informacion' => $informacion, 'proyectos' => $proyectos];
  }

  public function listarProyectos(Request $request) {
    $proyectos = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante AS c', 'c.investigador_id', '=', 'b.id')
      ->join('Proyecto_integrante_tipo AS d', 'd.id', '=', 'c.proyecto_integrante_tipo_id')
      ->join('Proyecto AS e', 'e.id', '=', 'c.proyecto_id')
      ->select([
        'e.id',
        'e.tipo_proyecto',
        'e.codigo_proyecto',
        'e.titulo',
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS responsable"),
        'e.periodo',
        'e.resolucion_rectoral',
        DB::raw("CASE(e.autorizacion_grupo)
            WHEN 1 THEN 'Sí'
          ELSE 'No' END AS autorizacion_grupo"),
        DB::raw("CASE(e.estado)
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
      ->where('a.grupo_id', '=', $request->query('id'))
      ->whereNot('a.condicion', 'LIKE', 'Ex%')
      ->whereIn('d.nombre', ['Responsable', 'Coordinador', 'Asesor'])
      ->orderByDesc('e.created_at')
      ->get();

    return $proyectos;
  }

  public function autorizarProyecto(Request $request) {
    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'autorizacion_grupo' => $request->input('autorizacion_grupo')
      ]);

    return ['message' => 'info', 'detail' => 'Cambios guardados correctamente'];
  }
}

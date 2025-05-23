<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use App\Mail\Investigador\Publicaciones\SolicitarSerAutor;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicacionesUtilsController extends S3Controller {

  public function observacion(Request $request) {
    $obs = DB::table('Publicacion')
      ->select([
        'estado',
        'observaciones_usuario'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    return $obs;
  }

  public function eliminarPublicacion(Request $request) {
    $count = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->where('a.publicacion_id', '=', $request->query('id'))
      ->where('b.estado', '=', 6)
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($count > 0) {

      DB::table('Publicacion_autor')
        ->where('publicacion_id', '=', $request->query('id'))
        ->delete();

      DB::table('Publicacion_index')
        ->where('publicacion_id', '=', $request->query('id'))
        ->delete();

      DB::table('Publicacion_wos')
        ->where('publicacion_id', '=', $request->query('id'))
        ->delete();

      DB::table('Publicacion_palabra_clave')
        ->where('publicacion_id', '=', $request->query('id'))
        ->delete();

      DB::table('Publicacion_proyecto')
        ->where('publicacion_id', '=', $request->query('id'))
        ->delete();

      DB::table('Publicacion')
        ->where('id', '=', $request->query('id'))
        ->delete();

      return ['message' => 'info', 'detail' => 'Publicación eliminada correctamente'];
    } else {
      return ['message' => 'warning', 'detail' => 'No se puede eliminar esta publicación'];
    }
  }

  public function eliminarFiliacion(Request $request) {
    $publicacionId = $request->query('id');
    $investigadorId = $request->attributes->get('token_decoded')->investigador_id;

    // Verificar si la publicación cuenta con filiación UNMSM
    $count = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->where('a.publicacion_id', '=', $publicacionId)
      ->where('a.filiacion', '=', 0) // Sin filiación UNMSM
      ->where('a.investigador_id', '=', $investigadorId)
      ->count();

    if ($count == 0) {
      return [
        'message' => 'error',
        'detail' => 'Esta publicación no se pueden hacer más cambios.'
      ];
    }

    // Eliminar la publicación si no tiene filiación UNMSM
    $affectedRows = DB::table('Publicacion')
      ->where('id', '=', $publicacionId)
      ->update([
        'estado' => -1,
        'updated_at' => Carbon::now(),
      ]);

    if ($affectedRows > 0) {
      return [
        'message' => 'info',
        'detail' => 'Se eliminó correctamente la publicación que no cuenta con filiación UNMSM.'
      ];
    }

    return [
      'message' => 'error',
      'detail' => 'No se pudo eliminar la publicación, verifique los datos e intente nuevamente.'
    ];
  }


  /*
  |-----------------------------------------------------------
  | Solicitar ser incluído como autor
  |-----------------------------------------------------------
  |
  | Funciones para solicitar inclusión como autor en el caso
  | de que la publicación que uno quiera registrar ya esté
  | registrada por otro investigador
  |
  */

  public function listadoTitulos(Request $request) {
    $titulos = DB::table('Publicacion')
      ->select([
        'id',
        'titulo AS value',
      ])
      ->where('estado', '=', 1)
      ->having('titulo', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $titulos;
  }

  public function listadoDois(Request $request) {
    $dois = DB::table('Publicacion')
      ->select([
        'id',
        'doi AS value',
      ])
      ->where('estado', '=', 1)
      ->having('doi', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $dois;
  }

  public function listadoRevistas(Request $request) {
    $revistas = DB::table('Publicacion_revista')
      ->select(
        DB::raw("CONCAT(issn, ' | ', issne, ' | ', revista) AS value"),
        'issn',
        'issne',
        'revista',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $revistas;
  }

  public function infoPublicacion(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select()
      ->where('id', '=', $request->query('id'))
      ->first();

    $palabras_clave = DB::table('Publicacion_palabra_clave')
      ->select([
        'clave AS value'
      ])
      ->where('publicacion_id', '=', $request->query('id'))
      ->get();

    $index = DB::table('Publicacion_index AS a')
      ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
      ->select([
        'b.nombre AS value'
      ])
      ->where('a.publicacion_id', '=', $request->query('id'))
      ->get();

    $proyectos = DB::table('Publicacion_proyecto')
      ->select([
        'codigo_proyecto',
        'nombre_proyecto',
        'entidad_financiadora'
      ])
      ->where('publicacion_id', '=', $request->query('id'))
      ->get();

    $autores = DB::table('Publicacion_autor')
      ->select([
        'autor',
        'categoria',
        'tipo'
      ])
      ->where('publicacion_id', '=', $request->query('id'))
      ->get();

    //  Incluir
    $publicacion->palabras_clave = $palabras_clave;
    $publicacion->index = $index;
    $publicacion->proyectos = $proyectos;
    $publicacion->autores = $autores;

    return $publicacion;
  }

  public function solicitarInclusion(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'id',
        'titulo'
      ])
      ->where('id', '=', $request->input('id'))
      ->first();

    $investigador = DB::table('Usuario_investigador')
      ->select([
        DB::raw("CONCAT(apellido1, ' ', apellido2, ', ', nombres) AS nombres"),
        'doc_tipo',
        'doc_numero'
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    Mail::to('dei.vrip@unmsm.edu.pe')->cc('dgitt.vrip@unmsm.edu.pe')->send(new SolicitarSerAutor($publicacion, $investigador));

    return ['message' => 'info', 'detail' => 'Solicitud enviada'];
  }

  public function verificarTituloUnico(Request $request) {
    $count = DB::table('Publicacion')
      ->where('titulo', '=', $request->input('titulo'))
      ->count();

    return $count == 0;
  }
  /*
  |-----------------------------------------------------------
  | Pasos 2, 3 y 4
  |-----------------------------------------------------------
  |
  | Funciones para los pasos 2, 3 y 4 de cada publicación, ya
  | que estos se repiten.
  |
  */

  //  Paso 2
  public function proyectos_asociados(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $proyectos = DB::table('Publicacion_proyecto AS a')
        ->leftJoin('File AS b', function (JoinClause $join) {
          $join->on('b.tabla_id', '=', 'a.id')
            ->where('b.tabla', '=', 'Publicacion_proyecto')
            ->where('b.recurso', '=', 'DOCUMENTO_ADJUNTO')
            ->where('b.estado', '=', 20);
        })
        ->select([
          'a.id',
          'a.codigo_proyecto',
          'a.nombre_proyecto',
          'a.entidad_financiadora',
          DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url"),
        ])
        ->where('a.publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $proyectos;
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function proyectos_registrados(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_descripcion AS b', function ($join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.codigo', '=', 'fuente_financiadora');
      })
      ->select(
        DB::raw("CONCAT(a.codigo_proyecto, ' | ', a.titulo) AS value"),
        'a.id AS proyecto_id',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("IFNULL(b.detalle, 'UNMSM') AS entidad_financiadora")
      )
      ->whereNotNull('codigo_proyecto')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $proyectos;
  }

  public function agregarProyecto(Request $request) {
    $pub = DB::table('Publicacion')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->first();

    if (!$pub) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    $pub_proyecto = DB::table('Publicacion_proyecto')
      ->insertGetId([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => $request->input('proyecto_id') == null ? 'EXTERNO' : 'INTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
      $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

      $this->uploadFile($request->file('file'), "publicacion", $nameFile);

      DB::table('File')
        ->insert([
          'tabla_id' => $pub_proyecto,
          'tabla' => 'Publicacion_proyecto',
          'bucket' => 'publicacion',
          'key' => $nameFile,
          'recurso' => 'DOCUMENTO_ADJUNTO',
          'estado' => 20,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }

    //  Audit
    $audit = json_decode($pub->audit ?? "[]");

    $audit[] = [
      'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
      'nombres' => $request->attributes->get('token_decoded')->nombres,
      'apellidos' => $request->attributes->get('token_decoded')->apellidos,
      'accion' => 'Proyecto agregado'
    ];

    $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->update([
        'step' => 2,
        'audit' => $audit
      ]);

    return ['message' => 'success', 'detail' => 'Proyecto agregado exitosamente'];
  }

  public function eliminarProyecto(Request $request) {
    $count = DB::table('Publicacion_proyecto AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->where('a.id', '=', $request->query('proyecto_id'))
      ->whereIn('b.estado', [2, 6])
      ->count();

    if ($count == 0) {

      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_proyecto')
      ->where('id', '=', $request->query('proyecto_id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Proyecto eliminado de la lista exitosamente'];
  }

  //  Paso 3
  public function listarAutores(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $autores = DB::table('Publicacion_autor AS a')
        ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.id',
          'a.presentado',
          'a.categoria',
          'a.autor',
          DB::raw("COALESCE(b.tipo, 'Externo') AS tipo"),
          DB::raw("COALESCE(CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres), 
                  CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres)) AS nombres"),
          DB::raw("CASE(a.filiacion)
            WHEN 1 THEN 'Sí'
            WHEN 0 THEN 'No'
          ELSE null END AS filiacion"),
          DB::raw("CASE(a.filiacion_unica)
            WHEN 1 THEN 'Sí'
            WHEN 0 THEN 'No'
          ELSE null END AS filiacion_unica"),
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $cumple = 0;
      $pub = DB::table('Publicacion')
        ->select([
          'tipo_publicacion'
        ])
        ->where('id', '=', $request->query('publicacion_id'))
        ->first();

      if ($pub->tipo_publicacion == 'tesis-asesoria') {
        $cumple = DB::table('Publicacion_autor')
          ->where('publicacion_id', '=', $request->query('publicacion_id'))
          ->where(function ($query) {
            $query->whereNull('autor')
              ->orWhereNull('filiacion');
          })
          ->count();
      } else {
        $cumple = DB::table('Publicacion_autor')
          ->where('publicacion_id', '=', $request->query('publicacion_id'))
          ->where(function ($query) {
            $query->whereNull('autor')
              ->orWhereNull('filiacion')
              ->orWhereNull('filiacion_unica');
          })
          ->count();
      }


      return ['listado' => $autores, 'cumple' => $cumple > 0 ? false : true];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function searchDocenteRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'DOCENTE%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchEstudianteRegistrado(Request $request) {
    $investigadores = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.programa',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchExternoRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'EXTERNO%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function agregarAutor(Request $request) {
    $pub = DB::table('Publicacion')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->first();

    if (!$pub) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    switch ($request->input('tipo')) {
      case "externo":
        DB::table('Publicacion_autor')
          ->insert([
            'publicacion_id' => $request->input('publicacion_id'),
            'tipo' => $request->input('tipo'),
            'nombres' => $request->input('nombres'),
            'apellido1' => $request->input('apellido1'),
            'apellido2' => $request->input('apellido2'),
            'autor' => $request->input('autor'),
            'categoria' => $request->input('categoria'),
            'filiacion' => $request->input('filiacion'),
            'filiacion_unica' => $request->input('filiacion_unica'),
            'presentado' => 0,
            'estado' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);
        break;
      case "estudiante":
        $id_investigador = $request->input('investigador_id');

        if ($id_investigador == null) {
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
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
              'tipo_investigador' => 'Estudiante',
              'tipo' => 'Estudiante'
            ]);
        }

        $cuenta_autor = DB::table('Publicacion_autor')
          ->where('publicacion_id', '=', $request->input('publicacion_id'))
          ->where('investigador_id', '=', $id_investigador)
          ->count();

        if ($cuenta_autor == 0) {
          DB::table('Publicacion_autor')->insert([
            'publicacion_id' => $request->input('publicacion_id'),
            'investigador_id' => $id_investigador,
            'tipo' => "interno",
            'autor' => $request->input('autor'),
            'categoria' => $request->input('categoria'),
            'filiacion' => $request->input('filiacion'),
            'filiacion_unica' => $request->input('filiacion_unica'),
            'presentado' => 0,
            'estado' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);
        } else {
          return ['message' => 'warning', 'detail' => 'Este autor ya está registrado'];
        }
        break;
      case "interno":
        $cuenta_autor = DB::table('Publicacion_autor')
          ->where('publicacion_id', '=', $request->input('publicacion_id'))
          ->where('investigador_id', '=', $request->input('investigador_id'))
          ->count();

        if ($cuenta_autor == 0) {
          DB::table('Publicacion_autor')->insert([
            'publicacion_id' => $request->input('publicacion_id'),
            'investigador_id' => $request->input('investigador_id'),
            'tipo' => "interno",
            'autor' => $request->input('autor'),
            'categoria' => $request->input('categoria'),
            'filiacion' => $request->input('filiacion'),
            'filiacion_unica' => $request->input('filiacion_unica'),
            'presentado' => 0,
            'estado' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);
        } else {
          return ['message' => 'warning', 'detail' => 'Este autor ya está registrado'];
        }
        break;
      default:
        break;
    }

    $audit = json_decode($pub->audit ?? "[]");

    $audit[] = [
      'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
      'nombres' => $request->attributes->get('token_decoded')->nombres,
      'apellidos' => $request->attributes->get('token_decoded')->apellidos,
      'accion' => 'Autor añadido'
    ];

    $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->update([
        'step' => 3,
        'audit' => $audit
      ]);

    return ['message' => 'success', 'detail' => 'Autor agregado exitosamente'];
  }

  public function editarAutor(Request $request) {
    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_autor')
      ->where('id', '=', $request->input('id'))
      ->update([
        'autor' => $request->input('autor'),
        'categoria' => $request->input('categoria'),
        'filiacion' => $request->input('filiacion'),
        'filiacion_unica' => $request->input('filiacion_unica'),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Datos del autor editado exitosamente'];
  }

  public function eliminarAutor(Request $request) {
    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_autor')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Autor eliminado de la lista exitosamente'];
  }

  //  Paso 4
  public function enviarPublicacion(Request $request) {
    if ($request->hasFile('file')) {
      //  Audit del investigador:
      $pub = DB::table('Publicacion')
        ->select([
          'audit',
          DB::raw("CASE(estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
        ])
        ->where('id', '=', $request->input('publicacion_id'))
        ->first();

      $audit = json_decode($pub->audit ?? "[]");

      $audit[] = [
        'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
        'nombres' => $request->attributes->get('token_decoded')->nombres,
        'apellidos' => $request->attributes->get('token_decoded')->apellidos,
        'accion' => 'Envío de publicación (estado anterior: ' . $pub->estado . ')'
      ];

      $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

      $count1 = DB::table('Publicacion')
        ->where('id', '=', $request->input('publicacion_id'))
        ->whereIn('estado', [2, 6])
        ->update([
          'step' => 4,
          'estado' => 5,
          'audit' => $audit
        ]);

      if ($count1 == 0) {
        return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
      } else {
        $date = Carbon::now();
        $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
        $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

        $this->uploadFile($request->file('file'), "publicacion", $nameFile);

        DB::table('File')
          ->where('tabla_id', '=', $request->input('publicacion_id'))
          ->where('tabla', 'Publicacion')
          ->where('recurso', 'ANEXO')
          ->update([
            'estado' => -1,
          ]);

        DB::table('File')
          ->insert([
            'tabla_id' => $request->input('publicacion_id'),
            'tabla' => 'Publicacion',
            'bucket' => 'publicacion',
            'key' => $nameFile,
            'recurso' => 'ANEXO',
            'estado' => 20,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);

        return ['message' => 'success', 'detail' => 'Publicación enviada correctamente'];
      }
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar el archivo'];
    }
  }

  public function reporte(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      switch ($request->query('tipo')) {
        case "articulo":
          $util = new ArticulosController();
          return $util->reporte($request);
          break;
        case "libro":
          $util = new LibrosController();
          return $util->reporte($request);
          break;
        case "capitulo_libro":
          $util = new CapitulosLibrosController();
          return $util->reporte($request);
          break;
        case "tesis":
          $util = new TesisPropiasController();
          return $util->reporte($request);
          break;
        case "tesis_asesoria":
        case "tesis-asesoria":
          $util = new TesisAsesoriaController();
          return $util->reporte($request);
          break;
        case "evento":
          $util = new EventoController();
          return $util->reporte($request);
          break;
        default:
          break;
      }
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  /*
  |-----------------------------------------------------------
  | Listado de data
  |-----------------------------------------------------------
  |
  | Listado de revistas, países, etc. Usados al momento de 
  | registrar más de un tipo de controlador de publicación.
  |
  */

  public function listadoRevistasIndexadas() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select([
        'id AS value',
        'nombre AS label',
      ])
      ->where('estado', '!=', 0)
      ->get();

    return $revistas;
  }

  public function getPaises() {
    $paises = DB::table('Pais')
      ->select(['name AS value'])
      ->get();
    return $paises;
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Query\JoinClause;
use App\Http\Controllers\Admin\Estudios\Publicaciones\PublicacionesUtilsController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\ArticulosController;

class GestionSubvencionesController extends S3Controller {
  public function listado() {
    $listado = DB::table('Subvencion_apc as a')
      ->join('Publicacion as b', 'b.id', '=', 'a.publicacion_id')
      ->join('Usuario_investigador as c', 'c.id', '=', 'a.investigador_id')
      ->leftJoin('Facultad as d', 'd.id', '=', 'c.facultad_id')
      ->select(
        'a.id',
        'a.codigo',
        'b.titulo as titulo_publicacion',
        DB::raw("CONCAT(c.apellido1,' ',c.apellido2,', ',c.nombres) as responsable"),
        'd.nombre as facultad',
        'a.periodo',
        'a.solicitud_subvencion',
        'a.monto_solicitado',
        'a.moneda',
        'a.estado',
        'a.fecha_envio',
        'a.resolucion',
        'a.created_at',
        'a.updated_at'
      )
      ->orderBy('a.id', 'DESC')
      ->get();

    return $listado;
  }

  public function searchPublicacion(Request $request) {
    return DB::table('Publicacion')
      ->select(['id AS value', DB::raw("CONCAT(codigo_registro, ' - ', titulo) AS label")])
      ->whereNotNull('codigo_registro')
      ->where('codigo_registro', 'LIKE', '%' . $request->query('query') . '%')
      ->where('tipo_publicacion', '=', 'articulo')
      ->limit(10)
      ->get();
  }

  public function datosPaso1(Request $request) {
    $id = $request->query('id');
    $publicacionData = null;

    if ($id) {
      $pub = DB::table('Publicacion')
        ->select([
          'id',
          'codigo_registro',
          'doi',
          'art_tipo',
          'titulo',
          'resumen',
          'pagina_inicial',
          'pagina_final',
          'fecha_publicacion',
          'publicacion_nombre',
          'issn',
          'issn_e',
          'volumen',
          'edicion',
          'url',
        ])
        ->where('id', '=', $id)
        ->first();

      if ($pub) {
        // Palabras clave en formato [{ label: "palabra" }]
        $palabras_clave = DB::table('Publicacion_palabra_clave')
          ->select(['clave AS label'])
          ->where('publicacion_id', '=', $id)
          ->get();

        // Revistas indexadas en formato [{ value: id, label: nombre }]
        $indexada = DB::table('Publicacion_index AS a')
          ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
          ->select([
            'b.id AS value',
            'b.nombre AS label'
          ])
          ->where('a.publicacion_id', '=', $id)
          ->get();

        // Colección WOS en formato [{ value: id, label: nombre }]
        $indexada_wos = DB::table('Publicacion_wos AS a')
          ->join('Publicacion_db_wos AS b', 'b.id', '=', 'a.publicacion_db_wos_id')
          ->select([
            'b.id AS value',
            'b.nombre AS label'
          ])
          ->where('a.publicacion_id', '=', $id)
          ->get();

        // Cuartil almacenado en Publicacion_descripcion
        $cuartilDetalle = DB::table('Publicacion_descripcion')
          ->where('publicacion_id', '=', $id)
          ->where('codigo', '=', 'cuartil')
          ->value('detalle');

        $publicacionData = [
          'id' => $pub->id,
          'codigo_registro' => $pub->codigo_registro,
          'doi' => $pub->doi,
          'art_tipo' => $pub->art_tipo,
          'titulo' => $pub->titulo,
          'pagina_inicial' => $pub->pagina_inicial,
          'pagina_final' => $pub->pagina_final,
          'fecha_publicacion' => $pub->fecha_publicacion,
          'publicacion_nombre' => $pub->publicacion_nombre,
          'issn' => $pub->issn,
          'issn_e' => $pub->issn_e,
          'volumen' => $pub->volumen,
          'edicion' => $pub->edicion,
          'url' => $pub->url,
          'palabras_clave' => $palabras_clave,
          'indexada' => $indexada,
          'wos' => $indexada_wos,
          'cuartil' => $cuartilDetalle ? ['value' => $cuartilDetalle] : null,
        ];
      }
    }

    // Listados generales desde utilitarios
    $utils = new PublicacionesUtilsController();
    $revistas = $utils->listadoRevistasIndexadas();
    $wos = $utils->listadoWos();

    return [
      'publicacion' => $publicacionData,
      'revistas'    => $revistas,
      'wos'         => $wos
    ];
  }

  public function registrarPaso1(Request $request) {
    $date = Carbon::now();
    $publicacion_id = $request->input('id');

    // Extraer valores simples o provenientes de selectores ({ value, label })
    $art_tipo = is_array($request->input('art_tipo')) ? $request->input('art_tipo.value') : $request->input('art_tipo');
    $cuartil  = is_array($request->input('cuartil')) ? $request->input('cuartil.value') : $request->input('cuartil');

    // --- CASO 1: REGISTRO NUEVO ---
    if ($publicacion_id == null) {
      $util = new PublicacionesUtilsController();

      if (!$util->verificarTituloUnico($request)) {
        return [ 'message' => 'error', 'detail'  => 'Está usando el título de una publicación que ya está registrada'];
      }

      $audit = [
        [
          'fecha' => $date->format('Y-m-d H:i:s'),
          'nombres' => $request->attributes->get('token_decoded')->nombre ?? 'Admin',
          'apellidos' => $request->attributes->get('token_decoded')->apellidos ?? 'Sistema',
          'accion' => 'Registro de subvención APC - Paso 1'
        ]
      ];

      $publicacion_id = DB::table('Publicacion')->insertGetId([
        'doi' => $request->input('doi'),
        'art_tipo' => $art_tipo,
        'titulo' => $request->input('titulo'),
        'pagina_inicial' => $request->input('pagina_inicial'),
        'pagina_final' => $request->input('pagina_final'),
        'fecha_publicacion' => $request->input('fecha_publicacion'),
        'publicacion_nombre' => $request->input('publicacion_nombre'),
        'issn' => $request->input('issn'),
        'issn_e' => $request->input('issn_e'),
        'volumen' => $request->input('volumen'),
        'edicion' => $request->input('edicion'),
        'url' => $request->input('url'),
        'validado' => 0,
        'step' => 1,
        'tipo_publicacion' => 'articulo',
        'audit' => json_encode($audit, JSON_UNESCAPED_UNICODE),
        'estado' => 6,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      // Guardar Palabras Clave
      if ($request->has('palabras_clave') && is_array($request->input('palabras_clave'))) {
        foreach ($request->input('palabras_clave') as $palabra) {
          DB::table('Publicacion_palabra_clave')->insert([
            'publicacion_id' => $publicacion_id,
            'clave' => is_array($palabra) ? $palabra['label'] : $palabra
          ]);
        }
      }

      // Guardar Indexaciones
      if ($request->has('indexada') && is_array($request->input('indexada'))) {
        foreach ($request->input('indexada') as $indexada) {
          DB::table('Publicacion_index')->insert([
            'publicacion_id' => $publicacion_id,
            'publicacion_db_indexada_id' => is_array($indexada) ? $indexada['value'] : $indexada,
            'created_at' => $date,
            'updated_at' => $date
          ]);
        }
      }

      // Guardar WOS
      if ($request->has('wos') && is_array($request->input('wos'))) {
        foreach ($request->input('wos') as $wos) {
          DB::table('Publicacion_wos')->insert([
            'publicacion_id' => $publicacion_id,
            'publicacion_db_wos_id' => is_array($wos) ? $wos['value'] : $wos,
            'created_at' => $date,
            'updated_at' => $date
          ]);
        }
      }

      // Guardar Cuartil
      if ($cuartil) {
        DB::table('Publicacion_descripcion')->updateOrInsert(
          ['publicacion_id' => $publicacion_id, 'codigo' => 'cuartil'],['detalle' => $cuartil]
        );
      }

      return [
        'message' => 'success',
        'detail' => 'Datos de la publicación para subvención APC registrados correctamente',
        'id' => $publicacion_id
      ];
    }

    // --- CASO 2: ACTUALIZACIÓN ---
    $pub = DB::table('Publicacion')
      ->select(['audit'])
      ->where('id', '=', $publicacion_id)
      ->first();

    $audit = json_decode($pub->audit ?? "[]", true);
    $audit[] = [
      'fecha' => $date->format('Y-m-d H:i:s'),
      'nombres' => $request->attributes->get('token_decoded')->nombre ?? 'Admin',
      'apellidos' => $request->attributes->get('token_decoded')->apellidos ?? 'Sistema',
      'accion' => 'Actualización de datos APC - Paso 1'
    ];

    DB::table('Publicacion')
      ->where('id', '=', $publicacion_id)
      ->update([
        'doi' => $request->input('doi'),
        'art_tipo' => $art_tipo,
        'titulo' => $request->input('titulo'),
        'pagina_inicial' => $request->input('pagina_inicial'),
        'pagina_final' => $request->input('pagina_final'),
        'fecha_publicacion' => $request->input('fecha_publicacion'),
        'publicacion_nombre' => $request->input('publicacion_nombre'),
        'issn' => $request->input('issn'),
        'issn_e' => $request->input('issn_e'),
        'volumen' => $request->input('volumen'),
        'edicion' => $request->input('edicion'),
        'url' => $request->input('url'),
        'audit' => json_encode($audit, JSON_UNESCAPED_UNICODE),
        'updated_at' => $date
      ]);

    // Reemplazar Palabras Clave
    DB::table('Publicacion_palabra_clave')
      ->where('publicacion_id', '=', $publicacion_id)
      ->delete();

    if ($request->has('palabras_clave') && is_array($request->input('palabras_clave'))) {
      foreach ($request->input('palabras_clave') as $palabra) {
        DB::table('Publicacion_palabra_clave')->insert([
          'publicacion_id' => $publicacion_id,
          'clave' => is_array($palabra) ? $palabra['label'] : $palabra
        ]);
      }
    }

    // Reemplazar Indexaciones
    DB::table('Publicacion_index')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();

    if ($request->has('indexada') && is_array($request->input('indexada'))) {
      foreach ($request->input('indexada') as $indexada) {
        DB::table('Publicacion_index')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_indexada_id' => is_array($indexada) ? $indexada['value'] : $indexada,
          'created_at' => $date,
          'updated_at' => $date
        ]);
      }
    }

    // Reemplazar WOS
    DB::table('Publicacion_wos')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();

    if ($request->has('wos') && is_array($request->input('wos'))) {
      foreach ($request->input('wos') as $wos) {
        DB::table('Publicacion_wos')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_wos_id' => is_array($wos) ? $wos['value'] : $wos,
          'created_at' => $date,
          'updated_at' => $date
        ]);
      }
    }

    // Actualizar/Insertar Cuartil
    if ($cuartil) {
      DB::table('Publicacion_descripcion')->updateOrInsert(
        ['publicacion_id' => $publicacion_id, 'codigo' => 'cuartil'],
        ['detalle' => $cuartil]
      );
    }

    return [
      'message' => 'success',
      'detail' => 'Datos de la publicación para subvención APC actualizados correctamente',
      'id' => $publicacion_id
    ];
  }

  // Registor de Paso 2
  public function proyectos_asociados(Request $request) {
    $publicacionId = $request->query('publicacion_id') ?? $request->query('id');

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
        ->where('a.publicacion_id', '=', $publicacionId)
        ->get();

    return response()->json($proyectos);
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
    $token = $request->attributes->get('token_decoded');

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
        'investigador_id' => $token->investigador_id ?? null,
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
      'nombres' => $token->nombre,
      'apellidos' => $token->apellidos,
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

  // Registro de paso 3 (Autores)
  public function listarAutores(Request $request) {
    $publicacion_id = $request->query('publicacion_id');
    if (!$publicacion_id) {
      return response()->json(['error' => 'El parámetro publicacion_id es requerido'], 400);
    }

    $pub = DB::table('Publicacion')
      ->select(['tipo_publicacion'])
      ->where('id', '=', $publicacion_id)
      ->first();

    if (!$pub) {
      return response()->json(['error' => 'Publicación no encontrada'], 404);
    }

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
    
    $totalAutores = count($autores);

    if ($totalAutores === 0) {
        // Si no hay autores registrados, NO cumple los requisitos
      $cumple = false;
    } else {
        // Si hay autores, verificar que ninguno tenga campos requeridos vacíos
      $incompletos = DB::table('Publicacion_autor')
        ->where('publicacion_id', '=', $publicacion_id)
        ->where(function($query){
          $query->whereNull('autor')
                ->orWhereNull('filiacion')
                ->orWhereNull('filiacion_unica');
        })
        ->count();
      
        $cumple = ($incompletos === 0);
    }

    return [
      'listado' => $autores,
      'cumple'  => $cumple
    ];
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
      ->select(['audit'])
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->first();

    if (!$pub) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    $categoria = $request->input('categoria');
    $presentado = in_array($categoria, ['Autor de correspondencia', 'Primer autor']) ? 1 : 0;

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
              'programa',
              'permanencia',
            ])
            ->where('id', '=', $request->input('sum_id'))
            ->first();
          
          $tipoPersona = $sumData->permanencia == 'Egresado'
              ? 'Egresado'
              : 'Estudiante';

          $nivel = str_starts_with($sumData->programa, 'E.P.')
              ? 'pregrado'
              : 'posgrado';

          $tipoFinal = $tipoPersona . ' ' . $nivel;

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
              'tipo_investigador' => 'Estudiante',
              'tipo' => $tipoFinal,
              'tipo_investigador_programa' => $sumData->programa,
              'tipo_investigador_estado' => $sumData->permanencia,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
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
            'presentado' => $presentado,
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
      'nombres' => $request->attributes->get('token_decoded')->nombre,
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
  public function reporte(Request $request) {
    $publicacion_id = $request->query('publicacion_id');
    if (!$publicacion_id) {
      return response()->json(['error' => 'El parámetro publicacion_id es requerido'], 400);
    }

    $pubExiste = DB::table('Publicacion')
      ->where('id', '=', $publicacion_id)
      ->exists();
    
    if (!$pubExiste) {
      return response()->json(['error' => 'Publicación no encontrada'], 404);
    }
    $request->query->set('id', $publicacion_id);
    $request->merge(['id' => $publicacion_id]);

    $util = new ArticulosController();
    return $util->reporte($request);
  }

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
        'nombres' => $request->attributes->get('token_decoded')->nombre,
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
          'updated_at' => Carbon::now(),
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
}
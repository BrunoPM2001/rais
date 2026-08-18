<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Query\JoinClause;
use App\Http\Controllers\Admin\Estudios\Publicaciones\PublicacionesUtilsController;

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

  public function datosPaso1(Request $request) {
    $id = $request->query('id');
    $publicacionData = null;

    if ($id) {
      $pub = DB::table('Publicacion')
        ->select([
          'id',
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
          'doi' => $pub->doi,
          'art_tipo' => $pub->art_tipo,
          'titulo' => $pub->titulo,
          'resumen' => $pub->resumen,
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

  public function datosPaso2(Request $request) {
    $proyecto = DB::table('Proyecto')
      ->select([
        DB::raw("COALESCE(fecha_inicio, '') AS fecha_inicio"),
        DB::raw("COALESCE(fecha_fin, '') AS fecha_fin"),
        DB::raw("COALESCE(palabras_clave, '') AS palabras_clave"),
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $extras = DB::table('Proyecto_descripcion')
      ->select([
        'codigo',
        'detalle'
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->whereIn('codigo', ['resumen', 'objetivos', 'duracion_anio', 'duracion_mes', 'duracion_dia'])
      ->get()
      ->mapWithKeys(function ($item) {
        return [$item->codigo => $item->detalle];
      });

    return [
      'proyecto' => $proyecto,
      'extras' => $extras
    ];
  }

  public function datosPaso3(Request $request) {
    $documentos = DB::table('Proyecto_fex_doc AS a')
      ->select([
        'id',
        'doc_tipo',
        'nombre',
        'comentario',
        'fecha',
        DB::raw("CONCAT('/minio/', bucket, '/', a.key) AS url")
      ])
      ->where('proyecto_id', '=', $request->query('id'))
      ->get();

    return $documentos;
  }

  public function datosPaso4(Request $request) {
    $integrantes = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Proyecto_integrante_tipo AS c', 'c.id', '=', 'a.proyecto_integrante_tipo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->select([
        'a.id',
        'c.nombre AS tipo',
        'b.tipo AS usuario_tipo',
        DB::raw("CASE
          WHEN a.responsabilidad IN ('', 'null') OR a.responsabilidad IS NULL THEN c.nombre
          ELSE a.responsabilidad
        END AS tipo_integrante"),
        DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombre"),
        'b.doc_numero',
        DB::raw("CASE(a.condicion)
          WHEN 'Responsable' THEN 'Sí'
          ELSE 'No'
        END AS responsable"),
        DB::raw("CASE
          WHEN a.proyecto_integrante_tipo_id = 90 OR b.tipo = 'Externo' THEN 'Externo'
          ELSE d.nombre
        END AS facultad")
      ])
      ->where('a.proyecto_id', '=', $request->query('id'))
      ->get();

    return $integrantes;
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
        'resumen' => $request->input('resumen'),
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
        'resumen' => $request->input('resumen'),
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

  public function registrarPaso2(Request $request) {
    $date = Carbon::now();
    DB::table('Proyecto')
      ->where('id', '=', $request->input('id'))
      ->update([
        'palabras_clave' => $request->input('palabras_clave'),
        'fecha_inicio' => $request->input('fecha_inicio'),
        'fecha_fin' => $request->input('fecha_fin'),
        'updated_at' => $date
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'resumen'
      ], [
        'detalle' => $request->input('resumen')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'objetivos'
      ], [
        'detalle' => $request->input('objetivos')
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_anio'
      ], [
        'detalle' => $request->input('años') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_mes'
      ], [
        'detalle' => $request->input('meses') ?? ""
      ]);

    DB::table('Proyecto_descripcion')
      ->updateOrInsert([
        'proyecto_id' => $request->input('id'),
        'codigo' => 'duracion_dia'
      ], [
        'detalle' => $request->input('dias') ?? ""
      ]);
  }

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

  public function registrarPaso3(Request $request) {
    $date = Carbon::now();
    $date_name = $date->format('Ymd-His');

    if ($request->hasFile('file')) {

      $nameFile = $request->input('id') . "/" . $request->input('doc_tipo') . "_" . $date_name . "." . $request->file('file')->getClientOriginalExtension();

      $this->uploadFile($request->file('file'), "proyecto-fex-doc", $nameFile);

      DB::table('Proyecto_fex_doc')
        ->insert([
          'proyecto_id' => $request->input('id'),
          'doc_tipo' => $request->input('doc_tipo'),
          'nombre' => $request->input('nombre'),
          'comentario' => $request->input('comentario'),
          'bucket' => 'proyecto-fex-doc',
          'key' => $nameFile,
          'fecha' => $date
        ]);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function updateDoc(Request $request) {
    DB::table('Proyecto_fex_doc')
      ->where([
        'id' => $request->input('id')
      ])
      ->update([
        'nombre' => $request->input('nombre'),
        'comentario' => $request->input('comentario')
      ]);

    return ['message' => 'info', 'detail' => 'Datos actualizados correctamente'];
  }

  public function deleteDoc(Request $request) {
    DB::table('Proyecto_fex_doc')
      ->where([
        'id' => $request->query('id')
      ])
      ->delete();

    return ['message' => 'info', 'detail' => 'Archivo eliminado correctamente'];
  }
}
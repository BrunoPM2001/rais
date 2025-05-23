<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Exports\Admin\FromDataExport;
use App\Exports\Admin\PublicacionesExport;
use App\Http\Controllers\Admin\Estudios\Publicaciones\ArticulosController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\CapitulosLibrosController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\EventoController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\LibrosController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\PublicacionesUtilsController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\TesisAsesoriaController;
use App\Http\Controllers\Admin\Estudios\Publicaciones\TesisPropiasController;
use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PublicacionesController extends S3Controller {
  public function listado(Request $request) {
    if (!$request->query('investigador_id')) {
      $publicaciones = DB::table('Publicacion AS a')
        ->leftJoin('Publicacion_autor AS b', function (JoinClause $join) {
          $join->on('b.publicacion_id', '=', 'a.id')
            ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
            ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
            ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
            ->where('b.presentado', '=', 1);
        })
        ->select(
          'a.id',
          'a.codigo_registro',
          'a.cod_old',
          DB::raw("CASE (a.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE tipo_publicacion END AS tipo"),
          'a.tipo_publicacion',
          'a.isbn',
          'a.issn',
          'a.publicacion_nombre AS revista',
          'a.editorial',
          'a.evento_nombre',
          DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS presentador"),
          'd.nombre AS facultad',
          'e.nombre AS area',
          DB::raw("CASE (b.filiacion)
            WHEN 1 THEN 'Sí'
            ELSE 'No'
          END AS filiacion"),
          DB::raw("CASE (b.filiacion_unica)
            WHEN 1 THEN 'Sí'
            ELSE 'No'
          END AS filiacion_unica"),
          'a.titulo',
          'a.doi',
          'a.url',
          'a.fecha_publicacion',
          DB::raw("YEAR(a.fecha_publicacion) AS periodo"),
          'a.created_at',
          'a.updated_at',
          DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
          'source AS procedencia'
        )
        ->groupBy('a.id')
        ->orderByDesc('id')
        ->get();

      $patentes = DB::table('Patente AS a')
        ->leftJoin('Patente_autor AS b', function (JoinClause $join) {
          $join->on('b.patente_id', '=', 'a.id')
            ->where('b.es_presentador', '=', 1);
        })
        ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
        ->leftJoin('Facultad AS d', 'd.id', '=', 'c.facultad_id')
        ->leftJoin('Area AS e', 'e.id', '=', 'd.area_id')
        ->select([
          DB::raw("CONCAT('0', a.id) AS id"),
          'a.nro_registro AS codigo_registro',
          DB::raw("'Propiedad intelectual' AS tipo"),
          'a.tipo AS tipo_patente',
          DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) AS presentador"),
          'd.nombre AS facultad',
          'e.nombre AS area',
          'a.titulo',
          'a.created_at',
          'a.updated_at',
          DB::raw("CASE(a.estado)
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
        ->groupBy('a.id')
        ->get();

      return $publicaciones->merge($patentes);
    } else {
      $publicaciones = DB::table('Publicacion_autor AS a')
        ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
        ->leftJoin('Publicacion_autor AS c', function (JoinClause $join) {
          $join->on('c.publicacion_id', '=', 'b.id')
            ->where('c.presentado', '=', 1);
        })
        ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'c.investigador_id')
        ->leftJoin('Facultad AS e', 'e.id', '=', 'd.facultad_id')
        ->leftJoin('Area AS f', 'f.id', '=', 'e.area_id')
        ->select(
          'b.id',
          'b.codigo_registro',
          'b.cod_old',
          DB::raw("CASE (b.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE b.tipo_publicacion END AS tipo"),
          'b.tipo_publicacion',
          'b.isbn',
          'b.issn',
          'b.publicacion_nombre AS revista',
          'b.editorial',
          'b.evento_nombre',
          DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS presentador"),
          'e.nombre AS facultad',
          'f.nombre AS area',
          DB::raw("CASE (a.filiacion)
            WHEN 1 THEN 'Sí'
            ELSE 'No'
          END AS filiacion"),
          DB::raw("CASE (a.filiacion_unica)
            WHEN 1 THEN 'Sí'
            ELSE 'No'
          END AS filiacion_unica"),
          'b.titulo',
          'b.doi',
          'b.url',
          'b.fecha_publicacion',
          DB::raw("YEAR(b.fecha_publicacion) AS periodo"),
          'b.created_at',
          'b.updated_at',
          DB::raw("CASE(b.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado"),
          'b.source AS procedencia'
        )
        ->where('a.investigador_id', '=', $request->query('investigador_id'))
        ->orderByDesc('b.id')
        ->groupBy('b.id')
        ->get();

      $patentes = DB::table('Patente AS a')
        ->join('Patente_autor AS b', 'b.patente_id', '=', 'a.id')
        ->leftJoin('Patente_autor AS c', function (JoinClause $join) {
          $join->on('c.patente_id', '=', 'b.id')
            ->where('c.es_presentador', '=', 1);
        })
        ->leftJoin('Usuario_investigador AS d', 'd.id', '=', 'c.investigador_id')
        ->leftJoin('Facultad AS e', 'e.id', '=', 'd.facultad_id')
        ->leftJoin('Area AS f', 'f.id', '=', 'e.area_id')
        ->select([
          DB::raw("CONCAT('0', a.id) AS id"),
          'a.nro_registro AS codigo_registro',
          DB::raw("'Propiedad intelectual' AS tipo"),
          'a.tipo AS tipo_patente',
          DB::raw("CONCAT(d.apellido1, ' ', d.apellido2, ', ', d.nombres) AS presentador"),
          'e.nombre AS facultad',
          'f.nombre AS area',
          'a.titulo',
          'a.created_at',
          'a.updated_at',
          DB::raw("CASE(a.estado)
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
        ->where('b.investigador_id', '=', $request->query('investigador_id'))
        ->get();

      return $publicaciones->merge($patentes);
    }
  }

  public function detalle(Request $request) {
    $data = DB::table('Publicacion AS a')
      ->leftJoin('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Publicacion')
          ->where('b.recurso', '=', 'ANEXO')
          ->where('b.estado', '=', 20);
      })
      ->leftJoin('File AS c', function (JoinClause $join) {
        $join->on('c.tabla_id', '=', 'a.id')
          ->where('c.tabla', '=', 'Publicacion')
          ->where('c.recurso', '=', 'COMENTARIO')
          ->where('c.estado', '=', 20);
      })
      ->select([
        'a.id',
        'a.codigo_registro',
        'a.validado',
        'a.categoria_id',
        'a.comentario',
        'a.observaciones_usuario',
        'a.created_at AS fecha_inscripcion',
        'a.resolucion',
        'a.estado',
        DB::raw("CASE(a.estado)
            WHEN -1 THEN 'Eliminado'
            WHEN 1 THEN 'Registrado'
            WHEN 2 THEN 'Observado'
            WHEN 5 THEN 'Enviado'
            WHEN 6 THEN 'En proceso'
            WHEN 7 THEN 'Anulado'
            WHEN 8 THEN 'No registrado'
            WHEN 9 THEN 'Duplicado'
          ELSE 'Sin estado' END AS estado_text"),
        'a.tipo_publicacion',
        DB::raw("CASE (a.tipo_publicacion)
            WHEN 'articulo' THEN 'Artículo en revista'
            WHEN 'capitulo' THEN 'Capítulo de libro'
            WHEN 'libro' THEN 'Libro'
            WHEN 'tesis' THEN 'Tesis propia'
            WHEN 'tesis-asesoria' THEN 'Tesis asesoria'
            WHEN 'evento' THEN 'R. en evento científico'
            WHEN 'ensayo' THEN 'Ensayo'
          ELSE a.tipo_publicacion END AS tipo"),
        'b.id AS file_id_1',
        'c.id AS file_id_2',
        DB::raw("CONCAT('/minio/', b.bucket, '/', b.key) AS url_1"),
        DB::raw("CONCAT('/minio/', c.bucket, '/', c.key) AS url_2")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    //  Verificar fecha
    $fechaLimite = '2019-10-05';
    $posterior = (strtotime($data->fecha_inscripcion) >= strtotime($fechaLimite));

    $categorias = DB::table('Publicacion_categoria')
      ->select([
        'id AS value',
        'categoria AS label',
      ]);

    if ($posterior) {
      $categorias = $categorias
        ->where(DB::raw("date(created_at)"), '=', '2019-10-05');
    } else {
      $categorias = $categorias
        ->where(DB::raw("date(created_at)"), '=', '2013-10-01');
    }

    switch ($data->tipo_publicacion) {
      case 'articulo':
        $categorias = $categorias
          ->where('tipo', '=', 'Artículo en Revista')
          ->get();
        break;
      case 'capitulo':
        $categorias = $categorias
          ->where('tipo', '=', 'Capítulo en Libro')
          ->get();
        break;
      case 'evento':
        $categorias = $categorias
          ->where('tipo', '=', 'Libro de Resúmenes')
          ->get();
        break;
      case 'libro':
        $categorias = $categorias
          ->where('tipo', '=', 'Libro')
          ->get();
        break;
      case 'tesis-asesoria':
        $categorias = $categorias
          ->where('tipo', '=', 'Tesis asesoria')
          ->get();
        break;
      case 'tesis':
        $categorias = $categorias
          ->where('tipo', '=', 'Tesis')
          ->get();
        break;
    }

    return [
      'data' => $data,
      'categorias' => $categorias,
    ];
  }

  public function updateDetalle(Request $request) {
    $now = Carbon::now();
    $cod = 0;
    if ($request->input('estado') == 1) {
      if ($request->input('categoria_id') == "null") {
        return ['message' => 'warning', 'detail' => 'Necesita colocar una calificación y marcar como validado en caso quiera registrar la publicación'];
      } else {
        $pub = DB::table('Publicacion')
          ->select([
            'codigo_registro',
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
          ->where('id', '=', $request->input('id'))
          ->first();

        if ($pub->codigo_registro == null) {
          $cod = DB::table('Publicacion')
            ->select([
              'codigo_registro'
            ])
            ->orderByDesc('codigo_registro')
            ->first();

          //  Audit
          $audit = json_decode($pub->audit ?? "[]");

          $audit[] = [
            'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
            'nombres' => $request->attributes->get('token_decoded')->nombre,
            'apellidos' => $request->attributes->get('token_decoded')->apellidos,
            'accion' => 'Actualización de detalles (estado anterior: ' . $pub->estado . ')'
          ];

          $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

          DB::table('Publicacion')
            ->where('id', '=', $request->input('id'))
            ->update([
              'codigo_registro' => $cod->codigo_registro + 1,
              'validado' => $request->input('validado'),
              'categoria_id' => $request->input('categoria_id') == "null" ? null : $request->input('categoria_id'),
              'comentario' => $request->input('comentario'),
              'resolucion' => $request->input('resolucion'),
              'observaciones_usuario' => $request->input('observaciones_usuario'),
              'estado' => $request->input('estado'),
              'audit' => $audit,
              'updated_at' => $now,
            ]);

          $categoria = DB::table('Publicacion_categoria')
            ->select([
              'puntaje',
            ])
            ->where('id', '=', $request->input('categoria_id'))
            ->first();

          if ($request->input('validado') == 1) {
            DB::table('Publicacion_autor AS a')
              ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
              ->where('a.publicacion_id', '=', $request->input('id'))
              ->where('b.tipo', '=', 'DOCENTE PERMANENTE')
              ->update([
                'a.puntaje' => $categoria->puntaje,
              ]);
          }

          if ($request->hasFile('file')) {
            $date = Carbon::now();
            $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
            $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();
            $this->uploadFile($request->file('file'), "publicacion", $nameFile);

            DB::table('File')
              ->where('tabla', '=', 'Publicacion')
              ->where('tabla_id', '=', $request->input('id'))
              ->where('recurso', '=', 'ANEXO')
              ->update([
                'estado' => -1
              ]);

            DB::table('File')
              ->insert([
                'tabla' => 'Publicacion',
                'tabla_id' => $request->input('id'),
                'bucket' => 'publicacion',
                'key' => $nameFile,
                'recurso' => 'ANEXO',
                'estado' => 20,
                'created_at' => $now,
                'updated_at' => $now
              ]);
          }

          if ($request->hasFile('file_comentario')) {
            $date = Carbon::now();
            $name = "comentario-" . $date->format('Ymd-His') . "-" . Str::random(8);
            $nameFile = $name . "." . $request->file('file_comentario')->getClientOriginalExtension();
            $this->uploadFile($request->file('file_comentario'), "publicacion", $nameFile);

            DB::table('File')
              ->where('tabla', '=', 'Publicacion')
              ->where('tabla_id', '=', $request->input('id'))
              ->where('recurso', '=', 'COMENTARIO')
              ->update([
                'estado' => -1
              ]);

            DB::table('File')
              ->insert([
                'tabla' => 'Publicacion',
                'tabla_id' => $request->input('id'),
                'bucket' => 'publicacion',
                'key' => $nameFile,
                'recurso' => 'COMENTARIO',
                'estado' => 20,
                'created_at' => $now,
                'updated_at' => $now
              ]);
          }

          return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados correctamente'];
        }
      }
    }

    if ($request->input('validado') == 0) {
      DB::table('Publicacion_autor AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->where('a.publicacion_id', '=', $request->input('id'))
        ->where('b.tipo', '=', 'DOCENTE PERMANENTE')
        ->update([
          'a.puntaje' => 0,
        ]);
    }

    //  Audit
    $audit_db = DB::table('Publicacion')
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
      ->where('id', '=', $request->input('id'))
      ->first();

    $audit = json_decode($audit_db->audit ?? "[]");

    $audit[] = [
      'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
      'nombres' => $request->attributes->get('token_decoded')->nombre,
      'apellidos' => $request->attributes->get('token_decoded')->apellidos,
      'accion' => 'Actualización de detalles (estado anterior: ' . $audit_db->estado . ')'
    ];

    $audit = json_encode($audit, JSON_UNESCAPED_UNICODE);

    DB::table('Publicacion')
      ->where('id', '=', $request->input('id'))
      ->update([
        'validado' => $request->input('validado'),
        'categoria_id' => $request->input('categoria_id') == "null" ? null : $request->input('categoria_id'),
        'comentario' => $request->input('comentario'),
        'resolucion' => $request->input('resolucion'),
        'observaciones_usuario' => $request->input('observaciones_usuario'),
        'estado' => $request->input('estado'),
        'audit' => $audit,
        'updated_at' => Carbon::now(),
      ]);

    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
      $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "publicacion", $nameFile);

      DB::table('File')
        ->where('tabla', '=', 'Publicacion')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('recurso', '=', 'ANEXO')
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Publicacion',
          'tabla_id' => $request->input('id'),
          'bucket' => 'publicacion',
          'key' => $nameFile,
          'recurso' => 'ANEXO',
          'estado' => 20,
          'created_at' => $now,
          'updated_at' => $now
        ]);
    }

    if ($request->hasFile('file_comentario')) {
      $date = Carbon::now();
      $name = "comentario-" . $date->format('Ymd-His') . "-" . Str::random(8);
      $nameFile = $name . "." . $request->file('file_comentario')->getClientOriginalExtension();
      $this->uploadFile($request->file('file_comentario'), "publicacion", $nameFile);

      DB::table('File')
        ->where('tabla', '=', 'Publicacion')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('recurso', '=', 'COMENTARIO')
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Publicacion',
          'tabla_id' => $request->input('id'),
          'bucket' => 'publicacion',
          'key' => $nameFile,
          'recurso' => 'COMENTARIO',
          'estado' => 20,
          'created_at' => $now,
          'updated_at' => $now
        ]);
    }

    return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados correctamente'];
  }

  public function getTabs(Request $request) {
    $publicacion = DB::table('Publicacion')
      ->select([
        'tipo_publicacion'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $data = [];

    switch ($publicacion->tipo_publicacion) {
      case "articulo":
        $util1 = new ArticulosController();
        $data = $util1->datosPaso1($request);
        break;
      case "libro":
        $util1 = new LibrosController();
        $data = $util1->datosPaso1($request);
        break;
      case "capitulo":
        $util1 = new CapitulosLibrosController();
        $data = $util1->datosPaso1($request);
        break;
      case "tesis":
        $util1 = new TesisPropiasController();
        $data = $util1->datosPaso1($request);
        break;
      case "tesis-asesoria":
        $util1 = new TesisAsesoriaController();
        $data = $util1->datosPaso1($request);
        break;
      case "evento":
        $util1 = new EventoController();
        $data = $util1->datosPaso1($request);
        break;
      default:
        break;
    }

    $util2 = new PublicacionesUtilsController();
    $proyectos = $util2->proyectos_asociados($request);
    $autores = $util2->listarAutores($request);
    return [
      'detalle' => $data,
      'proyectos' => $proyectos,
      'autores' => $autores,
      'tipo' => $publicacion->tipo_publicacion
    ];
  }

  public function reporte(Request $request) {
    switch ($request->query('tipo')) {
      case "articulo":
        $util = new ArticulosController();
        return $util->reporte($request);
        break;
      case "libro":
        $util = new LibrosController();
        return $util->reporte($request);
        break;
      case "capitulo":
        $util = new CapitulosLibrosController();
        return $util->reporte($request);
        break;
      case "tesis":
        $util = new TesisPropiasController();
        return $util->reporte($request);
        break;
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
  }

  public function verAuditoria(Request $request) {
    $documento = DB::table('Publicacion')
      ->select([
        'audit'
      ])
      ->where('id', '=', $request->query('id'))
      ->first();

    $audit = json_decode($documento->audit ?? "[]");

    return $audit;
  }

  public function paso1(Request $request) {
    if ($request->input('id') == null) {
      switch ($request->tipo) {
        case "articulo":
          $p1 = new ArticulosController();
          return $p1->registrarPaso1($request);
        case "capitulo":
          $p1 = new CapitulosLibrosController();
          return $p1->registrarPaso1($request);
        case "evento":
          $p1 = new EventoController();
          return $p1->registrarPaso1($request);
        case "libro":
          $p1 = new LibrosController();
          return $p1->registrarPaso1($request);
        case "tesis-asesoria":
          $p1 = new TesisAsesoriaController();
          return $p1->registrarPaso1($request);
        case "tesis":
          $p1 = new TesisPropiasController();
          return $p1->registrarPaso1($request);
      }
    } else {
      $pub = DB::table('Publicacion')
        ->select([
          'tipo_publicacion'
        ])
        ->where('id', '=', $request->input('id'))
        ->first();

      switch ($pub->tipo_publicacion) {
        case "articulo":
          $p1 = new ArticulosController();
          return $p1->registrarPaso1($request);
        case "capitulo":
          $p1 = new CapitulosLibrosController();
          return $p1->registrarPaso1($request);
        case "evento":
          $p1 = new EventoController();
          return $p1->registrarPaso1($request);
        case "libro":
          $p1 = new LibrosController();
          return $p1->registrarPaso1($request);
        case "tesis-asesoria":
          $p1 = new TesisAsesoriaController();
          return $p1->registrarPaso1($request);
        case "tesis":
          $p1 = new TesisPropiasController();
          return $p1->registrarPaso1($request);
      }
    }
  }

  public function infoNuevo(Request $request) {
    switch ($request->query('tipo')) {
      case "articulo":
        $util1 = new ArticulosController();
        $data = $util1->infoNuevo();
        break;
      case "libro":
        $util1 = new LibrosController();
        $data = $util1->infoNuevo();
        break;
      case "capitulo":
        $util1 = new CapitulosLibrosController();
        $data = $util1->infoNuevo();
        break;
      case "tesis":
        $util1 = new TesisPropiasController();
        $data = $util1->infoNuevo();
        break;
      case "tesis-asesoria":
        $util1 = new TesisAsesoriaController();
        $data = $util1->infoNuevo();
        break;
      case "evento":
        $util1 = new EventoController();
        $data = $util1->infoNuevo();
        break;
      default:
        break;
    }

    return $data;
  }

  public function excel(Request $request) {

    $data = $request->all();

    $export = new FromDataExport($data);

    return Excel::download($export, 'proyectos.xlsx');
  }

  public function excelComplete(Request $request) {

    $filters = $request->all();
    set_time_limit(300);

    $export = new PublicacionesExport($filters);

    return Excel::download($export, 'publicaciones.xlsx');
  }
}

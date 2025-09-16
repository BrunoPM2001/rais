<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropiedadIntelectualController extends S3Controller {

  public function listado(Request $request) {
    $patentes = DB::table('Patente AS a')
      ->leftJoin('Patente_autor AS b', 'b.patente_id', '=', 'a.id')
      ->leftJoin('Usuario_investigador AS c', 'c.id', '=', 'b.investigador_id')
      ->select(
        'a.id',
        'a.titulo',
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
        'b.puntaje',
        'a.step'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->groupBy('a.id')
      ->orderByDesc('a.updated_at')
      ->get();

    return ['data' => $patentes];
  }

  public function verificar1(Request $request) {
    $data = DB::table('Patente AS a')
      ->leftJoin('File AS b', function (JoinClause $join) {
        $join->on('a.id', '=', 'b.tabla_id')
          ->where('b.tabla', '=', 'Patente')
          ->where('b.bucket', '=', 'publicacion')
          ->where('b.recurso', '=', 'CERTIFICADO')
          ->where('b.estado', '=', 20);
      })
      ->select([
        'a.titulo',
        'a.nro_registro',
        'a.tipo',
        'a.nro_expediente',
        'a.fecha_presentacion',
        'a.oficina_presentacion',
        'a.enlace',
        DB::raw("CONCAT('/minio/publicacion/', b.key) AS url")
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $data;
  }

  public function registrar1(Request $request) {
    if ($request->input('id')) {
      DB::table('Patente')
        ->where('id', '=', $request->input('id'))
        ->update([
          'titulo' => $request->input('titulo'),
          'nro_registro' => $request->input('nro_registro') ?? "",
          'tipo' => $request->input('tipo'),
          'nro_expediente' => $request->input('nro_expediente') ?? "",
          'fecha_presentacion' => $request->input('fecha_presentacion') ?? "",
          'oficina_presentacion' => $request->input('oficina_presentacion') ?? "",
          'enlace' => $request->input('enlace') ?? "",
          'step' => 2,
          'estado' => 6,
          'updated_at' => Carbon::now(),
        ]);

      if ($request->hasFile('file')) {
        $date = Carbon::now();
        $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
        $this->uploadFile($request->file('file'), "publicacion", $name);

        DB::table('File')
          ->where('tabla_id', '=', $request->input('id'))
          ->where('tabla', '=', 'Patente')
          ->where('bucket', '=', 'publicacion')
          ->where('recurso', '=', 'CERTIFICADO')
          ->update([
            'estado' => -1,
          ]);

        DB::table('File')
          ->insert([
            'tabla' => 'Patente',
            'tabla_id' => $request->input('id'),
            'bucket' => 'publicacion',
            'key' => $name,
            'recurso' => 'CERTIFICADO',
            'estado' => 20,
            'created_at' => $date,
            'updated_at' => $date
          ]);
      }

      return ['message' => 'success', 'detail' => 'Registro creado exitosamente', 'id' => $request->input('id')];
    } else {
      $date1 = Carbon::now();
      $id = DB::table('Patente')
        ->insertGetId([
          'titulo' => $request->input('titulo'),
          'nro_registro' => $request->input('nro_registro'),
          'tipo' => $request->input('tipo'),
          'nro_expediente' => $request->input('nro_expediente'),
          'fecha_presentacion' => $request->input('fecha_presentacion'),
          'oficina_presentacion' => $request->input('oficina_presentacion'),
          'enlace' => $request->input('enlace'),
          'step' => 2,
          'estado' => 6,
          'created_at' => $date1,
          'updated_at' => $date1,
        ]);

      DB::table('Patente_autor')
        ->insert([
          'patente_id' => $id,
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'condicion' => 'Autor',
          'es_presentador' => 1,
          'created_at' => $date1,
          'updated_at' => $date1
        ]);

      if ($request->hasFile('file')) {
        $date = Carbon::now();
        $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8) . "." . $request->file('file')->getClientOriginalExtension();
        $this->uploadFile($request->file('file'), "publicacion", $name);

        DB::table('File')
          ->where('tabla_id', '=', $id)
          ->where('tabla', '=', 'Patente')
          ->where('bucket', '=', 'publicacion')
          ->where('recurso', '=', 'CERTIFICADO')
          ->update([
            'estado' => -1,
          ]);

        DB::table('File')
          ->insert([
            'tabla' => 'Patente',
            'tabla_id' => $id,
            'bucket' => 'publicacion',
            'key' => $name,
            'recurso' => 'CERTIFICADO',
            'estado' => 20,
            'created_at' => $date,
            'updated_at' => $date
          ]);
      }

      return ['message' => 'success', 'detail' => 'Registro creado exitosamente', 'id' => $id];
    }
  }

  public function verificar2(Request $request) {
    $listado = DB::table('Patente_entidad')
      ->select([
        'id',
        'titular'
      ])
      ->where('patente_id', '=', $request->query('id'))
      ->get();

    return $listado;
  }

  public function verificar3(Request $request) {
    $listado = DB::table('Patente_autor AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.id',
        'a.condicion',
        DB::raw("COALESCE(CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres), CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres)) AS nombres"),
        DB::raw("IFNULL(b.tipo, 'Externo') AS tipo"),
        'a.es_presentador'
      ])
      ->where('patente_id', '=', $request->query('id'))
      ->get();

    return $listado;
  }

  public function verificar4(Request $request) {
    $estado = $this->verificar($request);
    if ($estado == 0) {
      return ['estado' => false];
    }

    return ['estado' => true];
  }

  public function registrar4(Request $request) {
    $count = DB::table('Patente')
      ->where('id', '=', $request->input('id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'La publicación no se puede volver a enviar'];
    } else {
      DB::table('Patente')
        ->where('id', '=', $request->input('id'))
        ->update([
          'estado' => 5,
          'updated_at' => Carbon::now()
        ]);

      return ['message' => 'success', 'detail' => 'Registro de patente enviado'];
    }
  }

  //  Paso 2
  public function addTitular(Request $request) {
    DB::table('Patente_entidad')
      ->insert([
        'patente_id' => $request->input('id'),
        'titular' => $request->input('titular'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Titular añadido'];
  }

  public function deleteTitular(Request $request) {
    DB::table('Patente_entidad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Titular eliminado'];
  }

  //  Paso 3
  public function addAutor(Request $request) {
    switch ($request->input('tipo')) {
      case "docente":
        DB::table('Patente_autor')
          ->insert([
            'patente_id' => $request->input('id'),
            'investigador_id' => $request->input('investigador_id'),
            'condicion' => $request->input('condicion')["value"],
            'es_presentador' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);

        return ['message' => 'success', 'detail' => 'Autor agregado correctamente'];
      case "estudiante":
        DB::table('Patente_autor')
          ->insert([
            'patente_id' => $request->input('id'),
            'investigador_id' => $request->input('investigador_id'),
            'condicion' => 'Inventor',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);

        return ['message' => 'success', 'detail' => 'Autor agregado correctamente'];
      case "externo":
        DB::table('Patente_autor')
          ->insert([
            'patente_id' => $request->input('id'),
            'nombres' => $request->input('nombres'),
            'apellido1' => $request->input('apellido1'),
            'apellido2' => $request->input('apellido2'),
            'condicion' => $request->input('condicion')["value"],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]);

        return ['message' => 'success', 'detail' => 'Autor agregado correctamente'];
    }
  }

  public function deleteAutor(Request $request) {
    DB::table('Patente_autor')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Autor eliminado'];
  }

  public function reporte(Request $request) {
    $patente = DB::table('Patente AS a')
      ->select([
        'a.titulo',
        'a.nro_registro',
        'a.tipo',
        'a.estado',
        'a.updated_at',
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $entidades = DB::table('Patente_entidad')
      ->select([
        'id',
        'titular'
      ])
      ->where('patente_id', '=', $request->query('id'))
      ->get();

    $autores = DB::table('Patente_autor AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select([
        'a.id',
        'a.condicion',
        DB::raw("COALESCE(CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres), CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres)) AS nombres"),
        DB::raw("IFNULL(b.tipo, 'Externo') AS tipo"),
        DB::raw("CASE(a.es_presentador)
          WHEN 1 THEN 'Sí'
          ELSE 'No'
        END AS es_presentador")
      ])
      ->where('patente_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('investigador.publicaciones.patente', [
      'patente' => $patente,
      'entidades' => $entidades,
      'autores' => $autores
    ]);

    return $pdf->stream();
  }

  //  Verificar
  public function verificar(Request $request) {
    $count = DB::table('Patente_autor')
      ->where('patente_id', '=', $request->query('id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    return $count;
  }
}

<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatentesController extends S3Controller {
  public function detalle(Request $request) {
    $patente = DB::table('Patente AS a')
      ->leftJoin('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Patente')
          ->where('b.recurso', '=', 'CERTIFICADO')
          ->where('b.estado', '=', 20);
      })
      ->select([
        'a.nro_registro',
        'a.estado',
        'a.titulo',
        'a.tipo',
        'a.nro_expediente',
        'a.fecha_presentacion',
        'a.oficina_presentacion',
        'a.enlace',
        DB::raw("CONCAT('/', b.bucket, '/', b.key) AS url"),
        'a.comentario',
        'a.observaciones_usuario'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    return $patente;
  }

  public function updateDetalle(Request $request) {
    $now = Carbon::now();

    DB::table('Patente')
      ->where('id', '=', $request->input('id'))
      ->update([
        'nro_registro' => $request->input('nro_registro'),
        'estado' => $request->input('estado'),
        'titulo' => $request->input('titulo'),
        'tipo' => $request->input('tipo'),
        'nro_expediente' => $request->input('nro_expediente'),
        'fecha_presentacion' => $request->input('fecha_presentacion'),
        'oficina_presentacion' => $request->input('oficina_presentacion'),
        'enlace' => $request->input('enlace'),
        'comentario' => $request->input('comentario'),
        'observaciones_usuario' => $request->input('observaciones_usuario'),
        'updated_at' => $now,
      ]);

    if ($request->hasFile('file')) {
      $date = Carbon::now();
      $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
      $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "publicacion", $nameFile);

      DB::table('File')
        ->where('tabla', '=', 'Patente')
        ->where('tabla_id', '=', $request->input('id'))
        ->where('recurso', '=', 'CERTIFICADO')
        ->update([
          'estado' => -1
        ]);

      DB::table('File')
        ->insert([
          'tabla' => 'Patente',
          'tabla_id' => $request->input('id'),
          'bucket' => 'publicacion',
          'key' => $nameFile,
          'recurso' => 'CERTIFICADO',
          'estado' => 20,
          'created_at' => $now,
          'updated_at' => $now
        ]);
    }

    return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados correctamente'];
  }

  public function getTabs(Request $request) {
    $titulares = DB::table('Patente_entidad')
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
        'b.tipo',
        DB::raw("CASE (a.es_presentador)
            WHEN 1 THEN 'Sí'
          ELSE 'No' END AS es_presentador"),
        'a.puntaje',
        'a.created_at',
        'a.updated_at',
      ])
      ->where('a.patente_id', '=', $request->query('id'))
      ->get();

    return [
      'titulares' => $titulares,
      'autores' => $autores,
    ];
  }

  public function agregarTitular(Request $request) {
    DB::table('Patente_entidad')
      ->insert([
        'patente_id' => $request->input('patente_id'),
        'titular' => $request->input('titular'),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    return ['message' => 'success', 'detail' => 'Titular añadido'];
  }

  public function eliminarTitular(Request $request) {
    DB::table('Patente_entidad')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Titular eliminado'];
  }

  public function agregarAutor(Request $request) {
    switch ($request->input('tipo')) {
      case "externo":
        DB::table('Patente_autor')->insert([
          'patente_id' => $request->input('id'),
          'nombres' => $request->input('nombres'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'condicion' => $request->input('condicion'),
          'es_presentador' => 0,
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

        $cuenta_autor = DB::table('Patente_autor')
          ->where('patente_id', '=', $request->input('id'))
          ->where('investigador_id', '=', $id_investigador)
          ->count();

        if ($cuenta_autor == 0) {
          DB::table('Patente_autor')->insert([
            'patente_id' => $request->input('id'),
            'investigador_id' => $id_investigador,
            'condicion' => $request->input('condicion'),
            'es_presentador' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ]);
        } else {
          return ['message' => 'warning', 'detail' => 'Este autor ya está registrado'];
        }
        break;
      case "interno":
        $cuenta_autor = DB::table('Patente_autor')
          ->where('patente_id', '=', $request->input('id'))
          ->where('investigador_id', '=', $request->input('investigador_id'))
          ->count();

        if ($cuenta_autor == 0) {
          DB::table('Patente_autor')->insert([
            'patente_id' => $request->input('id'),
            'investigador_id' => $request->input('investigador_id'),
            'condicion' => $request->input('condicion'),
            'es_presentador' => 0,
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

    return ['message' => 'success', 'detail' => 'Autor agregado exitosamente'];
  }

  public function editarAutor(Request $request) {

    DB::table('Patente_autor')
      ->where('id', '=', $request->input('id'))
      ->update([
        'condicion' => $request->input('condicion'),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Datos del autor editado exitosamente'];
  }

  public function eliminarAutor(Request $request) {
    DB::table('Patente_autor')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Autor eliminado de la lista exitosamente'];
  }

  public function reporte(Request $request) {
    $patente = DB::table('Patente AS a')
      ->leftJoin('File AS b', function (JoinClause $join) {
        $join->on('b.tabla_id', '=', 'a.id')
          ->where('b.tabla', '=', 'Patente')
          ->where('b.recurso', '=', 'CERTIFICADO')
          ->where('b.estado', '=', 20);
      })
      ->select([
        'a.nro_registro',
        'a.estado',
        'a.titulo',
        'a.tipo',
        'a.nro_expediente',
        'a.fecha_presentacion',
        'a.oficina_presentacion',
        'a.enlace',
        DB::raw("CONCAT('/', b.bucket, '/', b.key) AS url"),
        'a.comentario',
        'a.observaciones_usuario',
        'a.updated_at'
      ])
      ->where('a.id', '=', $request->query('id'))
      ->first();

    $titulares = DB::table('Patente_entidad')
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
        'b.tipo',
        DB::raw("CASE (a.es_presentador)
            WHEN 1 THEN 'Sí'
          ELSE 'No' END AS es_presentador"),
        'a.puntaje',
        'a.created_at',
        'a.updated_at',
      ])
      ->where('a.patente_id', '=', $request->query('id'))
      ->get();

    $pdf = Pdf::loadView('admin.estudios.publicaciones.patente', [
      'patente' => $patente,
      'titulares' => $titulares,
      'autores' => $autores,
    ]);
    return $pdf->stream();
  }
}

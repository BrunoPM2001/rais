<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestionSUMController extends Controller {

  public function listadoLocal(Request $request) {

    $listado = DB::table('Repo_sum')
      ->select([
        DB::raw("CONCAT(codigo_alumno, ' ', dni, ' ', apellido_paterno, ' ', apellido_materno, ' ', nombres) AS value"),
        'id',
        'codigo_alumno',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'sexo',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'telefono',
        'telefono_personal',
        'correo_electronico',
        'correo_electronico_personal',
        'domicilio',
        'facultad',
        'programa',
        'año_ciclo_estudio',
        'num_periodo_acad_matric',
        'situacion_academica',
        'permanencia',
        'ultimo_periodo_matriculado',
      ]);
    if (!empty($request->query('search'))) {
      $listado = $listado->having('value', 'LIKE', '%' . $request->query('search') . '%');
    }
    $listado = $listado
      ->orderBy('apellido_paterno')
      ->orderBy('apellido_materno')
      ->orderByDesc('id')
      ->paginate(10, ['*'], 'page', $request->query('page'));

    return $listado;
  }

  public function listadoSum(Request $request) {

    $concat = "(codigo_alumno || ' | ' || dni || ' | ' || apellido_paterno || ' ' || apellido_materno || ', ' || nombres || ' | ' || programa)";

    $listado = DB::connection('sum')->table('ALUMNO')
      ->select(
        DB::raw($concat . " value"),
        'codigo_alumno',
        'dni',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'sexo',
        'facultad',
        'programa',
        'permanencia',
        'ultimo_periodo_matriculado',
        DB::raw("TO_CHAR(FECHA_NACIMIENTO, 'dd-MM-yyyy') fecha_nacimiento"),
        'telefono',
        'telefono_personal',
        'correo_electronico',
        'correo_electronico_personal',
      )
      ->whereRaw($concat . " LIKE '%' || ? || '%'", [mb_strtoupper($request->query('query'))])
      ->limit(10)
      ->get();

    return $listado;
  }

  public function syncTotal()
  {
      Log::info("🚀 INICIO syncTotal");
      set_time_limit(0);
      ini_set('memory_limit', '512M');

      $chunkNumber = 0;
      $procesados = 0;

      DB::disableQueryLog();

      DB::connection('sum')->table('ALUMNO')
          ->orderBy('codigo_alumno')
          ->chunk(50, function ($alumnos) use (&$procesados, &$chunkNumber) { 

              $chunkNumber++;
              Log::info("📦 Procesando chunk: " . $chunkNumber);

              $data = [];

              foreach ($alumnos as $alumno) {
                  $hash = md5(
                      trim($alumno->dni) . '|' .
                      strtoupper(trim($alumno->apellido_paterno)) . '|' .
                      strtoupper(trim($alumno->apellido_materno)) . '|' .
                      strtoupper(trim($alumno->nombres)) . '|' .
                      ($alumno->facultad ?? '') . '|' .
                      ($alumno->programa ?? '') . '|' .
                      ($alumno->permanencia ?? '') . '|' .
                      ($alumno->ultimo_periodo_matriculado ?? '') . '|' .
                      ($alumno->situacion_academica ?? '') . '|' .
                      ($alumno->promedio_ponderado ?? '')
                  );

                  $data[] = [
                      'codigo_alumno' => trim($alumno->codigo_alumno),
                      'dni' => trim($alumno->dni),
                      'apellido_paterno' => strtoupper(trim($alumno->apellido_paterno)),
                      'apellido_materno' => strtoupper(trim($alumno->apellido_materno)),
                      'nombres' => strtoupper(trim($alumno->nombres)),
                      'sexo' => $alumno->sexo,
                      'fecha_nacimiento' => $alumno->fecha_nacimiento,
                      'lugar_nacimiento' => $alumno->lugar_nacimiento ?? null,
                      'telefono' => $alumno->telefono,
                      'telefono_personal' => $alumno->telefono_personal,
                      'correo_electronico' => $alumno->correo_electronico,
                      'correo_electronico_personal' => $alumno->correo_electronico_personal,
                      'domicilio' => $alumno->domicilio ?? null,
                      'facultad' => $alumno->facultad,
                      'id_facultad' => $alumno->id_facultad ?? null,
                      'especialidad' => $alumno->especialidad ?? null,
                      'programa' => $alumno->programa,
                      'año_ciclo_estudio' => $alumno->año_ciclo_estudio ?? null,
                      'num_periodo_acad_matric' => $alumno->num_periodo_acad_matric ?? null,
                      'situacion_academica' => $alumno->situacion_academica ?? null,
                      'permanencia' => $alumno->permanencia,
                      'ultimo_periodo_matriculado' => $alumno->ultimo_periodo_matriculado,
                      'promedio_ponderado' => $alumno->promedio_ponderado ?? null,
                      'promedio_ultima_matricula' => $alumno->promedio_ultima_matricula ?? null,
                      'hash' => $hash,
                  ];
              }

              $codigos = collect($data)->pluck('codigo_alumno')->toArray();
              $existentes = DB::table('Repo_sum')
                ->whereIn('codigo_alumno', $codigos)
                ->pluck('hash', 'codigo_alumno');

              Log::info("✅ Existentes encontrados: " . count($existentes));
              
              $dataFiltrado = [];
              foreach ($data as $item) {
                $codigo = $item['codigo_alumno'];

                if (!isset($existentes[$codigo]) || $existentes[$codigo] !== $item['hash']) {
                    $dataFiltrado[] = $item;
                }
              }

              if (!empty($dataFiltrado)) {
                DB::table('Repo_sum')->upsert(
                    $dataFiltrado,
                    ['codigo_alumno'],
                    [
                      'dni',
                      'apellido_paterno',
                      'apellido_materno',
                      'nombres',
                      'sexo',
                      'fecha_nacimiento',
                      'lugar_nacimiento',
                      'telefono',
                      'telefono_personal',
                      'correo_electronico',
                      'correo_electronico_personal',
                      'domicilio',
                      'facultad',
                      'id_facultad',
                      'especialidad',
                      'programa',
                      'año_ciclo_estudio',
                      'num_periodo_acad_matric',
                      'situacion_academica',
                      'permanencia',
                      'ultimo_periodo_matriculado',
                      'promedio_ponderado',
                      'promedio_ultima_matricula',
                      'hash',
                    ]
                  );
              }

              $procesados += count($data);

              Log::info("📈 Total procesados: " . $procesados);

              unset($data, $dataFiltrado);
          });

      return ['message' => 'success ','detail' => 'Se actualizaron los registros'];
  }
}

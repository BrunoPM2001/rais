<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProyectoController extends Controller {

  public function reporte(Request $request) {

    $adminId = $request->attributes->get('token_decoded')->id;

    $admin = DB::table('Usuario_admin AS admin')
      ->join('Usuario AS ux', 'admin.id', '=', 'ux.tabla_id')
      ->select('ux.username as nombres')
      ->where('admin.id', '=', $adminId)
      ->first();

    $facultad = $request->query('facultad');
    $periodo = $request->query('periodo');
    $tipo = $request->query('tipo_proyecto');
    $ocultarPresupuesto = in_array($tipo, ['PSINFIPU', 'PSINFINV']);
    $UIT = 5350;
    $subvencionVrip = 0;
    if ($tipo === 'PINVPOS' && (int) $periodo >= 2025) {
      $subvencionVrip = $UIT * 0.5; // 2675
    }

    $proyectos = DB::table('Proyecto as a')
      ->leftJoin('Proyecto_integrante as b', 'a.id', '=', 'b.proyecto_id')
      ->leftJoin('Usuario_investigador as c', 'b.investigador_id', '=', 'c.id')
      ->leftJoin('Facultad as d', 'a.facultad_id', '=', 'd.id')
      ->leftJoin('Area as e', 'd.area_id', '=', 'e.id')
      ->leftJoin('Grupo as f', 'a.grupo_id', '=', 'f.id')
      ->leftJoin('Facultad as g', 'c.facultad_id', '=', 'g.id')
      ->leftJoin('Proyecto_integrante_tipo as h', 'b.proyecto_integrante_tipo_id', '=', 'h.id')
      ->leftJoin('Grupo_integrante as i', function ($join) {
          $join->on('c.id', '=', 'i.investigador_id')
              ->on('a.grupo_id', '=', 'i.grupo_id');
      })
      ->leftJoin('Proyecto_presupuesto as j', 'a.id', '=', 'j.proyecto_id')
      ->select([
        'a.id',
        'e.sigla',
        'e.nombre as area',
        'f.grupo_nombre_corto',
        'f.grupo_nombre',
        'd.nombre as facultad_grupo',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("CONCAT(c.apellido1, ' ', c.apellido2, ', ', c.nombres) as nombres"),
        'h.nombre as condicion',
        'g.nombre as facultad_miembro',
        'c.codigo',
        DB::raw("CASE 
            WHEN c.tipo = 'DOCENTE PERMANENTE' THEN 'Docente permanente'
            WHEN c.tipo = 'Estudiante Pre Grado' THEN 'Estudiante pregrado'
            WHEN c.tipo IS NULL THEN 'Externo'
            ELSE c.tipo
        END as tipo_investigador"),
        'i.condicion as condicion_gi',
        'f.id as grupo_id',
        DB::raw("(
          SELECT COALESCE(SUM(j_sub.monto), 0)
          FROM Proyecto_presupuesto AS j_sub
          WHERE j_sub.proyecto_id = a.id
        ) AS presupuesto"),
      ])
      ->where('a.tipo_proyecto', '=', $tipo)
      ->when(in_array($tipo, ['PRO-CTIE', 'PICV', 'ECI']) && empty($facultad), function ($query) {
      }, function ($query) use ($facultad) {
          $query->where('a.facultad_id', '=', $facultad);
      })
      ->where('a.periodo', '=', $periodo)
      ->whereIn('a.estado', [1, 8])
      ->where(function ($query) use ($tipo) {
          $query->where(function ($sub) {
              $sub->where('i.condicion', 'not like', 'Ex%')
                  ->orWhereNull('i.condicion');
          });
          if (in_array($tipo, ['PRO-CTIE', 'PICV', 'PINVPOS'])) {
              $query->orWhereIn('c.id', function ($sub2) {
                  $sub2->select('gi2.investigador_id')
                      ->from('Grupo_integrante as gi2')
                      ->groupBy('gi2.investigador_id')
                      ->havingRaw('SUM(gi2.condicion LIKE "Ex%") = COUNT(*)');
              });
          }
      })
      ->when(in_array($tipo, ['PRO-CTIE', 'PICV', 'ECI']), function ($query) use ($tipo) {

          if ($tipo === 'ECI') {

              $query->orderBy('a.orden_merito', 'asc')
              ->orderBy('a.id')
              ->orderByRaw("
                FIELD(b.proyecto_integrante_tipo_id,
                    86, 88, 87,
                    92, 93
                ),
                c.apellido1,
                c.apellido2,
                c.nombres
              ");

          } else {

              $query->orderByRaw('
                  CAST(SUBSTRING(a.codigo_proyecto, 6, 2) AS UNSIGNED), 
                  a.codigo_proyecto,
                  FIELD(b.proyecto_integrante_tipo_id,
                      86, 88, 87,
                      92, 93
                  ),
                  c.apellido1,
                  c.apellido2,
                  c.nombres
              ');
          }

      }, function ($query) {
          $query->orderByRaw('f.grupo_nombre, 
            a.codigo_proyecto, 
            FIELD(b.proyecto_integrante_tipo_id,
              1, 2, 3, 5, 6, 4, 31, 32, 33, 35, 95,
              7, 8, 9, 11, 12, 10, 42, 50, 51, 52, 55,
              13, 14, 53, 54,
              15, 16,
              17, 18,
              19, 20,
              21, 22, 23, 24, 26, 25, 27,
              28, 29,
              30, 34,
              36, 37, 38, 40, 41, 39,
              44, 45, 46, 47, 48, 49, 90, 91,
              56, 57, 58, 59, 60, 61, 94, 62, 63, 64, 65, 82,
              66, 67, 68, 69,
              70, 71,
              74, 75, 76, 77, 78, 79, 80, 81,
              83, 84, 85,
              86, 88, 87,
              92, 93, 94
            ),
            c.apellido1, c.apellido2, c.nombres');
        })
      ->groupBy('b.id')
      ->get();

    if ($tipo === 'PINVPOS' && (int) $periodo >= 2025 && $subvencionVrip > 0) {
      $proyectos->transform(function ($p) use ($subvencionVrip) {
        $p->presupuesto = (float) ($p->presupuesto ?? 0) + $subvencionVrip;
        return $p;
      });
    }

    $area = DB::table('Facultad AS fx')
      ->leftJoin('Area as ax', 'ax.id', '=', 'fx.area_id')
      ->select('ax.nombre', 'ax.sigla', 'fx.nombre as facultad')
      ->where('fx.id', '=', $facultad)
      ->first();

    $qrUrl = "https://vrip.unmsm.edu.pe/convocatorias/"; // Aquí va la URL fija del sistema RAIS
    $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($qrUrl));


    switch ($tipo) {
      case 'PCONFIGI':
        $tipo = 'Proyectos de Investigación con Financiamiento para Grupos de Investigación';
        break;
      case 'PCONFIGI-INV':
        $tipo = 'Proyectos de Innovación para  Grupos de Investigación “INNOVA SAN MARCOS';
        break;
      case 'PRO-CTIE':
        $tipo = 'Proyectos de Ciencia, Tecnología, Innovación y Emprendimiento (PRO-CTIE) para Estudiantes de la UNMSM';
        $vista = 'admin.reportes.proctiePDF';
        break;
      case 'ECI':
        $tipo = 'Programa de Equipamiento Científico para la Investigación de la UNMSM';
        break;
      case 'PSINFIPU':
        $tipo = 'Proyectos de Publicación Académica para Grupos de Investigación';
        break;
      case 'PMULTI':
        $tipo = 'Proyectos multidisciplinarios';
        break;
      case 'PSINFINV':
        $tipo = 'Proyectos de Investigación Con Recursos No Monetarios para Grupos de Investigación';
        break;
      case 'FEX':
        $tipo = 'Proyectos con Financiamiento Externo para Grupos de Investigación';
        break;
      case 'PICV':
        $tipo = 'Programa para la Inducción en Investigación Científica, en el Verano (PICV)';
        $vista = 'admin.reportes.proctiePDF';
        break;
      case 'PINVPOS':
        $tipo = 'Programa de Talleres de Investigación y Posgrado';
        break;
      default:
        $tipo = 'Tipo de Proyecto Desconocido';
    }

    $pdf = Pdf::loadView( $vista ?? 'admin.reportes.proyectoPDF', [
      'lista' => $proyectos,
      'periodo' => $periodo,
      'tipo' => $tipo,
      'area' => $area,
      'admin' => $admin,
      'qr' => $qrCode,
      'ocultarPresupuesto' => $ocultarPresupuesto,
      'subvencionVrip'    => $subvencionVrip,
    ]);
    return $pdf->stream();
  }
}

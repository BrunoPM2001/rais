<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProyectosExport implements FromQuery, WithHeadings, ShouldAutoSize {

    protected $filters;
    protected $facultadId;

    public function __construct($filters, $facultadId)
    {
        $this->filters = $filters;
        $this->facultadId = $facultadId;
    }

    public function headings(): array
    {
        return [
            'Grupo',
            'Código proyecto',
            'Tipo',
            'Periodo',
            'Facultad',
            'Estado',
            'Resolución',
            'Fecha de inscripción',
            'Título',
            'Código',
            'Facultad_inv',
            'Investigador',
            'Condición',
            'Cod_linea',
            'Linea'
        ];
    }

    public function query()
    {
        $query = DB::table('Proyecto as a')
            ->leftJoin('Proyecto_integrante as b','b.proyecto_id','=','a.id')
            ->leftJoin('Usuario_investigador as c','c.id','=','b.investigador_id')
            ->leftJoin('Proyecto_integrante_tipo as d', 'd.id', '=', 'b.proyecto_integrante_tipo_id')
            ->leftJoin('Grupo as e', 'e.id', '=', 'a.grupo_id')
            ->leftJoin('Facultad as f', 'f.id', '=', 'c.facultad_id')
            ->leftJoin('Linea_investigacion as g', 'g.id', '=', 'a.linea_investigacion_id')
            ->leftJoin('Facultad as h', 'h.id', '=', 'a.facultad_id')
            ->select(
                'e.grupo_nombre',
                'a.codigo_proyecto',
                'a.tipo_proyecto',
                'a.periodo',
                'h.nombre as facultad',
                DB::raw("CASE(a.estado)
                    WHEN -1 THEN 'Eliminado'
                    WHEN 0 THEN 'No aprobado'
                    WHEN 1 THEN 'Aprobado'
                    WHEN 2 THEN 'Observado'
                    WHEN 3 THEN 'En evaluacion'
                    WHEN 5 THEN 'Enviado'
                    WHEN 6 THEN 'En proceso'
                    WHEN 7 THEN 'Anulado'
                    WHEN 8 THEN 'Sustentado'
                    WHEN 9 THEN 'En ejecución'
                    WHEN 10 THEN 'Ejecutado'
                    WHEN 11 THEN 'Concluído'
                ELSE 'Sin estado' END AS estado"),
                'a.resolucion_rectoral',
                'a.fecha_inscripcion',
                'a.titulo',
                'c.codigo as cod_docente',
                'f.nombre as facultad_inv',
                DB::raw("CONCAT(c.apellido1,' ',c.apellido2,' , ',c.nombres) as integrante"),
                'd.nombre as condicion',
                'g.codigo',
                'g.nombre'
            )
            ->where('a.facultad_id', $this->facultadId);

        /*
        MAPA DE FILTROS
        Traduce filtros del frontend a columnas SQL
        */

        $map = [
            'tipo_proyecto' => 'a.tipo_proyecto',
            'codigo_proyecto' => 'a.codigo_proyecto',
            'titulo' => 'a.titulo',
            'periodo' => 'a.periodo',
            'resolucion_rectoral' => 'a.resolucion_rectoral',
            'responsable' => 'd.nombre',
            'fecha_inscripcion' => 'a.fecha_inscripcion',
            'estado' => 'a.estado'
        ];

        $tokens = $this->filters['tokens'] ?? [];

        foreach ($tokens as $token) {
            $key = $token['propertyKey'] ?? null;
            $operator = $token['operator'] ?? null;
            $value = $token['value'] ?? null;

            if (!$key || !isset($map[$key])) {
                continue;
            }

            $column = $map[$key];
            switch ($operator) {

                case '=':
                    $query->where($column, $value);
                    break;

                case '!=':
                    $query->where($column, '!=', $value);
                    break;

                case ':':
                    $query->where($column, 'like', "%$value%");
                    break;

                case '!:':
                    $query->where($column, 'not like', "%$value%");
                    break;

                case '^':
                    $query->where($column, 'like', "$value%");
                    break;

                case '!^':
                    $query->where($column, 'not like', "$value%");
                    break;
            }
        }

        return $query
            ->orderBy('a.id')
            ->limit(15000);
        }
}
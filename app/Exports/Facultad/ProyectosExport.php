<?php

namespace App\Exports\Facultad;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProyectosExport implements FromQuery, WithHeadings, ShouldAutoSize {

    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            'Grupo',
            'Tipo',
            'Código proyecto',
            'Periodo',
            'Resolución',
            'Fecha de inscripción',
            'Título',
            'Código',
            'Investigador',
            'Estado',
            'Condición'
        ];
    }

    public function query()
    {
        $query = DB::table('Proyecto as a')
            ->leftJoin('Proyecto_integrante as b','b.proyecto_id','=','a.id')
            ->leftJoin('Usuario_investigador as c','c.id','=','b.investigador_id')
            ->leftJoin('Proyecto_integrante_tipo as d', 'd.id', '=', 'b.proyecto_integrante_tipo_id')
            ->leftJoin('Grupo as e', 'e.id', '=', 'a.grupo_id')
            ->select(
                'e.grupo_nombre',
                'a.tipo_proyecto',
                'a.codigo_proyecto',
                'a.periodo',
                'a.resolucion_rectoral',
                'a.fecha_inscripcion',
                'a.titulo',
                'c.codigo',
                DB::raw("CONCAT(c.apellido1,' ',c.apellido2,' , ',c.nombres) as integrante"),
                'a.estado',
                'b.condicion'
            );

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
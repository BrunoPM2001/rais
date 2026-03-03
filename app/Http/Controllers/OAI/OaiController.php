<?php

namespace App\Http\Controllers\Oai;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class OaiController extends Controller
{
    public function handle(Request $request)
    {
        $verb = $request->query('verb');

        switch ($verb) {

            case 'Identify':
                return $this->identify();

            case 'ListRecords':
                return $this->listRecords($request);

            default:
                return response()->json([
                    'error' => 'badVerb',
                    'message' => 'Verbo OAI no soportado'
                ], 400);
        }
    }

    /**
     * Información del repositorio
     */
    private function identify()
    {
        return response()->json([
            'repositoryName' => 'RAIS - UNMSM',
            'baseURL' => url('/api/oai'),
            'protocolVersion' => '2.0',
            'adminEmail' => 'repositorio@unmsm.edu.pe',
            'earliestDatestamp' => '2024-01-01',
            'granularity' => 'YYYY-MM-DD',
            'responseDate' => now()
        ]);
    }

    /**
     * Lista de registros (ejemplo: proyectos publicados)
     */
    private function listRecords(Request $request)
    {
        $limit = $request->query('limit', 50);

        // Ajusta esto a tu lógica real (por ejemplo publicar = 1)
        $items = DB::table('Proyecto')
            ->select(
                'id',
                'titulo',
                'tipo_proyecto',
                'periodo',
                'created_at'
            )
            ->limit($limit)
            ->get();

        $records = $items->map(function ($item) {
            return [
                'identifier' => 'oai:rais.unmsm.edu.pe:proyecto/' . $item->id,
                'datestamp' => $item->created_at,
                'metadata' => [
                    'title' => $item->titulo,
                    'type' => $item->tipo_proyecto,
                    'periodo' => $item->periodo,
                    'url' => url('/proyecto/' . $item->id)
                ]
            ];
        });

        return response()->json([
            'verb' => 'ListRecords',
            'responseDate' => now(),
            'count' => $records->count(),
            'records' => $records
        ]);
    }
}
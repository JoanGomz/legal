<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\Operation\Consents;

class DashboardController extends Controller
{

    /**
     * Endpoint para obtener las métricas del dashboard
     */
    public function getMetrics()
    {
        try {
            //si es rol super admin, mostrar todas las metricas, sino solo las del parque asignado
            if (!is_null(auth()->user())) {
                $data =  $this->getAllMetrics();
            } else {
                $data = $this->getMetricsByPark();
            }

            return $this->responseLivewire('success', 'Métricas obtenidas exitosamente', $data);
        } catch (\Exception $ex) {
            return $this->responseLivewire('error', $ex->getMessage(), []);
        }
    }

    /**
     * Métricas para el dashboard, sin filtrar por parque
     */
    public function getAllMetrics()
    {
        //* Conteo total de consentimientos
        $totalConsents = Consents::where('is_delete', 0)->count();

        //* Conteo para gráfico barras por parque y arcade
        $rawData = Consents::where('consents.is_delete', 0)
            ->join('parks', 'consents.park_id', '=', 'parks.id')
            ->leftJoin('atraccion_arcade', 'consents.arcade_id', '=', 'atraccion_arcade.id')
            ->selectRaw("
                        parks.name as park_name, 
                        COALESCE(atraccion_arcade.nombre, 'Sin Arcade') as arcade_name, 
                        COUNT(*) as total
                    ")
            ->groupBy('parks.name', 'atraccion_arcade.nombre')
            ->get();

        $categories = $rawData->pluck('park_name')->unique()->values()->toArray(); // Eje X (Parques)
        $arcades = $rawData->pluck('arcade_name')->unique()->values(); // Tipos de barra (Series)

        $series = [];

        foreach ($arcades as $arcade) {
            $data = [];
            foreach ($categories as $park) {
                $match = $rawData->where('park_name', $park)->where('arcade_name', $arcade)->first();
                $data[] = $match ? $match->total : 0;
            }

            $series[] = [
                'name' => $arcade,
                'data' => $data
            ];
        }

        //* Conteo para gráfico parentesco
        $countParent = Consents::where('is_delete', 0)
            ->selectRaw('relationship, COUNT(*) as total')
            ->groupBy('relationship')
            ->get();

        $totalConsents = $countParent->sum('total');

        $radialData = [
            'series' => $countParent->map(function ($item) use ($totalConsents) {
                return round(($item->total / $totalConsents) * 100, 2);
            })->values(),
            'labels' => $countParent->pluck('relationship')->values()
        ];

        return [
            'total_consents' => $totalConsents,
            'chart_data' => [
                'series' => $series,
                'categories' => $categories
            ],
            'count_parent' => $radialData
        ];
    }

    /**
     * Métricas para el dashboard, filtradas por parque del usuario autenticado
     */
    public function getMetricsByPark()
    {
        $userParkId = auth()->user()->park_id;

        //* Conteo total de consentimientos del parque
        $totalConsents = Consents::where('park_id', $userParkId)->where('is_delete', 0)->count();

        //* consentimientos por arcades del parque
        $consentsByPark = Consents::where('park_id', $userParkId)
            ->where('is_delete', 0)
            ->leftJoin('atraccion_arcade', 'consents.arcade_id', '=', 'atraccion_arcade.id')
            ->selectRaw('COALESCE(atraccion_arcade.nombre, "Sin Arcade") as arcade_name, COUNT(*) as total')
            ->groupBy('arcade_id', 'atraccion_arcade.nombre')
            ->get();

        $chartdata = [
            'labels' => $consentsByPark->pluck('arcade_name')->toArray(),
            'series' => $consentsByPark->pluck('total')->toArray()
        ];

        return [
            'total_consents' => $totalConsents,
            'chart_data' => $chartdata,
        ];
    }
}

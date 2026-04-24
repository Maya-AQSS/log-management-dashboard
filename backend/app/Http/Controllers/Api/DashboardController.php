<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private LogServiceInterface $logService) {}

    /**
     * BFF del dashboard: cards de severidad (incluye "all") y totales por aplicación.
     * Las traducciones y la construcción de URLs se hacen en el cliente a partir del `key`.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'severity_cards' => $this->logService->dashboardSeverityCards(),
                'application_totals' => $this->logService->dashboardApplicationTotals(),
            ],
        ]);
    }
}

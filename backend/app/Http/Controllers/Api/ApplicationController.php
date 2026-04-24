<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationPluckScope;
use App\Http\Controllers\Controller;
use App\Services\Contracts\ApplicationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        private ApplicationServiceInterface $applicationService,
    ) {}

    /**
     * Devuelve aplicaciones para dropdowns de filtros.
     * Query param `scope`: all (default), with_logs, with_archived_logs.
     */
    public function index(Request $request): JsonResponse
    {
        $scope = ApplicationPluckScope::tryFrom(
            $request->string('scope')->toString()
        ) ?? ApplicationPluckScope::All;

        $applications = $this->applicationService->pluckForFilter($scope);

        $data = $applications
            ->map(fn (string $name, int|string $id) => [
                'id' => (int) $id,
                'name' => $name,
            ])
            ->values()
            ->all();

        return response()->json(['data' => $data]);
    }
}

<?php

namespace App\Services;

use App\Enums\Severity;
use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class LogService implements LogServiceInterface
{
    public function __construct(
        private LogRepositoryInterface $logRepository
    ) {}

    /**
     * Devuelve una página de logs.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->logRepository->paginate($perPage);
    }

    /**
     * Encuentra un log por su id.
     */
    public function findOrFail(int $id): Log
    {
        return $this->logRepository->findOrFail($id);
    }

    /**
     * Devuelve los datos de los últimos logs para streaming.
     */
    public function streamPayload(int $limit = 10): array
    {
        $logs = $this->logRepository->latestForStream($limit);

        return $logs->map(function (Log $log): array {
            return [
                'id' => $log->id,
                'severity' => $log->severity,
                'message' => $log->message,
                'application' => $log->application?->name,
                'error_code' => $log->errorCode?->code,
                'created_at' => $log->created_at?->toIso8601String(),
            ];
        })->all();
    }

    /**
     * Busca y filtra logs por diferentes criterios: 
     * - texto libre en el mensaje
     * - tipo de severidad de error
     * - si está archivado o no
     * - si está resuelto o no
     */
    public function searchAndFilter(?string $search, ?string $severity, ?string $archived, ?string $resolved, int $perPage = 15): LengthAwarePaginator
    {
        return $this->logRepository->searchAndFilter($search, $severity, $archived, $resolved, $perPage);
    }

    /**
     * Devuelve los datos de las cards del dashboard con estado resolved/unresolved.
     * Incluye todas las severidades y la card "all" usando el total de logs.
     *
     * @return array<int,array{key:string,totalCount:int,resolvedCount:int,unresolvedCount:int}>
     */
    public function dashboardSeverityCards(): array
    {
        $cacheKey = 'dashboard:severity-cards:all';

        return Cache::remember($cacheKey, now()->addSeconds(10), function (): array {
            $severityKeys = Severity::values();
            $bySeverity = $this->logRepository->severityResolvedCounts(true);
            $totalLogsCount = $this->logRepository->logsCount(true);

            $cards = collect($severityKeys)
                ->map(function (string $key) use ($bySeverity): array {
                    $resolvedCount = (int) ($bySeverity[$key]['resolved'] ?? 0);
                    $unresolvedCount = (int) ($bySeverity[$key]['unresolved'] ?? 0);

                    return $this->buildDashboardCard(
                        key: $key,
                        resolvedCount: $resolvedCount,
                        unresolvedCount: $unresolvedCount,
                    );
                })
                ->values();

            $allResolved = (int) $cards->sum('resolvedCount');
            $allUnresolved = (int) $cards->sum('unresolvedCount');

            $cards->prepend($this->buildDashboardCard(
                key: 'all',
                resolvedCount: $allResolved,
                unresolvedCount: $allUnresolved,
                totalCount: $totalLogsCount,
            ));

            return $cards->all();
        });
    }

    /**
     * Devuelve el id de ArchivedLog equivalente al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int
    {
        return $this->logRepository->archivedLogIdFor($logId);
    }

    /**
     * Construye una card del dashboard con estado resolved/unresolved.
     * 
     * @return array{key:string,totalCount:int,resolvedCount:int,unresolvedCount:int}
     */
    private function buildDashboardCard(
        string $key,
        int $resolvedCount,
        int $unresolvedCount,
        ?int $totalCount = null,
    ): array {
        return [
            'key' => $key,
            'totalCount' => $totalCount ?? ($resolvedCount + $unresolvedCount),
            'resolvedCount' => $resolvedCount,
            'unresolvedCount' => $unresolvedCount,
        ];
    }
}

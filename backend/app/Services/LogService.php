<?php

namespace App\Services;

use App\Enums\Severity;
use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maya\Messaging\Publishers\AuditPublisher;

class LogService implements LogServiceInterface
{
    private const AUDIT_ENTITY_TYPE = 'log';

    public function __construct(
        private LogRepositoryInterface $logRepository,
        private AuditPublisher $auditPublisher,
    ) {}

    /**
     * Devuelve una página de logs.
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator
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
     * - rango de fechas
     */
    public function searchAndFilter(
        ?string $search,
        ?array $severity,
        ?int $applicationId,
        ?string $archived,
        ?string $resolved,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortBy,
        ?string $sortDir,
        int $perPage = 25
    ): LengthAwarePaginator {
        return $this->logRepository->searchAndFilter(
            $search,
            $severity,
            $applicationId,
            $archived,
            $resolved,
            $dateFrom,
            $dateTo,
            $sortBy,
            $sortDir,
            $perPage
        );
    }

    /**
     * Agregados del dashboard (severidad + por aplicación) con la misma entrada de caché.
     *
     * @return array{severity_cards: array<int, array{key:string,totalCount:int,resolvedCount:int,unresolvedCount:int}>, application_totals: array<int, array{application_id: int, name: string, total: int}>}
     */
    private function getDashboardAggregates(): array
    {
        return Cache::remember('dashboard:aggregates', now()->addSeconds(10), function (): array {
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

            return [
                'severity_cards' => $cards->all(),
                'application_totals' => $this->logRepository->applicationTotals(true),
            ];
        });
    }

    /**
     * Devuelve los datos de las cards del dashboard con estado resolved/unresolved.
     * Incluye todas las severidades y la card "all" usando el total de logs.
     *
     * @return array<int,array{key:string,totalCount:int,resolvedCount:int,unresolvedCount:int}>
     */
    public function dashboardSeverityCards(): array
    {
        return $this->getDashboardAggregates()['severity_cards'];
    }

    /**
     * Conteos por aplicación (logs activos en `logs`, mismo criterio que las cards de severidad).
     *
     * @return array<int, array{application_id: int, name: string, total: int}>
     */
    public function dashboardApplicationTotals(): array
    {
        return $this->getDashboardAggregates()['application_totals'];
    }

    /**
     * Devuelve el id de ArchivedLog equivalente al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int
    {
        return $this->logRepository->archivedLogIdFor($logId);
    }

    /**
     * Marca el log como resuelto.
     */
    public function resolved(int $logId, string $actorUserId): void
    {
        $this->logRepository->findOrFail($logId);

        $affected = $this->logRepository->resolved($logId);
        if ($affected < 1) {
            return;
        }

        $this->afterCommit(function () use ($logId, $actorUserId): void {
            $this->auditPublisher->publish(
                applicationSlug: (string) config('messaging.app'),
                entityType: self::AUDIT_ENTITY_TYPE,
                entityId: (string) $logId,
                action: 'Marcar un log como resuelto',
                userId: $actorUserId,
                previousValue: ['resolved' => false],
                newValue: ['resolved' => true],
            );
        });
    }

    /**
     * Sin transacción activa, publica de inmediato; dentro de `DB::transaction`, difiere al commit.
     */
    private function afterCommit(callable $callback): void
    {
        if (DB::transactionLevel() === 0) {
            $callback();

            return;
        }

        DB::afterCommit($callback);
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

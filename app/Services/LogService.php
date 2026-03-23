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

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->logRepository->paginate($perPage);
    }

    public function findOrFail(int $id): Log
    {
        return $this->logRepository->findOrFail($id);
    }

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

    public function searchAndFilter(?string $search, ?string $severity, ?string $archived, ?string $resolved, int $perPage = 15): LengthAwarePaginator
    {
        return $this->logRepository->searchAndFilter($search, $severity, $archived, $resolved, $perPage);
    }

    public function dashboardSeverityCards(bool $includeArchived = false): array
    {
        $cacheKey = sprintf('dashboard:severity-cards:%s', $includeArchived ? 'all' : 'active');

        return Cache::remember($cacheKey, now()->addSeconds(10), function () use ($includeArchived): array {
            $severityKeys = Severity::values();
            $bySeverity = $this->logRepository->severityResolvedCounts($includeArchived);
            $totalLogsCount = $this->logRepository->logsCount($includeArchived);

            $cards = collect($severityKeys)
                ->map(function (string $key) use ($bySeverity, $includeArchived): array {
                    $resolvedCount = (int) ($bySeverity[$key]['resolved'] ?? 0);
                    $unresolvedCount = (int) ($bySeverity[$key]['unresolved'] ?? 0);

                    return [
                        'key' => $key,
                        'count' => $resolvedCount + $unresolvedCount,
                        'resolvedCount' => $resolvedCount,
                        'unresolvedCount' => $unresolvedCount,
                        'routeParams' => $includeArchived
                            ? ['severity' => $key]
                            : ['severity' => $key, 'archived' => 'not_archived'],
                    ];
                })
                ->values();

            $allResolved = (int) $cards->sum('resolvedCount');
            $allUnresolved = (int) $cards->sum('unresolvedCount');

            $cards->prepend([
                'key' => 'all',
                'count' => $totalLogsCount,
                'resolvedCount' => $allResolved,
                'unresolvedCount' => $allUnresolved,
                'routeParams' => [],
            ]);

            return $cards->all();
        });
    }

    public function archivedLogIdFor(int $logId): ?int
    {
        return $this->logRepository->archivedLogIdFor($logId);
    }
}

<?php

namespace App\Repositories\Eloquent;

use App\Enums\Severity;
use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LogRepository implements LogRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Log
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->findOrFail($id);
    }

    public function latestForStream(int $limit = 10): Collection
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->whereNull('matched_archived_log_id')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function searchAndFilter(
        ?string $search,
        ?string $severity,
        ?string $archived,
        ?string $resolved,
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->when($search, fn ($q) => $q->where('message', 'ilike', '%' . $search . '%'))
            ->when($severity, fn ($q) => $q->where('severity', $severity))
            ->when($archived, function ($q) use ($archived): void {
                if ($archived === 'archived') {
                    $q->whereNotNull('matched_archived_log_id');
                } elseif ($archived === 'not_archived') {
                    $q->whereNull('matched_archived_log_id');
                }
            })
            ->when($resolved, function ($q) use ($resolved): void {
                if ($resolved === 'resolved') {
                    $q->where('resolved', true);
                } elseif ($resolved === 'unresolved') {
                    $q->where('resolved', false);
                }
            })
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function severityResolvedCounts(bool $includeArchived = false): array
    {
        $severities = Severity::values();

        $rows = Log::query()
            ->when(!$includeArchived, fn ($q) => $q->whereNull('matched_archived_log_id'))
            ->selectRaw('severity, resolved, count(*) as count')
            ->whereIn('severity', $severities)
            ->groupBy('severity', 'resolved')
            ->get();

        $result = [];
        foreach ($severities as $severity) {
            $result[$severity] = [
                'resolved' => 0,
                'unresolved' => 0,
                'total' => 0,
            ];
        }

        foreach ($rows as $row) {
            $severity = (string) $row->severity;
            $count = (int) $row->count;
            $bucket = (bool) $row->resolved ? 'resolved' : 'unresolved';

            if (!isset($result[$severity])) {
                continue;
            }

            $result[$severity][$bucket] += $count;
            $result[$severity]['total'] += $count;
        }

        return $result;
    }

    public function logsCount(bool $includeArchived = false): int
    {
        return Log::query()
            ->when(!$includeArchived, fn ($q) => $q->whereNull('matched_archived_log_id'))
            ->count();
    }

    public function archivedLogIdFor(int $logId): ?int
    {
        $matchedId = Log::query()
            ->whereKey($logId)
            ->value('matched_archived_log_id');

        return $matchedId !== null ? (int) $matchedId : null;
    }
}

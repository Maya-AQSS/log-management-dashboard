<?php

namespace App\Services;

use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}

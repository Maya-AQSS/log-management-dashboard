<?php

namespace App\Services;

use App\Dtos\ArchivedLogDto;
use App\Dtos\Pagination\PaginatedDto;
use App\Events\ArchivedLogFieldsWereUpdated;
use App\Events\ArchivedLogWasDeleted;
use App\Events\LogWasArchived;
use App\Models\ArchivedLog;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Support\ResilientLogPublisher;
use Throwable;

class ArchivedLogService implements ArchivedLogServiceInterface
{
    public function __construct(
        private ArchivedLogRepositoryInterface $archivedLogRepository,
        private ResilientLogPublisher $resilientLogPublisher,
    ) {
    }

    private function messagingAppSlug(): string
    {
        return (string) config('messaging.app');
    }

    public function paginate(int $perPage = 15): PaginatedDto
    {
        return PaginatedDto::fromPaginator(
            $this->archivedLogRepository->paginate($perPage),
            static fn (ArchivedLog $m) => ArchivedLogDto::fromModel($m),
        );
    }

    public function searchAndFilter(
        ?array $severities,
        ?int $applicationId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortBy,
        string $sortDir,
        int $perPage = 15
    ): PaginatedDto {
        return PaginatedDto::fromPaginator(
            $this->archivedLogRepository->searchAndFilter(
                $severities,
                $applicationId,
                $dateFrom,
                $dateTo,
                $sortBy,
                $sortDir,
                $perPage
            ),
            static fn (ArchivedLog $m) => ArchivedLogDto::fromModel($m),
        );
    }

    public function findOrFail(int $id): ArchivedLogDto
    {
        return ArchivedLogDto::fromModel($this->findModelOrFail($id));
    }

    public function findModelOrFail(int $id): ArchivedLog
    {
        try {
            return $this->archivedLogRepository->findOrFail($id);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_not_found',
                ['archived_log_id' => $id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): ArchivedLogDto
    {
        try {
            $sanitized = array_map(
                static fn($value) => is_string($value) ? (blank($value) ? null : trim($value)) : $value,
                $fields
            );

            $previousValue = [];
            foreach (array_keys($sanitized) as $key) {
                $previousValue[$key] = $archivedLog->getAttribute($key);
            }

            $this->archivedLogRepository->updateArchivedFields($archivedLog, $sanitized);

            ArchivedLogFieldsWereUpdated::dispatch(
                $archivedLog->id,
                (string) $archivedLog->archived_by_id,
                $previousValue,
                $sanitized,
            );

            $archivedLog->refresh();
            $archivedLog->loadMissing(['application', 'archivedBy', 'errorCode']);
            $archivedLog->loadCount('comments');

            return ArchivedLogDto::fromModel($archivedLog);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_update_failed',
                ['archived_log_id' => $archivedLog->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    public function delete(ArchivedLog $archivedLog): void
    {
        try {
            $archivedLogId = $archivedLog->id;
            $archivedByUserId = (string) $archivedLog->archived_by_id;

            $this->archivedLogRepository->delete($archivedLog);

            ArchivedLogWasDeleted::dispatch($archivedLogId, $archivedByUserId);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_delete_failed',
                ['archived_log_id' => $archivedLog->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    public function archiveFromLogId(int $logId, string $archivedByUserId): ArchivedLogDto
    {
        try {
            $archivedLog = $this->archivedLogRepository->archiveFromLogId($logId, $archivedByUserId);
            LogWasArchived::dispatch($archivedLog, $archivedByUserId);

            return ArchivedLogDto::fromModel($archivedLog);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_archive_failed',
                ['log_id' => $logId],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }
}

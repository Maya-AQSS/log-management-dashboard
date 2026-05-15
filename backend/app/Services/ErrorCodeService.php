<?php

namespace App\Services;

use App\Models\ErrorCode;
use App\Repositories\Contracts\ErrorCodeRepositoryInterface;
use App\Services\Contracts\ErrorCodeServiceInterface;
use App\Support\ResilientLogPublisher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class ErrorCodeService implements ErrorCodeServiceInterface
{
    private const CODE_NOT_FOUND = 'LAR-LOG-010';

    private const CODE_CREATE_FAILED = 'LAR-LOG-011';

    private const CODE_UPDATE_FAILED = 'LAR-LOG-012';

    private const CODE_DELETE_FAILED = 'LAR-LOG-013';

    public function __construct(
        private ErrorCodeRepositoryInterface $errorCodeRepository,
        private ResilientLogPublisher $resilientLogPublisher,
    ) {}

    private function messagingAppSlug(): string
    {
        return (string) config('messaging.app');
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->errorCodeRepository->paginate($perPage);
    }

    public function searchAndFilter(
        ?string $search,
        ?int $filterApp,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->errorCodeRepository->searchAndFilter($search, $filterApp, $perPage);
    }

    /**
     * Sin telemetría en listados (evita ruido); solo se publica a maya.logs si falla la carga por id.
     */
    public function findOrFail(int $id): ErrorCode
    {
        try {
            return $this->errorCodeRepository->findOrFail($id);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                self::CODE_NOT_FOUND,
                ['error_code_id' => $id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Crea un nuevo código de error.
     *
     * @param  array<string, mixed>  $data  Los datos del código de error.
     * @return \App\Models\ErrorCode  El código de error creado.
     */
    public function create(array $data): ErrorCode
    {
        try {
            return $this->errorCodeRepository->create($data);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                self::CODE_CREATE_FAILED,
                ['payload_keys' => array_keys($data)],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Actualiza un código de error.
     *
     * @param  \App\Models\ErrorCode  $errorCode  El código de error que se está actualizando.
     * @param  array<string, mixed>  $data  Los datos del código de error.
     * @return \App\Models\ErrorCode  El código de error actualizado.
     */
    public function update(ErrorCode $errorCode, array $data): ErrorCode
    {
        try {
            return $this->errorCodeRepository->update($errorCode, $data);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                self::CODE_UPDATE_FAILED,
                ['error_code_id' => $errorCode->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Elimina un código de error.
     *
     * @param  \App\Models\ErrorCode  $errorCode  El código de error que se está eliminando.
     */
    public function delete(ErrorCode $errorCode): void
    {
        try {
            DB::transaction(function () use ($errorCode): void {
                $this->errorCodeRepository->delete($errorCode);
            });
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                self::CODE_DELETE_FAILED,
                ['error_code_id' => $errorCode->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }
}

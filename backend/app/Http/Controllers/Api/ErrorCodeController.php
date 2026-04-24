<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreErrorCodeRequest;
use App\Http\Requests\Api\UpdateErrorCodeRequest;
use App\Http\Resources\ErrorCodeResource;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ErrorCodeController extends Controller
{
    public function __construct(
        private ErrorCodeServiceInterface $errorCodeService,
    ) {}

    /**
     * Listado paginado y filtrado de error codes.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 15);

        $paginator = $this->errorCodeService->searchAndFilter(
            search: $request->string('search')->toString() ?: null,
            filterApp: $request->filled('application_id') ? (int) $request->input('application_id') : null,
            perPage: $perPage > 0 ? $perPage : 15,
        );

        return ErrorCodeResource::collection($paginator);
    }

    public function show(int $id): ErrorCodeResource
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $errorCode->loadMissing('application');
        $errorCode->loadCount('comments');

        return new ErrorCodeResource($errorCode);
    }

    public function store(StoreErrorCodeRequest $request): JsonResponse
    {
        $errorCode = $this->errorCodeService->create($request->validated());
        $errorCode->loadMissing('application');

        return (new ErrorCodeResource($errorCode))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateErrorCodeRequest $request, int $id): ErrorCodeResource
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $errorCode = $this->errorCodeService->update($errorCode, $request->validated());
        $errorCode->loadMissing('application');
        $errorCode->loadCount('comments');

        return new ErrorCodeResource($errorCode);
    }

    public function destroy(int $id): JsonResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $this->errorCodeService->delete($errorCode);

        return response()->json(null, 204);
    }
}

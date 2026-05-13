<?php

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ResolvesJwtUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArchivedLogResource;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArchivedLogController extends Controller
{
    use ResolvesJwtUser;

    public function __construct(
        private ArchivedLogServiceInterface $archivedLogService,
    ) {
    }

    /**
     * Listado paginado y filtrado de logs archivados.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 15);
        $severity = $request->input('severity');
        if (is_string($severity)) {
            $severity = array_filter(array_map('trim', explode(',', $severity)), fn(string $v): bool => $v !== '');
        }

        $paginator = $this->archivedLogService->searchAndFilter(
            severities: is_array($severity) && $severity !== [] ? array_values($severity) : null,
            applicationId: $request->filled('application_id') ? (int) $request->input('application_id') : null,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
            sortBy: $request->string('sort_by')->toString() ?: null,
            sortDir: $request->string('sort_dir')->toString() ?: 'desc',
            perPage: $perPage > 0 ? $perPage : 15,
        );

        return ArchivedLogResource::collection($paginator);
    }

    /**
     * Detalle de un log archivado con relaciones estándar.
     */
    public function show(int $id): ArchivedLogResource
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);
        $archivedLog->loadMissing(['application', 'archivedBy', 'errorCode']);
        $archivedLog->loadCount('comments');

        return new ArchivedLogResource($archivedLog);
    }

    /**
     * Actualiza los campos editables del log archivado.
     */
    public function update(Request $request, int $id): ArchivedLogResource
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        $this->authorize('update', $archivedLog);

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:5000'],
            'url_tutorial' => ['nullable', 'url', 'max:2048'],
        ]);

        $this->archivedLogService->updateArchivedFields($archivedLog, $validated);

        $archivedLog->refresh();
        $archivedLog->loadMissing(['application', 'archivedBy', 'errorCode']);
        $archivedLog->loadCount('comments');

        return new ArchivedLogResource($archivedLog);
    }

    /**
     * Elimina (soft delete) un log archivado.
     */
    public function destroy(int $id): JsonResponse
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        $this->authorize('delete', $archivedLog);

        $this->archivedLogService->delete($archivedLog);

        return response()->json(null, 204);
    }
}

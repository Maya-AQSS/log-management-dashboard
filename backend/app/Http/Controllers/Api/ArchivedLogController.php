<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArchivedLogResource;
use App\Models\ArchivedLog;
use App\Models\User;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ArchivedLogController extends Controller
{
    public function __construct(
        private ArchivedLogServiceInterface $archivedLogService,
    ) {}

    /**
     * Listado paginado y filtrado de logs archivados.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 15);
        $severity = $request->input('severity');
        if (is_string($severity)) {
            $severity = array_filter(array_map('trim', explode(',', $severity)), fn (string $v): bool => $v !== '');
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

        $this->authorizeOwner($request, $archivedLog, 'update');

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
    public function destroy(Request $request, int $id): JsonResponse
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        $this->authorizeOwner($request, $archivedLog, 'delete');

        $this->archivedLogService->delete($archivedLog);

        return response()->json(null, 204);
    }

    /**
     * Autoriza que el usuario del JWT sea el propietario del recurso.
     */
    private function authorizeOwner(Request $request, ArchivedLog $archivedLog, string $action): void
    {
        /** @var array<string, mixed>|null $jwtUser */
        $jwtUser = $request->attributes->get('jwt_user');
        $externalId = is_array($jwtUser) ? ($jwtUser['id'] ?? null) : null;

        $user = is_string($externalId) && $externalId !== ''
            ? User::where('external_id', $externalId)->first()
            : null;

        abort_if($user === null, 403);

        Gate::forUser($user)->authorize($action, $archivedLog);
    }
}

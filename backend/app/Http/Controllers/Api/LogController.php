<?php

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ResolvesJwtUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogResource;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class LogController extends Controller
{
    use ResolvesJwtUser;

    public function __construct(
        private LogServiceInterface $logService,
        private ArchivedLogServiceInterface $archivedLogService,
    ) {}

    /**
     * Listado paginado y filtrado de logs activos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 25);
        $severity = $request->input('severity');
        if (is_string($severity)) {
            $severity = array_filter(array_map('trim', explode(',', $severity)), fn (string $v): bool => $v !== '');
        }

        $paginator = $this->logService->searchAndFilter(
            search: $request->string('search')->toString() ?: null,
            severity: is_array($severity) && $severity !== [] ? array_values($severity) : null,
            applicationId: $request->filled('application_id') ? (int) $request->input('application_id') : null,
            archived: $request->string('archived')->toString() ?: null,
            resolved: $request->string('resolved')->toString() ?: null,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
            sortBy: $request->string('sort_by')->toString() ?: null,
            sortDir: $request->string('sort_dir')->toString() ?: null,
            perPage: $perPage > 0 ? $perPage : 25,
        );

        return LogResource::collection($paginator);
    }

    /**
     * Detalle de un log. Incluye el id del ArchivedLog asociado (si existe).
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->logService->findOrFail($id);
        $log->loadMissing(['application', 'errorCode']);

        return response()->json([
            'data' => (new LogResource($log))->resolve(),
            'meta' => [
                'archived_log_id' => $this->logService->archivedLogIdFor($id),
            ],
        ]);
    }

    /**
     * Archiva un log (idempotente). Devuelve el ArchivedLog resultante.
     */
    public function archive(Request $request, int $id): JsonResponse
    {
        $matchedId = $this->logService->archivedLogIdFor($id);
        if ($matchedId !== null) {
            return response()->json([
                'data' => ['archived_log_id' => $matchedId],
                'meta' => ['already_archived' => true],
            ]);
        }

        try {
            $user = $this->resolveJwtUserOrFail($request);

            $archivedLog = $this->archivedLogService->archiveFromLogId($id, $user->id);

            return response()->json([
                'data' => ['archived_log_id' => $archivedLog->id],
                'meta' => ['already_archived' => false],
            ], 201);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'error' => [
                    'code' => 'user_not_found',
                    'message' => __('logs.not_authorized'),
                ],
            ], 403);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => [
                    'code' => 'archive_failed',
                    'message' => __('logs.archived_error'),
                ],
            ], 500);
        }
    }

    /**
     * Marca el log como resuelto.
     */
    public function resolve(int $id): JsonResponse
    {
        $this->logService->resolved($id);

        return response()->json([
            'data' => ['id' => $id, 'resolved' => true],
        ]);
    }

    /**
     * SSE: últimos logs para streaming en tiempo real.
     */
    public function stream(): StreamedResponse
    {
        return response()->stream(function (): void {
            $payload = $this->logService->streamPayload(10);

            echo "event: logs\n";
            echo 'data: '.json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )."\n\n";

            if (function_exists('ob_flush')) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}

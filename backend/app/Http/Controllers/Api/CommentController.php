<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\Contracts\CommentServiceInterface;
use App\Services\PanelUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate as GateFacade;

class CommentController extends Controller
{
    public function __construct(
        private readonly PanelUserService $panelUserService,
        private readonly CommentServiceInterface $commentService,
    ) {
    }

    public function indexForArchivedLog(int $id): AnonymousResourceCollection
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        return $this->indexFor($archivedLog);
    }

    /**
     * Listado de comentarios para un código de error.
     */
    public function indexForErrorCode(int $errorCodeId): AnonymousResourceCollection
    {
        $errorCode = $this->errorCodeService->findOrFail($errorCodeId);

        return $this->indexFor($errorCode);
    }

    /**
     * Crea un nuevo comentario para un log archivado.
     */
    public function storeForArchivedLog(StoreCommentRequest $request, int $archivedLogId): JsonResponse
    {
        $archivedLog = $this->archivedLogService->findOrFail($archivedLogId);

        return $this->storeFor($request, $archivedLog);
    }

    /**
     * Crea un nuevo comentario para un código de error.
     */
    public function storeForErrorCode(StoreCommentRequest $request, int $errorCodeId): JsonResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($errorCodeId);

        return $this->storeFor($request, $errorCode);
    }

    /**
     * Actualiza un comentario.
     */
    public function update(UpdateCommentRequest $request, int $id): CommentResource
    {
        $comment = $this->commentService->findOrFail($id);
        $user = $this->panelUserService->resolveFromJwtRequest($request);

        GateFacade::forUser($user)->authorize('update', $comment);

        $dto = $this->commentService->updateContent($comment, $request->validated('content'));

        return response()->json([
            'data' => (new CommentResource($dto))->resolve($request),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = $this->commentService->findModelOrFail($id);
        $user = $this->panelUserService->resolveFromJwtRequest($request);

        GateFacade::forUser($user)->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json(null, 204);
    }
}

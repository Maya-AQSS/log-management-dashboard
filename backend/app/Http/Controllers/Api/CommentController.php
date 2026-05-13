<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\CommentServiceInterface;
use App\Services\Contracts\ErrorCodeServiceInterface;
use App\Services\PanelUserService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate as GateFacade;

class CommentController extends Controller
{
    public function __construct(
        private readonly PanelUserService $panelUserService,
        private readonly ArchivedLogServiceInterface $archivedLogService,
        private readonly ErrorCodeServiceInterface $errorCodeService,
        private readonly CommentServiceInterface $commentService,
    ) {}

    /**
     * Listado de comentarios para un log archivado.
     */
    public function indexForArchivedLog(int $archivedLogId): AnonymousResourceCollection
    {
        $archivedLog = $this->archivedLogService->findOrFail($archivedLogId);

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

        $comment = $this->commentService->updateContent($comment, $request->validated('content'));

        return new CommentResource($comment);
    }

    /**
     * Elimina un comentario.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = $this->commentService->findOrFail($id);
        $user = $this->panelUserService->resolveFromJwtRequest($request);

        GateFacade::forUser($user)->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json(null, 204);
    }

    /**
     * Listado de comentarios para un modelo comentable.
     */
    private function indexFor(Model $commentable): AnonymousResourceCollection
    {
        return CommentResource::collection(
            $this->commentService->listForCommentable($commentable)
        );
    }

    /**
     * Crea un nuevo comentario para un modelo comentable.
     */
    private function storeFor(StoreCommentRequest $request, Model $commentable): JsonResponse
    {
        $user = $this->panelUserService->resolveFromJwtRequest($request);

        $comment = $this->commentService->createForCommentable(
            $commentable,
            $user->id,
            $request->validated('content')
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}

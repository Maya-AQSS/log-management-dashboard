<?php

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ResolvesJwtUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Requests\Api\UpdateCommentRequest;
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
    ) {
    }

    public function indexForArchivedLog(int $archivedLogId): AnonymousResourceCollection
    {
        return $this->indexFor($this->archivedLogService->findModelOrFail($archivedLogId));
    }

    public function indexForErrorCode(int $errorCodeId): AnonymousResourceCollection
    {
        return $this->indexFor($this->errorCodeService->findModelOrFail($errorCodeId));
    }

    public function storeForArchivedLog(StoreCommentRequest $request, int $archivedLogId): JsonResponse
    {
        return $this->storeFor($request, $this->archivedLogService->findModelOrFail($archivedLogId));
    }

    public function storeForErrorCode(StoreCommentRequest $request, int $errorCodeId): JsonResponse
    {
        return $this->storeFor($request, $this->errorCodeService->findModelOrFail($errorCodeId));
    }

    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        $comment = $this->commentService->findModelOrFail($id);
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

    private function indexFor(Model $commentable): AnonymousResourceCollection
    {
        return CommentResource::collection(
            $this->commentService->listForCommentable($commentable),
        );
    }

    private function storeFor(StoreCommentRequest $request, Model $commentable): JsonResponse
    {
        $user = $this->panelUserService->resolveFromJwtRequest($request);

        $dto = $this->commentService->createForCommentable(
            $commentable,
            $user->id,
            $request->validated('content'),
        );

        return response()->json([
            'data' => (new CommentResource($dto))->resolve($request),
        ], 201);
    }
}

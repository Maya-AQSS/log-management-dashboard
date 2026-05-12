<?php

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ResolvesJwtUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Requests\Api\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\Contracts\CommentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    use ResolvesJwtUser;

    public function __construct(
        private readonly CommentServiceInterface $comments,
    ) {}

    public function indexForArchivedLog(int $archivedLogId): AnonymousResourceCollection
    {
        return CommentResource::collection(
            $this->comments->listForArchivedLog($archivedLogId),
        );
    }

    public function indexForErrorCode(int $errorCodeId): AnonymousResourceCollection
    {
        return CommentResource::collection(
            $this->comments->listForErrorCode($errorCodeId),
        );
    }

    public function storeForArchivedLog(StoreCommentRequest $request, int $archivedLogId): JsonResponse
    {
        $author = $this->resolveJwtUserOrFail($request);

        $comment = $this->comments->storeForArchivedLog(
            $archivedLogId,
            $author,
            (string) $request->validated('content'),
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function storeForErrorCode(StoreCommentRequest $request, int $errorCodeId): JsonResponse
    {
        $author = $this->resolveJwtUserOrFail($request);

        $comment = $this->comments->storeForErrorCode(
            $errorCodeId,
            $author,
            (string) $request->validated('content'),
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCommentRequest $request, int $id): CommentResource
    {
        $comment = $this->comments->findOrFail($id);
        $user = $this->resolveJwtUserOrFail($request);

        Gate::forUser($user)->authorize('update', $comment);

        $comment = $this->comments->update(
            $comment,
            (string) $request->validated('content'),
        );

        return new CommentResource($comment);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = $this->comments->findOrFail($id);
        $user = $this->resolveJwtUserOrFail($request);

        Gate::forUser($user)->authorize('delete', $comment);

        $this->comments->delete($comment);

        return response()->json(null, 204);
    }
}

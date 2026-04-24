<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\ArchivedLog;
use App\Models\Comment;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

class CommentController extends Controller
{
    private const MAX_COMMENT_BYTES = 10 * 1024 * 1024;

    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    public function indexForArchivedLog(int $archivedLogId): AnonymousResourceCollection
    {
        $archivedLog = ArchivedLog::query()->findOrFail($archivedLogId);

        return $this->indexFor($archivedLog);
    }

    public function indexForErrorCode(int $errorCodeId): AnonymousResourceCollection
    {
        $errorCode = ErrorCode::query()->findOrFail($errorCodeId);

        return $this->indexFor($errorCode);
    }

    public function storeForArchivedLog(Request $request, int $archivedLogId): JsonResponse
    {
        $archivedLog = ArchivedLog::query()->findOrFail($archivedLogId);

        return $this->storeFor($request, $archivedLog);
    }

    public function storeForErrorCode(Request $request, int $errorCodeId): JsonResponse
    {
        $errorCode = ErrorCode::query()->findOrFail($errorCodeId);

        return $this->storeFor($request, $errorCode);
    }

    public function update(Request $request, int $id): CommentResource
    {
        $comment = Comment::query()->findOrFail($id);
        $user = $this->resolveUserOrFail($request);

        Gate::forUser($user)->authorize('update', $comment);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3'],
        ]);

        $sanitized = $this->sanitizeAndValidateContent($validated['content']);

        $comment->update(['content' => $sanitized]);
        $comment->refresh()->loadMissing('user');

        return new CommentResource($comment);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = Comment::query()->findOrFail($id);
        $user = $this->resolveUserOrFail($request);

        Gate::forUser($user)->authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }

    private function indexFor(Model $commentable): AnonymousResourceCollection
    {
        $comments = $commentable->comments()
            ->with('user')
            ->latest()
            ->get();

        return CommentResource::collection($comments);
    }

    private function storeFor(Request $request, Model $commentable): JsonResponse
    {
        $user = $this->resolveUserOrFail($request);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3'],
        ]);

        $sanitized = $this->sanitizeAndValidateContent($validated['content']);

        $comment = $commentable->comments()->create([
            'user_id' => $user->id,
            'content' => $sanitized,
        ]);

        $comment->loadMissing('user');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    private function resolveUserOrFail(Request $request): User
    {
        /** @var array<string, mixed>|null $jwtUser */
        $jwtUser = $request->attributes->get('jwt_user');
        $externalId = is_array($jwtUser) ? ($jwtUser['id'] ?? null) : null;

        $user = is_string($externalId) && $externalId !== ''
            ? User::where('external_id', $externalId)->first()
            : null;

        abort_if($user === null, 403);

        return $user;
    }

    private function sanitizeAndValidateContent(string $rawContent): string
    {
        $sanitized = Purifier::clean($rawContent, 'rich_comment');

        $this->validateNotBlank($sanitized);
        $this->validateContentSize($sanitized);
        $this->validateEmbeddedImages($sanitized);

        return $sanitized;
    }

    private function validateNotBlank(string $html): void
    {
        $textOnly = trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $html)));

        if ($textOnly !== '' || str_contains($html, '<img')) {
            return;
        }

        throw ValidationException::withMessages([
            'content' => __('validation.required', ['attribute' => 'content']),
        ]);
    }

    private function validateContentSize(string $html): void
    {
        if (strlen($html) <= self::MAX_COMMENT_BYTES) {
            return;
        }

        throw ValidationException::withMessages([
            'content' => __('comments.editor.comment_too_large'),
        ]);
    }

    private function validateEmbeddedImages(string $html): void
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);
        $sources = $matches[1] ?? [];

        foreach ($sources as $src) {
            if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/s', $src, $parts) !== 1) {
                continue;
            }

            $decoded = base64_decode($parts[2], true);
            if ($decoded === false) {
                throw ValidationException::withMessages([
                    'content' => __('comments.editor.image_invalid_type'),
                ]);
            }

            if (strlen($decoded) > self::MAX_IMAGE_BYTES) {
                throw ValidationException::withMessages([
                    'content' => __('comments.editor.image_too_large'),
                ]);
            }

            if (! $this->isAllowedImageByMagicBytes($decoded)) {
                throw ValidationException::withMessages([
                    'content' => __('comments.editor.image_invalid_type'),
                ]);
            }
        }
    }

    private function isAllowedImageByMagicBytes(string $binary): bool
    {
        $header = substr($binary, 0, 12);

        if (str_starts_with($header, "\x89PNG")) {
            return true;
        }

        if (str_starts_with($header, "\xFF\xD8\xFF")) {
            return true;
        }

        if (str_starts_with($header, 'GIF8')) {
            return true;
        }

        return str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP';
    }
}

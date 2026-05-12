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

    /**
     * Listado de comentarios para un log archivado.
     */
    public function indexForArchivedLog(int $archivedLogId): AnonymousResourceCollection
    {
        $archivedLog = ArchivedLog::query()->findOrFail($archivedLogId);

        return $this->indexFor($archivedLog);
    }

    /**
     * Listado de comentarios para un código de error.
     */
    public function indexForErrorCode(int $errorCodeId): AnonymousResourceCollection
    {
        $errorCode = ErrorCode::query()->findOrFail($errorCodeId);

        return $this->indexFor($errorCode);
    }

    /**
     * Crea un nuevo comentario para un log archivado.
     */
    public function storeForArchivedLog(Request $request, int $archivedLogId): JsonResponse
    {
        $archivedLog = ArchivedLog::query()->findOrFail($archivedLogId);

        return $this->storeFor($request, $archivedLog);
    }

    /**
     * Crea un nuevo comentario para un código de error.
     */
    public function storeForErrorCode(Request $request, int $errorCodeId): JsonResponse
    {
        $errorCode = ErrorCode::query()->findOrFail($errorCodeId);

        return $this->storeFor($request, $errorCode);
    }

    /**
     * Actualiza un comentario.
     */
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

    /**
     * Elimina un comentario.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = Comment::query()->findOrFail($id);
        $user = $this->resolveUserOrFail($request);

        Gate::forUser($user)->authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }

    /**
     * Listado de comentarios para un modelo comentable.
     */
    private function indexFor(Model $commentable): AnonymousResourceCollection
    {
        $comments = $commentable->comments()
            ->with('user')
            ->latest()
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Crea un nuevo comentario para un modelo comentable.
     */
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

    /**
     * Resuelve el {@see User} de la vista FDW `users` (Odoo) usando el mismo identificador
     * que el JWT (`jwt_user['id']`) → columna `users.id` (UUID Keycloak), no `external_id`.
     */
    private function resolveUserOrFail(Request $request): User
    {
        /** @var array<string, mixed>|null $jwtUser */
        $jwtUser = $request->attributes->get('jwt_user');
        $jwtSubject = is_array($jwtUser) ? ($jwtUser['id'] ?? null) : null;

        $user = is_string($jwtSubject) && $jwtSubject !== ''
            ? User::query()->whereKey($jwtSubject)->first()
            : null;

        abort_if($user === null, 403);

        return $user;
    }

    /**
     * Sanitiza y valida el contenido de un comentario.
     */
    private function sanitizeAndValidateContent(string $rawContent): string
    {
        $sanitized = Purifier::clean($rawContent, 'rich_comment');

        $this->validateNotBlank($sanitized);
        $this->validateContentSize($sanitized);
        $this->validateEmbeddedImages($sanitized);

        return $sanitized;
    }

    /**
     * Valida que el contenido no esté en blanco.
     */
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

    /**
     * Valida que el contenido no sea demasiado grande.
     */
    private function validateContentSize(string $html): void
    {
        if (strlen($html) <= self::MAX_COMMENT_BYTES) {
            return;
        }

        throw ValidationException::withMessages([
            'content' => __('comments.editor.comment_too_large'),
        ]);
    }

    /**
     * Valida que las imágenes incrustadas no sean demasiado grandes.
     */
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

    /**
     * Valida que las imágenes incrustadas sean de un tipo permitido.
     */
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

<?php

namespace App\Livewire;

use App\Models\ArchivedLog;
use App\Models\Comment;
use App\Models\ErrorCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;
use Throwable;
use Livewire\Component;

class CommentThread extends Component
{
    use AuthorizesRequests;

    private const MAX_COMMENT_BYTES = 10 * 1024 * 1024;

    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    public string $commentableType;

    public int $commentableId;

    public string $content = '';

    public ?int $editingCommentId = null;

    public string $editingContent = '';

    public ?int $commentIdToDelete = null;

    public function mount(string $commentableType, int $commentableId): void
    {
        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->resolveCommentableModel();
    }

    public function addComment(string $htmlContent): void
    {
        dd("Estoy en addComment.");
    /*
       try {
            $validated = $this->validate([
                'htmlContent' => ['required', 'string', 'min:3'],
            ], [], ['htmlContent' => 'content']);

            $sanitizedContent = $this->sanitizeAndValidateContent($validated['htmlContent'], 'content');

            $this->resolveCommentableModel()
                ->comments()
                ->create([
                    'user_id' => auth()->id(),
                    'content' => $sanitizedContent,
                ]);

            $this->reset('content');
            $this->dispatch('comment-editor-reset');
            session()->flash('status', __('comments.flash.created'));
        } 
         
        catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            Log::error('comment.add.failed', [
                'commentable_type' => $this->commentableType,
                'commentable_id' => $this->commentableId,
                'user_id' => auth()->id(),
                'content_length' => strlen($this->content),
                'message' => $e->getMessage(),
            ]);

            session()->flash('status', $this->errorStatus($e));
        }
     */
       
    }

    public function startEditing(int $commentId): void
    {
        $this->editingCommentId = $commentId;

        $validated = $this->validate([
            'editingCommentId' => ['required', 'integer', 'exists:comments,id'],
        ]);

        $comment = $this->findCommentOrFail($validated['editingCommentId']);

        $this->authorize('update', $comment);

        $this->editingContent = $comment->content;
    }

    public function updateComment(int $commentId, string $htmlContent): void
    {
        try {
            $validated = $this->validate([
                'commentId' => ['required', 'integer', 'exists:comments,id'],
                'htmlContent' => ['required', 'string', 'min:3'],
            ], [], ['commentId' => 'comment', 'htmlContent' => 'content']);

            $sanitizedContent = $this->sanitizeAndValidateContent($validated['htmlContent'], 'content');

            $comment = $this->findCommentOrFail($validated['commentId']);

            $this->authorize('update', $comment);

            $comment->update([
                'content' => $sanitizedContent,
            ]);

            $this->cancelEditing();
            session()->flash('status', __('comments.flash.updated'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            Log::error('comment.update.failed', [
                'comment_id' => $validated['editingCommentId'] ?? null,
                'user_id' => auth()->id(),
                'content_length' => strlen($this->editingContent),
                'message' => $e->getMessage(),
            ]);

            session()->flash('status', $this->errorStatus($e));
        }
    }

    public function deleteComment(int $commentId): void
    {
        try {
            $this->commentIdToDelete = $commentId;

            $validated = $this->validate([
                'commentIdToDelete' => ['required', 'integer', 'exists:comments,id'],
            ]);

            $comment = $this->findCommentOrFail($validated['commentIdToDelete']);

            $this->authorize('delete', $comment);

            $comment->delete();

            if ($this->editingCommentId === $commentId) {
                $this->cancelEditing();
            }

            $this->reset('commentIdToDelete');
            session()->flash('status', __('comments.flash.deleted'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            Log::error('comment.delete.failed', [
                'comment_id' => $commentId,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            session()->flash('status', $this->errorStatus($e));
        }
    }

    public function cancelEditing(): void
    {
        $this->reset(['editingCommentId', 'editingContent']);
    }

    public function render(): View
    {
        return view('livewire.comment-thread', [
            'comments' => $this->commentsQuery()->get(),
        ]);
    }

    private function commentsQuery(): Builder
    {
        return Comment::query()
            ->with('user')
            ->where('commentable_type', $this->resolveCommentableClass())
            ->where('commentable_id', $this->commentableId)
            ->latest();
    }

    private function findCommentOrFail(int $commentId): Comment
    {
        return $this->commentsQuery()
            ->whereKey($commentId)
            ->firstOrFail();
    }

    private function resolveCommentableModel(): ArchivedLog|ErrorCode
    {
        $class = $this->resolveCommentableClass();

        return $class::query()->findOrFail($this->commentableId);
    }

    private function resolveCommentableClass(): string
    {
        $map = $this->commentableTypeMap();
        $class = $map[$this->commentableType] ?? null;

        if ($class === null) {
            abort(404);
        }

        $allowedTypes = (new Comment())->allowedTypes();
        if (!in_array($class, $allowedTypes, true)) {
            abort(404);
        }

        return $class;
    }

    private function sanitizeAndValidateContent(string $rawContent, string $field): string
    {
        $sanitized = Purifier::clean($rawContent, 'rich_comment');

        $this->validateNotBlank($sanitized, $field);
        $this->validateContentSize($sanitized, $field);
        $this->validateEmbeddedImages($sanitized, $field);

        return $sanitized;
    }

    private function validateNotBlank(string $html, string $field): void
    {
        $textOnly = trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $html)));

        if ($textOnly !== '' || str_contains($html, '<img')) {
            return;
        }

        throw ValidationException::withMessages([
            $field => __('validation.required', ['attribute' => $field]),
        ]);
    }

    private function validateContentSize(string $html, string $field): void
    {
        if (strlen($html) <= self::MAX_COMMENT_BYTES) {
            return;
        }

        throw ValidationException::withMessages([
            $field => __('comments.editor.comment_too_large'),
        ]);
    }

    private function validateEmbeddedImages(string $html, string $field): void
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
                    $field => __('comments.editor.image_invalid_type'),
                ]);
            }

            if (strlen($decoded) > self::MAX_IMAGE_BYTES) {
                throw ValidationException::withMessages([
                    $field => __('comments.editor.image_too_large'),
                ]);
            }

            if (!$this->isAllowedImageByMagicBytes($decoded)) {
                throw ValidationException::withMessages([
                    $field => __('comments.editor.image_invalid_type'),
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

    /**
     * Mapa único slug -> clase para resolver el commentable.
     *
     * @return array<string, class-string>
     */
    private function commentableTypeMap(): array
    {
        return [
            'archived-log' => ArchivedLog::class,
            'error-code' => ErrorCode::class,
        ];
    }

    private function errorStatus(Throwable $e): string
    {
        if (!config('app.debug')) {
            return __('comments.flash.error');
        }

        return __('comments.flash.error').': '.$e->getMessage();
    }
}
<?php

namespace App\Livewire;

use App\Models\ArchivedLog;
use App\Models\Comment;
use App\Models\ErrorCode;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;
use Livewire\Component;

class CommentThread extends Component
{
    use AuthorizesRequests;

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

    public function addComment(): void
    {
        $validated = $this->validate([
            'content' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        try {
            $this->resolveCommentableModel()
                ->comments()
                ->create([
                    'user_id' => auth()->id(),
                    'content' => $validated['content'],
                ]);

            $this->reset('content');
            session()->flash('status', __('comments.flash.created'));
        } catch (Throwable $e) {
            report($e);
            session()->flash('status', __('comments.flash.error'));
        }
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

    public function updateComment(): void
    {
        $validated = $this->validate([
            'editingCommentId' => ['required', 'integer', 'exists:comments,id'],
            'editingContent' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $comment = $this->findCommentOrFail($validated['editingCommentId']);

        $this->authorize('update', $comment);

        try {
            $comment->update([
                'content' => $validated['editingContent'],
            ]);

            $this->cancelEditing();
            session()->flash('status', __('comments.flash.updated'));
        } catch (Throwable $e) {
            report($e);
            session()->flash('status', __('comments.flash.error'));
        }
    }

    public function deleteComment(int $commentId): void
    {
        $this->commentIdToDelete = $commentId;

        $validated = $this->validate([
            'commentIdToDelete' => ['required', 'integer', 'exists:comments,id'],
        ]);

        $comment = $this->findCommentOrFail($validated['commentIdToDelete']);

        $this->authorize('delete', $comment);

        try {
            $comment->delete();

            if ($this->editingCommentId === $commentId) {
                $this->cancelEditing();
            }

            $this->reset('commentIdToDelete');
            session()->flash('status', __('comments.flash.deleted'));
        } catch (Throwable $e) {
            report($e);
            session()->flash('status', __('comments.flash.error'));
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
}
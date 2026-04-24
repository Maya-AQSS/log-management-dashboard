<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        $canEdit = $authUser !== null && Gate::forUser($authUser)->check('update', $this->resource);
        $canDelete = $authUser !== null && Gate::forUser($authUser)->check('delete', $this->resource);

        return [
            'id' => $this->id,
            'content' => $this->content,
            'commentable_type' => $this->commentable_type,
            'commentable_id' => $this->commentable_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Dtos\CommentDto;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dto = $this->resource instanceof CommentDto
            ? $this->resource
            : CommentDto::fromModel($this->resource);

        $authUser = $request->user();
        $canEdit = $authUser !== null && Gate::forUser($authUser)->check('update', $dto->source);
        $canDelete = $authUser !== null && Gate::forUser($authUser)->check('delete', $dto->source);

        $payload = [
            'id' => $dto->id,
            'content' => $dto->content,
            'commentable_type' => $dto->commentableType,
            'commentable_id' => $dto->commentableId,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
        ];

        if ($dto->userLoaded) {
            $payload['user'] = $dto->user !== null
                ? ['id' => $dto->user->id, 'name' => $dto->user->name]
                : null;
        }

        return $payload;
    }
}

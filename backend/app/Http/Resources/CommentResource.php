<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
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
        $authUser = $this->resolveViewerForGates($request);
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

    /**
     * Las rutas API usan middleware {@code jwt} y {@see PanelUserService} en controladores;
     * {@see Request::user()} suele ser null. Los flags can_* deben usar el mismo actor que update/delete.
     */
    private function resolveViewerForGates(Request $request): ?User
    {
        $user = $request->user();
        if ($user instanceof User) {
            return $user;
        }

        $jwtUser = $request->attributes->get('jwt_user');
        $subject = is_array($jwtUser) ? ($jwtUser['id'] ?? null) : null;
        if (! is_string($subject) || $subject === '') {
            return null;
        }

        return app(UserRepositoryInterface::class)->findByKey($subject);
    }
}

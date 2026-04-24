<?php

namespace App\Http\Resources;

use App\Models\ErrorCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ErrorCode
 */
class ErrorCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'file' => $this->file,
            'line' => $this->line,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'application' => $this->whenLoaded('application', fn () => [
                'id' => $this->application->id,
                'name' => $this->application->name,
            ]),
            'comments_count' => $this->when(isset($this->comments_count), fn () => (int) $this->comments_count),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Dtos\ErrorCodeDto;
use App\Models\ErrorCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dto = $this->resource instanceof ErrorCodeDto
            ? $this->resource
            : ErrorCodeDto::fromModel($this->resource);

        $payload = [
            'id' => $dto->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'file' => $dto->file,
            'line' => $dto->line,
            'description' => $dto->description,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
        ];

        if ($dto->applicationLoaded) {
            $payload['application'] = $dto->application !== null
                ? ['id' => $dto->application->id, 'name' => $dto->application->name]
                : null;
        }

        if ($dto->commentsCount !== null) {
            $payload['comments_count'] = $dto->commentsCount;
        }

        return $payload;
    }
}

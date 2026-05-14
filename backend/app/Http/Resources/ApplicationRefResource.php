<?php

namespace App\Http\Resources;

use App\Dtos\ApplicationRefDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationRefResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dto = $this->resource instanceof ApplicationRefDto
            ? $this->resource
            : new ApplicationRefDto(
                id: (int) $this->resource->id,
                name: (string) $this->resource->name,
            );

        return [
            'id' => $dto->id,
            'name' => $dto->name,
        ];
    }
}

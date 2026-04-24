<?php

namespace App\Http\Resources;

use App\Models\ArchivedLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ArchivedLog
 */
class ArchivedLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'severity' => $this->severity,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'metadata_formatted' => $this->metadata_formatted,
            'description' => $this->description,
            'url_tutorial' => $this->url_tutorial,
            'original_created_at' => $this->original_created_at?->toIso8601String(),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'application' => $this->whenLoaded('application', fn () => $this->application ? [
                'id' => $this->application->id,
                'name' => $this->application->name,
            ] : null),
            'archived_by' => $this->whenLoaded('archivedBy', fn () => $this->archivedBy ? [
                'id' => $this->archivedBy->id,
                'name' => $this->archivedBy->name,
            ] : null),
            'error_code' => $this->whenLoaded('errorCode', fn () => $this->errorCode ? [
                'id' => $this->errorCode->id,
                'code' => $this->errorCode->code,
                'name' => $this->errorCode->name,
            ] : null),
            'comments_count' => $this->when(isset($this->comments_count), fn () => (int) $this->comments_count),
        ];
    }
}

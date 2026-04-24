<?php

namespace App\Http\Resources;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Log
 */
class LogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'severity' => $this->severity,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'resolved' => (bool) $this->resolved,
            'file' => $this->file,
            'line' => $this->line,
            'created_at' => $this->created_at?->toIso8601String(),
            'application' => $this->whenLoaded('application', fn () => [
                'id' => $this->application->id,
                'name' => $this->application->name,
            ]),
            'error_code' => $this->whenLoaded('errorCode', fn () => $this->errorCode ? [
                'id' => $this->errorCode->id,
                'code' => $this->errorCode->code,
                'name' => $this->errorCode->name,
            ] : null),
        ];
    }
}

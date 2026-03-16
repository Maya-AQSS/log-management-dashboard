<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArchivedLog extends Model
{
    use HasFactory;

    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'application_id',
        'archived_by_id',
        'error_code_id',
        'severity',
        'message',
        'metadata',
        'description',
        'url_tutorial',
        'original_created_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'original_created_at' => 'datetime',
            'archived_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_id');
    }

    public function errorCode(): BelongsTo
    {
        return $this->belongsTo(ErrorCode::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'matched_archived_log_id');
    }
}

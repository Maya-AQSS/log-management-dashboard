<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ErrorCode extends Model
{
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        // Cascade delete comments when error code is deleted
        static::deleting(function (self $errorCode) {
            $errorCode->comments()->delete();
        });
    }

    protected $fillable = [
        'code',
        'application_id',
        'name',
        'file',
        'line',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'line' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function archivedLogs(): HasMany
    {
        return $this->hasMany(ArchivedLog::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

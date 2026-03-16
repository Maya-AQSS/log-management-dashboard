<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ArchivedLog extends Model
{
    /** @use HasFactory<\Database\Factories\ArchivedLogFactory> */
    use HasFactory;

    protected $table = 'archived_logs';
    
    protected $fillable = [
        'original_log_id',
        'archived_by_id',
        'type',
        'app_source',
        'message',
        'metadata',
        'archived_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'archived_at' => 'datetime',
        'archived_by_id' => 'integer',
    ];


    public function log(): BelongsTo
    {
        return $this->belongsTo(Log::class, 'original_log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


}

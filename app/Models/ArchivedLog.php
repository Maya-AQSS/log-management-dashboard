<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by_id');
    }

    public function errorCode()
    {
        return $this->belongsTo(ErrorCode::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'application_id',
        'name',
        'description',
        'severity',
        'default_role_id',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function archivedLogs()
    {
        return $this->hasMany(ArchivedLog::class);
    }

    public function comments()
    {
        return $this->hasMany(ErrorCodeComment::class);
    }
}

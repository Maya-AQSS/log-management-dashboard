<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'resolved' => 'boolean',
            'created_at' => 'datetime',
            'line' => 'integer',
        ];
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function errorCode()
    {
        return $this->belongsTo(ErrorCode::class);
    }
}

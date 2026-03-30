<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function archivedLogs(): HasMany
    {
        return $this->hasMany(ArchivedLog::class);
    }

    public function errorCodes(): HasMany
    {
        return $this->hasMany(ErrorCode::class);
    }
}

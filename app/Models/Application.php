<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'description', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function archivedLogs()
    {
        return $this->hasMany(ArchivedLog::class);
    }

    public function errorCodes()
    {
        return $this->hasMany(ErrorCode::class);
    }
}

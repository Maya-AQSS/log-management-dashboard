<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Log extends Model
{
    /** @use HasFactory<\Database\Factories\LogFactory> */
    use HasFactory;

    protected $table = 'logs';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'archived' => 'boolean',
        'created_at' => 'datetime',
    ];

    
    // TODO descomentar cuando tengamos la tabla de logs real
    
    // protected static function booted(): void
    // {
    //     static::creating(fn () => false);
    //     static::updating(fn () => false);
    //     static::deleting(fn () => false);
    //     static::saving(fn () => false);
    // }
}

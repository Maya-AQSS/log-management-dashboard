<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Los Events de dominio que implementan {@see \Maya\Messaging\Contracts\AuditableEvent}
 * los recoge el wildcard registrado por `MessagingServiceProvider::boot()` del
 * package `maya-shared-messaging-laravel` — no se registran aquí Listeners locales.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

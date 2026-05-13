<?php

namespace App\Providers;

use App\Events\ArchivedLogFieldsWereUpdated;
use App\Events\ArchivedLogWasDeleted;
use App\Events\LogWasArchived;
use App\Listeners\RecordArchivedLogArchiveAudit;
use App\Listeners\RecordArchivedLogDeleteAudit;
use App\Listeners\RecordArchivedLogUpdateAudit;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        LogWasArchived::class => [
            RecordArchivedLogArchiveAudit::class,
        ],
        ArchivedLogWasDeleted::class => [
            RecordArchivedLogDeleteAudit::class,
        ],
        ArchivedLogFieldsWereUpdated::class => [
            RecordArchivedLogUpdateAudit::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

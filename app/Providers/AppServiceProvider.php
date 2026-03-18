<?php

namespace App\Providers;

use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Repositories\Eloquent\ArchivedLogRepository;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\ArchivedLogService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArchivedLogRepositoryInterface::class, ArchivedLogRepository::class);
        $this->app->bind(ArchivedLogServiceInterface::class, ArchivedLogService::class);
    }

    public function boot(): void
    {
        //
    }
}
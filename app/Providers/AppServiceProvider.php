<?php

namespace App\Providers;

use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Repositories\Eloquent\ArchivedLogRepository;
use App\Repositories\Eloquent\LogRepository;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use App\Services\ArchivedLogService;
use App\Services\LogService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArchivedLogRepositoryInterface::class, ArchivedLogRepository::class);
        $this->app->singleton(ArchivedLogServiceInterface::class, ArchivedLogService::class);

        $this->app->singleton(LogRepositoryInterface::class, LogRepository::class);
        $this->app->singleton(LogServiceInterface::class, LogService::class);
    }

    public function boot(): void
    {
        //
    }
}

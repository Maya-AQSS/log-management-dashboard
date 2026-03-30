<?php

namespace App\Providers;

use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Repositories\Contracts\ErrorCodeRepositoryInterface;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Repositories\Eloquent\ArchivedLogRepository;
use App\Repositories\Eloquent\ErrorCodeRepository;
use App\Repositories\Eloquent\LogRepository;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\ErrorCodeServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use App\Services\ArchivedLogService;
use App\Services\ErrorCodeService;
use App\Services\LogService;
use App\Support\AuthExternalUrlGuard;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArchivedLogRepositoryInterface::class, ArchivedLogRepository::class);
        $this->app->singleton(ArchivedLogServiceInterface::class, ArchivedLogService::class);

        $this->app->singleton(LogRepositoryInterface::class, LogRepository::class);
        $this->app->singleton(LogServiceInterface::class, LogService::class);

        $this->app->singleton(ErrorCodeRepositoryInterface::class, ErrorCodeRepository::class);
        $this->app->singleton(ErrorCodeServiceInterface::class, ErrorCodeService::class);
    }

    public function boot(): void
    {
        AuthExternalUrlGuard::assertConfiguredForDeploy(
            (string) $this->app->environment(),
            (string) config('services.auth_gateway.external_url', '')
        );
    }
}

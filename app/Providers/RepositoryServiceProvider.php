<?php

namespace App\Providers;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\MonthRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\MonthRepository;
use App\Repositories\Eloquent\ProjectRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(MonthRepositoryInterface::class, MonthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\TimeRecordRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\TimeRecordRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TimeRecordRepositoryInterface::class, TimeRecordRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use App\Models\ChatPost;
use App\Models\Reports;
use App\Observers\ChatPostObserver;
use App\Observers\ReportsObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        ChatPost::observe(ChatPostObserver::class);
        Reports::observe(ReportsObserver::class);
        //
    }
}

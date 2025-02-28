<?php

namespace App\Providers;

use App\Services\BetParserService;
use Illuminate\Support\ServiceProvider;

class BetParserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(BetParserService::class, function ($app) {
            return new BetParserService();
        });
    }

    public function boot()
    {
        //
    }
}
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('lottery:fetch --process')
    ->dailyAt('18:30')
    ->appendOutputTo(storage_path('logs/lottery-fetch.log'));

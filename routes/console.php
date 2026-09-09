<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule SPS reminder notifications
Schedule::command('push:sps-reminder --time=morning')->dailyAt('07:00');
Schedule::command('push:sps-reminder --time=afternoon')->dailyAt('13:00');
Schedule::command('push:sps-reminder --time=evening')->dailyAt('18:00');

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule meter generation command
Schedule::command('meter:generate-readings')
    ->monthlyOn(28, '0:00') // runs on 28th of every month at 00:00 AM
    ->withoutOverlapping()
    ->runInBackground();

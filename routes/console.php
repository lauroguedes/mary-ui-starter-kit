<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('app.demo.enabled')) {
    $schedule = match (config('app.demo.reset_schedule')) {
        'daily' => Schedule::command('demo:reset --force')->daily(),
        'weekly' => Schedule::command('demo:reset --force')->weekly(),
        default => Schedule::command('demo:reset --force')->hourly(),
    };

    $schedule->withoutOverlapping()->runInBackground();
}

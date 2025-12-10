<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Scheduler untuk auto-cancel pending orders
 * Dijalankan setiap menit untuk mengecek orders yang expired
 */
Schedule::command('orders:auto-cancel')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

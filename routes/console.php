<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('opname:auto-cancel-stale')->hourly();
Schedule::command('stock:daily-low-stock-digest')->dailyAt('07:00');
Schedule::command('snapshot:monthly-asset-value')->dailyAt('00:05');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

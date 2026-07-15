<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('enrichment:process --limit=20')->everyFiveMinutes();
Schedule::command('news:refresh --limit=50')->hourly();
Schedule::command('digest:weekly')->weeklyOn(1, '8:00');
Schedule::command('casinos:check-links --limit=200')->daily();
Schedule::command('betting:expire-markets')->hourly();
Schedule::command('betting:finalize-disputes')->everyFifteenMinutes();
Schedule::command('betting:advance-events')->everyFiveMinutes();

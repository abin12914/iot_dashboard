<?php

use App\Jobs\AggregateDailyTelematicsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Console Tasks
|--------------------------------------------------------------------------
|
| Nightly job to aggregate intervals into daily fleet metrics at 00:05 AM.
| Pushes an asynchronous job to the queue worker without blocking cron.
|
*/
Schedule::job(new AggregateDailyTelematicsJob())->dailyAt('00:05');

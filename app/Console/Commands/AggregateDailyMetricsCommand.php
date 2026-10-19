<?php

namespace App\Console\Commands;

use App\Jobs\AggregateDailyTelematicsJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AggregateDailyMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:aggregate-daily {date? : The date to aggregate (YYYY-MM-DD), defaults to yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roll up operational activity intervals into daily telematics KPIs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->argument('date') ?: Carbon::yesterday()->toDateString();

        $this->info("Dispatching daily telematics aggregation for {$date}...");

        AggregateDailyTelematicsJob::dispatchSync($date);

        $this->info("Telematics daily metrics updated successfully for {$date}!");

        return Command::SUCCESS;
    }
}

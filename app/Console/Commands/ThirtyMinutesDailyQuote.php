<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\APIController;

class ThirtyMinutesDailyQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:thirtyMinutes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger API call at a specified interval';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $trigger_time = (new APIController())->trigger_cron();

        if (!empty($trigger_time)) {
            $this->info('Successfully triggered API call  at ' . $trigger_time);
        } else {
            $this->info('API call failed at ' . $trigger_time);
        }
    }
}

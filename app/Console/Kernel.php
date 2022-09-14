<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Event;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ThirtyMinutesDailyQuote::class,
    ];

    /**
     * Inject your command in $commands variable
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $interval = config('msgraph.interval');

        switch ($interval) {
            case 'everyMinute':
                $schedule->command('quote:thirtyMinutes')
                    ->everyMinute();
                break;
            case 'everyFiveMinutes':
                $schedule->command('quote:thirtyMinutes')
                    ->everyFiveMinutes();
                break;
            case 'everyFifteenMinutes':
                $schedule->command('quote:thirtyMinutes')
                    ->everyFifteenMinutes();
                break;
            case 'hourly':
                $schedule->command('quote:thirtyMinutes')
                    ->hourly();
                break;
            case 'everyTwoHours':
                $schedule->command('quote:thirtyMinutes')
                    ->everyTwoHours();
                break;
            case 'everyThreeHours':
                $schedule->command('quote:thirtyMinutes')
                    ->everyThreeHours();
                break;
            case 'everySixHours':
                $schedule->command('quote:thirtyMinutes')
                    ->everySixHours();
                break;
            case 'daily':
                $schedule->command('quote:thirtyMinutes')
                    ->daily();
                break;
            case 'weekly':
                $schedule->command('quote:thirtyMinutes')
                    ->weekly();
                break;
            case 'monthly':
                $schedule->command('quote:thirtyMinutes')
                    ->monthly();
                break;
            case 'quarterly':
                $schedule->command('quote:thirtyMinutes')
                    ->quarterly();
                break;
            case 'yearly':
                $schedule->command('quote:thirtyMinutes')
                    ->yearly();
                break;
            default:
                $schedule->command('quote:thirtyMinutes')
                    ->everyThirtyMinutes();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

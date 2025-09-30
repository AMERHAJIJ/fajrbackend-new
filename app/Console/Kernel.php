<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UpdateGoogleSheets::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Update Google Sheets with statistics every day at midnight
        $schedule->command('sheets:update')
                 ->dailyAt('00:00')
                 ->timezone('Asia/Riyadh')
                 ->onOneServer();
                 
        // Check missing teacher tasks every hour
        $schedule->command('teacher-tasks:check')
                 ->hourly()
                 ->timezone('Asia/Riyadh');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

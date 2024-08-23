<?php

namespace App\Console;

use App\Console\Commands\BackupDatabaseToS3;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [

    ];


    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:database-s3')->dailyAt('05:30');
        $schedule->command('backup:database-s3')->weekly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

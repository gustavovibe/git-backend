<?php

namespace App\Console;

use App\Console\Commands\BackupDatabaseToS3;
use App\Console\Commands\SyncToursData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
      Commands\ProcessPendingAttempts::class,
      Commands\SyncToursData::class,
    ];
    
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:database-s3')->dailyAt('05:30');
        $schedule->command('backup:database-s3')->weekly();
        $schedule->command('process:pending-attempts')->everyThirtyMinutes();
        $schedule->command('sync:tours')
         ->weeklyOn(0, '00:00');     // 0 = Sunday
        $schedule->command('sync:weekly-tour-health')
            ->weekly()   // runs every Monday at 00:00 by default
            ->withoutOverlapping()
            ->onOneServer();    
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}

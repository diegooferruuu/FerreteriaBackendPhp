<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('app:sincronizar-fecha')->dailyAt('05:45');
//        $schedule->command('app:sincronizar-fecha')->everyMinute();
        $schedule->command('cufd:store')->dailyAt('06:00');
        $schedule->command('app:solicitud-sincronizacion-catalogo')->dailyAt('06:05');

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

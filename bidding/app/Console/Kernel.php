<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Buscar licitações diariamente
        $schedule->command('biddings:fetch')->dailyAt('03:00');
    }

    // ...
}

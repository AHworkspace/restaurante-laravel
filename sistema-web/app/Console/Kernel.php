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
        Commands\TestDbConnection::class,
        Commands\ProcessEmailCommands::class,
        Commands\EmailScheduler::class,
        Commands\ProcessEmailsCommand::class,
        Commands\SetupEmailSystemCommand::class,
        Commands\ProcessEmailsMockCommand::class,
        Commands\TestNativeEmailCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * These schedules are used to run the console commands.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Procesar emails cada minuto
        $schedule->command('email:schedule')->everyMinute();

        // También puedes usar el comando directo si prefieres
        // $schedule->command('email:process')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

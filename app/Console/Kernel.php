<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\NewsCron',
        'App\Console\Commands\GoogleNewsCron',
        'App\Console\Commands\GoogleNewsCronID',
        'App\Console\Commands\GoogleNewsCronAU',
        'App\Console\Commands\TelegramCron',
        'App\Console\Commands\InternationalNewsCron',
        'App\Console\Commands\FoxNewsCron',
        'App\Console\Commands\CnnNewsCron',
        'App\Console\Commands\BbcNewsCron',
        'App\Console\Commands\RtNewsCron',
        'App\Console\Commands\PressTvNewsCron',
        'App\Console\Commands\IrnaNewsCron',
        'App\Console\Commands\KhameneiIrNewsCron',
        'App\Console\Commands\InfoBricsNewsCron',
        'App\Console\Commands\TvBricsNewsCron',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('etl:presstvnews')->everyFiveMinutes();
        $schedule->command('etl:irnanews')->everyFiveMinutes();
        $schedule->command('etl:khameneinews')->everyFiveMinutes();
        $schedule->command('etl:infobricsnews')->everyFiveMinutes();
        $schedule->command('etl:tvbricsnews')->everyFiveMinutes();
        $schedule->command('etl:googlenewsus')->everyFiveMinutes();
        $schedule->command('etl:googlenewsid')->everyFiveMinutes();
        $schedule->command('etl:googlenewsau')->everyFiveMinutes();
        $schedule->command('etl:rtnews')->everyFiveMinutes();
        $schedule->command('etl:foxnews')->hourly();
        $schedule->command('etl:cnnnews')->hourly();
        $schedule->command('etl:bbcnews')->hourly();
        $schedule->command('etl:newsapi')->hourly();
        //$schedule->command('etl:news')->daily();
    }
}

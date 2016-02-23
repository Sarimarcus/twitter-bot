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
        Commands\Inspire::class,
        Commands\RetweetTrending::class,
        Commands\TweetInspire::class,
        Commands\TweetInterest::class,
        Commands\FollowUsers::class,
        Commands\PurgeUsers::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('twitter:tweet-interest')
                 ->everyThirtyMinutes();

        $schedule->command('twitter:follow-users')
                 ->everyThirtyMinutes();

        $schedule->command('twitter:purge-users')
                 ->daily();

        $schedule->command('twitter:retweet-trending')
                 ->weekdays()->at('14:00');

        $schedule->command('twitter:tweet-inspire')
                 ->weekly()->fridays()->at('16:00');
    }
}

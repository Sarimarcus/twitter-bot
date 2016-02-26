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
        Commands\UnfollowUsers::class,
        Commands\PurgeUsers::class,
        Commands\PurgeUsers::class,
        Commands\GetSuggested::class,
        Commands\SavePopularTweets::class
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

        $schedule->command('twitter:save-popular-tweets')
                 ->twiceDaily(1, 13);

        $schedule->command('twitter:unfollow-users')
                 ->daily();

        $schedule->command('twitter:purge-users')
                 ->daily();

        $schedule->command('twitter:get-suggested')
                 ->daily();

        $schedule->command('twitter:retweet-trending')
                 ->weekdays()->at('14:00');

        $schedule->command('twitter:tweet-inspire')
                 ->weekly()->fridays()->at('16:00');
    }
}

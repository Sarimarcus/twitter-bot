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
        Commands\SavePopularTweets::class,
        Commands\UpdateBotInformation::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('twitter:follow-users')
                 ->everyTenMinutes();

        $schedule->command('twitter:tweet-interest')
                 ->everyTenMinutes();

        $schedule->command('twitter:save-popular-tweets')
                 ->twiceDaily(1, 13);

        $schedule->command('twitter:unfollow-users')
                 ->twiceDaily(2, 14);

        $schedule->command('twitter:purge-users')
                 ->daily();

        $schedule->command('twitter:get-suggested')
                 ->daily();

        $schedule->command('twitter:update-bot-information')
                 ->daily();

        $schedule->command('twitter:retweet-trending')
                 ->weekdays()->at('14:00');

        $schedule->command('twitter:tweet-inspire')
                 ->weekly()->fridays()->at('16:00');
    }
}

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
        Commands\UpdateBotInformation::class,
        Commands\GetQOTD::class,
        Commands\GetInspiration::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        /*
         * Poem Maker tasks
         */

        $schedule->command('twitter:follow-users')
                 ->everyTenMinutes();

         /*
          * Twitter Bot tasks
          */

        $schedule->command('twitter:follow-users')
                 ->everyTenMinutes();

        $schedule->command('twitter:tweet-interest')
                 ->everyThirtyMinutes();

        $schedule->command('twitter:save-popular-tweets')
                 ->twiceDaily(1, 13);

        $schedule->command('twitter:unfollow-users')
                 ->hourly();

        $schedule->command('twitter:retweet-trending')
                 ->weekdays()->at('14:00');

        $schedule->command('twitter:tweet-inspire')
                 ->weekdays()->at('10:00');

        $schedule->command('twitter:purge-users')
                 ->daily();

        $schedule->command('twitter:get-suggested')
                 ->daily();

        $schedule->command('twitter:update-bot-information')
                 ->daily();

        $schedule->command('quote:get-qotd')
                 ->daily();
    }
}

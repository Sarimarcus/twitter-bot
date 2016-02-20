<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetweetTrending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:retweet-trending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retweet one trending tweet from Paris';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /* Getting trends
         * 615702 is the WOEID for Paris
         */
        $trends = \Twitter::getTrendsPlace(['id' => 615702, 'format' => 'array']);
        $topTrend = $trends[0]['trends'][(rand(0,4))]['name'];

        /* Getting trending tweets */
        $tweets = \Twitter::getSearch(['q' => $topTrend, 'result-type' => 'popular', 'lang' => 'fr', 'format' => 'array']);
        $topTweet = $tweets['statuses'][(rand(0,4))]['id'];

        /* Retweeting one */
        \Twitter::postRt($topTweet);
    }
}

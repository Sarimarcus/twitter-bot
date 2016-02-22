<?php

namespace App\Console\Commands;

use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class TweetInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:tweet-interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweet a link from interesting user';

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
        /* Some interesting users */
        $username = Collection::make([

            'getthelook_fr',
            'ellefrance',
            'vogueparis',
            'CausetteLeMag',
            'FashionMagFR',
            'VanityFairFR',
            'marieclaire_fr',
            'TheBeautyst',
            'TTTmagazine',
            'stylistfrance',
            'puretrend',
            'lofficielparis',
            'grazia_fr',
            'flowmagazine_fr',
            'somanyparis',
            'My_Little_Paris',
            'LEXPRESS_Styles',
            'Terrafemina'

        ])->random();

        /* Getting tweets from account */
        $tweets = \Twitter::getUserTimeline(['screen_name' => $username, 'format' => 'array']);

        switch (rand(0,8)) {

            /* Trying to fake a tweet */
            case 0:
            case 1:

                /* Some introduction to the tweet */
                $intro = Collection::make([

                    'Vous aviez vu cet article ? C\'est un peu too much, non ? ',
                    'Ce genre de trucs me branche vraiment, pas vous les filles ? ',
                    'Non mais vraiment ? ',
                    'J\'aime beaucoup ! ',
                    'Jamais je ne pourrai croire un truc comme ça : ',
                    'Coup de coeur : ',
                    'Pourquoi ne pas y avoir pensé avant ? ',
                    'C\'est étonnant, mais c\'est pourtant vrai : '

                ])->random();

                /* Searching for an URL to tweet */
                foreach ($tweets as $tweet) {
                    if(isset($tweet['entities']['urls'][0]['expanded_url'])){
                        $url = $tweet['entities']['urls'][0]['expanded_url'];
                        break;
                    }
                }

                if(isset($url)){
                    Log::info('Tweeting something interesting : '.$intro . $url);
                    \Twitter::postTweet(['status' => $intro . $url, 'format' => 'array']);
                }

                break;

            /* Tweeting original content */
            case 2:
            case 3:
            case 4:

                $tweet = $tweets[(rand(0,10))]['text'];

                Log::info('Tweeting something interesting : '.$tweet);
                \Twitter::postTweet(['status' => $tweet, 'format' => 'array']);

                break;

            /* Retweeting  */
            case 5:
            case 6:
            case 7:
            case 8:

                $tweet = $tweets[(rand(0,10))]['id'];

                Log::info('Retweeting something interesting : '.$tweet);
                \Twitter::postRt($tweet);

                break;
        }
    }
}

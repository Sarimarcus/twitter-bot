<?php

namespace App\Console\Commands;

use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FollowUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:follow-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Follow followers from interesting accounts';

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

        /* Getting followers from account */
        $followers = \Twitter::getFollowers(['screen_name' => $username, 'format' => 'array']);

        /*
         * @todo : don't follow user without bio, without tweet or with less than X followers/following
         */
        if($target = $followers['users'][(rand(0,19))]['screen_name']){
            /* Tweeting the link */
            Log::info('Following user : '.$target);

            \Twitter::postFollow(['screen_name' => $target, 'format' => 'array']);
        }
    }
}

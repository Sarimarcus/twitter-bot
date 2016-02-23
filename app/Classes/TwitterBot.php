<?php

namespace App\Classes;

use Log;
use Illuminate\Support\Collection;
use App\Models\Users;

class TwitterBot
{
    public static $interestingUsers = [

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

    ];

    /*
     * Following followers from interesting accounts
     */
    public static function followUsers()
    {
        // Some interesting users to scan/
        $target = Collection::make(self::$interestingUsers)->random();

        // Getting followers from account
        $followers = \Twitter::getFollowers(['screen_name' => $target, 'count' => 10, 'format' => 'array']);

        foreach ($followers['users'] as $f) {

            $data = [
                'id'              => $f['id'],
                'screen_name'     => $f['screen_name'],
                'followers_count' => $f['followers_count'],
                'statuses_count'  => $f['statuses_count'],
                'lang'            => $f['lang']
            ];

            $flight = Users::updateOrCreate(['id' => $f['id']], $data);
        }

        // Getting and following best follower
        $winner = Users::getMostInteresting();
        Log::info('Following user : '.$winner->screen_name);
        \Twitter::postFollow(['screen_name' => $winner->screen_name, 'format' => 'array']);
        Users::flagFollowed($winner->id);
    }

    /*
     * Purge useless users
     */
    public static function purgeUsers()
    {
        Log::info('Purging users');
        Users::purgeUsers();
    }
}

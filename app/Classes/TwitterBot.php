<?php

namespace App\Classes;

use Log;
use Illuminate\Support\Collection;
use App\Models\Users;

class TwitterBot
{

    const WOEID = 615702; // Paris, FR

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

    /*
     * Retweet trending from local
     */
    public static function retweetTrending()
    {
        // Getting trends
        $trends = \Twitter::getTrendsPlace(['id' => self::WOEID, 'format' => 'array']);
        $topTrend = $trends[0]['trends'][(rand(0,4))]['name'];

        // Getting trending tweets
        $tweets = \Twitter::getSearch(['q' => $topTrend, 'result-type' => 'popular', 'lang' => 'fr', 'format' => 'array']);
        $topTweet = $tweets['statuses'][(rand(0,4))]['id'];

        Log::info('Retweeting trending tweet : '.$topTweet);

        // Retweeting one
        \Twitter::postRt($topTweet);
    }

    public static function tweetInterest()
    {
        // Some interesting users
        $target = Collection::make(self::$interestingUsers)->random();

        // Getting tweets from account
        $tweets = \Twitter::getUserTimeline(['screen_name' => $target, 'format' => 'array']);

        switch (rand(0,8)) {

            // Trying to fake a tweet
            case 0:

                // Some introduction to the tweet
                $intro = Collection::make([

                    'Vous aviez vu cet article ? C\'est un peu too much, non ? ',
                    'Ce genre de trucs me branche vraiment, pas vous les filles ? ',
                    'J\'aime beaucoup ! ',
                    'Jamais je ne pourrai croire un truc comme ça : ',
                    'Coup de coeur : ',
                    'Pourquoi ne pas y avoir pensé avant ? ',
                    'C\'est étonnant, mais c\'est pourtant vrai : '

                ])->random();

                //Searching for an URL to tweet
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

            // Tweeting original content
            case 1:
            case 2:
            case 3:
            case 4:

                $tweet = $tweets[(rand(0,10))]['text'];
                Log::info('Tweeting something interesting : '.$tweet);
                \Twitter::postTweet(['status' => $tweet, 'format' => 'array']);

                break;

            // Retweeting
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

    /*
     *
     */
    public static function tweetInspire()
    {
        $quote = Collection::make([

            'Ils ne savaient pas que c’était impossible alors ils l’ont fait. - Mark Twain',
            'Tous les hommes pensent que le bonheur se trouve au sommet de la montagne alors qu’il réside dans la façon de la gravir. - Confucius',
            'Un voyage de mille lieues commence toujours par un premier pas. - Lao Tseu',
            'Ce qui est plus triste qu’une œuvre inachevée, c’est une œuvre jamais commencée. - Christinna Rosseti',
            'Il y a des gens qui disent qu’ils peuvent, et d’autres qu’ils ne peuvent pas. En général ils ont tous raison. - Henry Ford',
            'Tous les jours et à tout point de vue, je vais de mieux en mieux. - Emile Coué',
            'Quand on ne peut revenir en arrière, on ne doit que se préoccuper de la meilleure manière d’aller de l’avant. - Paulo Coelho',
            'L’échec est seulement l’opportunité de recommencer d’une façon plus intelligente. - Henry Ford',
            'L’action n’apporte pas toujours le bonheur, sans doute, mais il n’y a pas de bonheur sans action. - Benjamin Disraeli',
            'Seuls ceux qui se risqueront à peut-être aller trop loin sauront jusqu’où il est possible d’aller. - Thomas Stearns Eliot',
            'Il faut viser la lune, parce qu’au moins si vous échouez, vous finissez dans les étoiles. - Oscar Wilde',
            'Les gagnants trouvent des moyens, les perdants des excuses. - Franklin Roosevelt',
            'Croyez en vous-même, en l’humanité, au succès de vos entreprises. Ne craignez rien ni personne. - Baronne Staffe',
            'Ils le peuvent, parce qu’ils pensent qu’ils le peuvent. - Virgile',
            'Si vous pouvez le rêver, vous pouvez le faire. - Walt Disney',
            'Les grandes réalisations sont toujours précédées par de grandes pensées. - Steve Jobs',
            'C’est à l’âge de dix ans que j’ai gagné Wimbledon pour la première fois… dans ma tête. - André Agassi',
            'Il n’est pas de vent favorable pour celui qui ne sait pas où il va. - Sénèque',
            'Il faut se concentrer sur ce qu’il nous reste et non sur ce qu’on a perdu. - Yann Arthus Bertrand',
            'Il est toujours trop tôt pour abandonner. - Norman Vincent Peale',
            'Il n’y a qu’une façon d’échouer, c’est d’abandonner avant d’avoir réussi. - Georges Clemenceau',
            'Le but n’est pas tout. Chaque pas vers le but est un but. Ce sont tous les petits buts qui font le but. - Confucius',
            'En suivant le chemin qui s’appelle plus tard, nous arrivons sur la place qui s’appelle jamais. - Sénèque',
            'Exposez-vous à vos peurs les plus profondes et après celà, la peur ne pourra plus vous atteindre. - Jim Morrisson',
            'Appréciez d’échouer, et apprenez de l’échec, car on n’apprend rien de ses succès. - James Dyson',
            'Le succès, c’est d’aller d’échec en échec sans perdre son enthousiasme. - Winston Churchill',
            'Si tu dors et que tu rêves que tu dors, il faut que tu te réveilles deux fois pour te lever. - Jean Claude Vandamme'

        ])->random();

        Log::info('Tweeting quote : '.$quote);
        \Twitter::postTweet(['status' => $quote, 'format' => 'array']);
    }
}

<?php

namespace App\Classes;

use Illuminate\Support\Collection;
use App\Models\Users;
use App\Models\Tweets;

class TwitterBot
{

    const WOEID = 615702; // Paris, FR
    const SUGG_SLUG = 'mode'; // Slug for suggestions
    const SEARCH_QUERY = '#mode OR #fashion'; // Query for search
    const NUMBER_UNFOLLOW = 20; // How many should we unfollow

    public static $interestingUsers = [

        'getthelook_fr', 'ellefrance', 'vogueparis',
        'CausetteLeMag', 'FashionMagFR', 'VanityFairFR',
        'marieclaire_fr', 'TheBeautyst', 'TTTmagazine',
        'stylistfrance', 'puretrend', 'lofficielparis',
        'grazia_fr', 'flowmagazine_fr', 'somanyparis',
        'My_Little_Paris', 'LEXPRESS_Styles', 'Terrafemina'

    ];

    /*
     * Following followers from interesting accounts
     */
    public static function followUsers()
    {
        // Some interesting users to scan/
        $target = Collection::make(self::$interestingUsers)->random();

        // Getting followers from account
        $followers = \Twitter::getFollowers(['screen_name' => $target, 'count' => 20, 'format' => 'array']);

        foreach ($followers['users'] as $f) {
            $data = [
                'id'              => $f['id'],
                'screen_name'     => $f['screen_name'],
                'followers_count' => $f['followers_count'],
                'statuses_count'  => $f['statuses_count'],
                'lang'            => $f['lang']
            ];

            $user = Users::updateOrCreate(['id' => $f['id']], $data);
        }

        // Getting and following best follower
        $winner = Users::getMostInteresting();

        if (\Twitter::postFollow(['screen_name' => $winner->screen_name, 'format' => 'array'])) {
            \Log::info('Following user : '.$winner->screen_name);
            Users::flagFollowed($winner->id);
        }
    }

    /*
     * Unfollow old followed users
     */
    public static function unFollowUsers()
    {
        // Getting the users to unfollow (if he's not following me, i'm a gentleman)
        $users = Users::getUsersToUnfollow(self::NUMBER_UNFOLLOW);

        if (count($users>0)) {

            // Preparing the lookup
            $lookup = (count($users>1)) ?  implode(',', collect($users)->pluck('screen_name')->toArray()) : $users[0]['screen_name'];
            $results = \Twitter::getFriendshipsLookup(['screen_name' => $lookup, 'format' => 'array']);

            // Checking their friendship
            foreach ($results as $u) {
                // He's following me, keep and flag him
                if (isset($u['connection'][1]['following_by'])) {
                    \Log::info($u['screen_name'].' is flagged as following');
                    Users::flagFollowing($u['id']);

                // Let's unfollow him ! Ingrate !
                } else {
                    if ($return = \Twitter::postUnfollow(['user_id' => $u['id'], 'format' => 'array'])) {
                        \Log::info('Unfollowing and deleting user : '.$u['screen_name']);
                        Users::deleteUser($u['id']);
                    }
                }
            }
        }
    }

    /*
     * Getting suggested users by slug
     * @todo Handle suggestions for EN (fashion.json?lang=en)
     */
    public static function getSuggested()
    {
        $suggestions = \Twitter::getSuggesteds(self::SUGG_SLUG, ['format' => 'array']);
        foreach ($suggestions['users'] as $f) {
            $data = [
                'id'              => $f['id'],
                'screen_name'     => $f['screen_name'],
                'followers_count' => $f['followers_count'],
                'statuses_count'  => $f['statuses_count'],
                'lang'            => $f['lang'],
                'suggested'       => 1
            ];

            $user = Users::updateOrCreate(['id' => $f['id']], $data);

            if ($user->wasRecentlyCreated) {
                if ($return = \Twitter::postFollow(['screen_name' =>  $f['screen_name'], 'format' => 'array'])) {
                    Users::flagFollowed($user->id);
                    \Log::info('Following suggested user : '.$user->screen_name);
                }
            }
        }
    }

    /*
     * Purge useless users
     */
    public static function purgeUsers()
    {
        \Log::info('Purging users');
        Users::purgeUsers();
    }

    /*
     * Retweet trending from local
     */
    public static function retweetTrending()
    {
        // Getting trends
        $trends = \Twitter::getTrendsPlace(['id' => self::WOEID, 'format' => 'array']);
        $topTrend = $trends[0]['trends'][(rand(0, 4))]['name'];

        // Getting trending tweets
        $tweets = \Twitter::getSearch(['q' => $topTrend, 'result-type' => 'popular', 'lang' => 'fr', 'format' => 'array']);
        $topTweet = $tweets['statuses'][(rand(0, 4))]['id'];

        \Log::info('Retweeting trending tweet : '.$topTweet);

        // Retweeting one
        \Twitter::postRt($topTweet);
    }

    /*
     * Getting tweets from our interesting users and publish one
     * in 3 ways : tweet with the URL, retweet, or tweet
     * original content
     */
    public static function tweetInterest()
    {
        $random = rand(0, 8);
        switch ($random) {

            // Retweet from the database
            case 0:
                $tweet = Tweets::getNext();
                \Log::info('Retweeting and liking from the DB : '.$tweet->id);

                try {
                    \Twitter::postRt($tweet->id);
                    \Twitter::postFavorite(['id' => $tweet->id]);
                    Tweets::flagRetweeted($tweet->id);
                } catch (\Exception $e) {
                    \Log::error($e);
                }

                break;

            // Tweeting original content
            case 1:
            case 2:
            case 3:
            case 4:

                $tweets = self::getRandomTweets();
                $tweet = $tweets[(rand(0, 10))]['text'];
                \Log::info('Tweeting something interesting : '.$tweet);

                try {
                    \Twitter::postTweet(['status' => html_entity_decode($tweet), 'format' => 'array']);
                } catch (\Exception $e) {
                    \Log::error($e);
                }

                break;

            // Retweeting
            case 5:
            case 6:
            case 7:
            case 8:

                $tweets = self::getRandomTweets();
                $tweetId = $tweets[(rand(0, 10))]['id'];
                \Log::info('Retweeting and liking something interesting : '.$tweetId);

                try {
                    \Twitter::postRt($tweetId);
                    \Twitter::postFavorite(['id' => $tweetId]);
                } catch (\Exception $e) {
                    \Log::error($e);
                }

                break;
        }
    }

    /*
     * Tweet an inspiring quote
     * @todo : use : http://quotes.rest/qod.json maybe
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

        \Log::info('Tweeting quote : '.$quote);
        \Twitter::postTweet(['status' => $quote, 'format' => 'array']);
    }

    /*
     * Save popular tweets by hashtags
     */
    public static function savePopularTweets()
    {
        $parameters = array(
            'q' => self::SEARCH_QUERY,
            'result_type' => 'popular',
            'format' => 'array'
            );

        $tweets = \Twitter::getSearch($parameters);

        \Log::info('Retrieving search from search query : '.self::SEARCH_QUERY);
        foreach ($tweets['statuses'] as $t) {
            $data = [
                'id'             => $t['id'],
                'user_id'        => $t['user']['id'],
                'text'           => $t['text'],
                'retweet_count'  => $t['retweet_count'],
                'favorite_count' => $t['favorite_count'],
                'lang'           => $t['lang']
            ];

            $tweet = Tweets::updateOrCreate(['id' => $t['id']], $data);
        }
    }

    /*
     *  Get some random tweets
     */
    private static function getRandomTweets()
    {
        // From hardcoded interesting users
        $interesting = Collection::make(self::$interestingUsers);

        // Some from the DB (the suggested ones), merging and picking one
        $rows = Users::getSuggested();
        $suggested = collect($rows)->pluck('screen_name');
        $target = $interesting->merge($suggested)->unique()->random();

        // Getting tweets from account
        $tweets = \Twitter::getUserTimeline(['screen_name' => $target, 'format' => 'array']);

        return $tweets;
    }
}

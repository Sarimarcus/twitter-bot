<?php

namespace App\Classes;

use Illuminate\Support\Collection;
use App\Models\Bot;
use App\Models\Stat;
use App\Models\Tweet;
use App\Models\User;

class TwitterBot
{
    const NUMBER_TO_UNFOLLOW = 20; // How many should we unfollow each time
    const NUMBER_LIMIT_FOR_UNFOLLOW = 500; // When begin to unfollow people ?

    /*
     * Run a task for every online bot
     */
    public static function runTask($task)
    {
        $bots = Bot::online()->orderBy('created_at')->get();
        foreach ($bots as $bot) {
            $bot::setConfiguration($bot);
            self::$task($bot);
        }
    }

    /*
     * Test function
     */
    public static function sendSomething(Bot $bot)
    {
        self::setOAuth($bot);
        \Twitter::postTweet(['status' => 'je teste quelque chose !', 'format' => 'array']);
    }

    /*
     * Following followers from interesting accounts
     */
    public static function followUsers(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        // Some interesting users to scan
        $target = Collection::make($bot->interestingUsers)->random();

        // Getting followers from account
        if ($followers = self::runRequest($bot, 'getFollowers', ['screen_name' => $target, 'count' => 30])) {
            foreach ($followers['users'] as $f) {
                $data = [
                    'id'              => $f['id'],
                    'bot_id'          => $bot->id,
                    'screen_name'     => $f['screen_name'],
                    'followers_count' => $f['followers_count'],
                    'statuses_count'  => $f['statuses_count'],
                    'lang'            => $f['lang']
                ];

                $user = User::updateOrCreate(['id' => $f['id']], $data);
            }
        }

        // Getting and following best follower
        if ($winner = User::getMostInteresting($bot)) {
            if (self::runRequest($bot, 'postFollow', ['screen_name' => $winner->screen_name])) {
                \Log::info('[' . $bot->screen_name . '] Following user : ' . $winner->screen_name);
                User::flagFollowed($winner->id);
            } else {
                \Log::error('[' . $bot->screen_name . '] Can\'t follow user ' . $winner->screen_name . ', deleting it');
                User::destroy($winner->id);
                self::followUsers($bot);
            }
        } else {
            \Log::info('[' . $bot->screen_name . '] Can\'t find new users to follow');
        }
    }

    /*
     * Unfollow old followed users
     * @todo : need to check this method
     */
    public static function unFollowUsers(Bot $bot)
    {
        // Not good to unfollow too early
        if ($bot->friends_count <= self::NUMBER_LIMIT_FOR_UNFOLLOW) {
            return;
        }

        // Setting OAuth parameters
        self::setOAuth($bot);

        // Getting the users to unfollow (if he's not following me, i'm a gentleman)
        $users = User::getUsersToUnfollow($bot, self::NUMBER_TO_UNFOLLOW);

        if (count($users>0)) {

            // Preparing the lookup
            $lookup = (count($users>1)) ?  implode(',', collect($users)->pluck('screen_name')->toArray()) : $users[0]['screen_name'];

            if ($results = self::runRequest($bot, 'getFriendshipsLookup', ['screen_name' => $lookup])) {
                // Checking their friendship
                foreach ($results as $u) {
                    // He's following me, keep and flag him
                    if (isset($u['connections'][1]['followed_by'])) {
                        \Log::info('[' . $bot->screen_name . '] ' . $u['screen_name'] . ' is flagged as following');
                        User::flagFollowing($u['id']);

                    // Let's unfollow him ! Ingrate !
                    } else {
                        if (self::runRequest($bot, 'postUnfollow', ['user_id' => $u['id']])) {
                            \Log::info('[' . $bot->screen_name . '] Unfollowing and deleting user : ' . $u['screen_name']);
                            User::deleteUser($u['id']);
                        }
                    }
                }
            }
        }
    }

    /*
     * Getting suggested users by slug
     */
    public static function getSuggested(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        if (isset($bot->slugSuggestions)) {
            foreach ($bot->slugSuggestions as $lang => $slug) {
                if ($lang != 'fr') {
                    $parameters = ['lang' => $lang, 'format' => 'array'];
                } else {
                    $parameters = ['format' => 'array'];
                }

                try {
                    \Log::info('[' . $bot->screen_name . '] Getting suggested users for : ' . $slug);
                    $suggestions = \Twitter::getSuggesteds($slug, $parameters);
                    Bot::isFine($bot);
                } catch (\Exception $e) {
                    \Log::error('[' . $bot->screen_name . '] Can\'t get suggestions : ' . $e->getMessage());
                    Bot::addError($bot);
                }

                if (isset($suggestions)) {
                    foreach ($suggestions['users'] as $f) {
                        $data = [
                        'id'              => $f['id'],
                        'bot_id'          => $bot->id,
                        'screen_name'     => $f['screen_name'],
                        'followers_count' => $f['followers_count'],
                        'statuses_count'  => $f['statuses_count'],
                        'lang'            => $f['lang'],
                        'suggested'       => 1
                    ];

                        $user = User::updateOrCreate(['id' => $f['id']], $data);

                        if ($user->wasRecentlyCreated) {
                            if ($return = self::runRequest($bot, 'postFollow', ['screen_name' => $f['screen_name']])) {
                                User::flagFollowed($user->id);
                                \Log::info('[' . $bot->screen_name . '] Following suggested user : ' . $user->screen_name);
                            }
                        }
                    }
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
        User::purgeUsers();
    }

    /*
     * Retweet trending from local
     */
    public static function retweetTrending(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        // Getting trends
        if ($trends = self::runRequest($bot, 'getTrendsPlace', ['id' => $bot->woeid])) {
            $topTrend = $trends[0]['trends'][(rand(0, count($trends[0]['trends'])))]['name'];

            // Getting trending tweets
            if ($tweets = self::runRequest($bot, 'getSearch', ['q' => $topTrend, 'result-type' => 'popular'])) {
                $topTweet = $tweets['statuses'][(rand(0, count($tweets['statuses'])))];

                // Retweeting one
                try {
                    \Log::info('[' . $bot->screen_name . '] Retweeting trending : ' . $topTweet['text']);
                    \Twitter::postRt($topTweet['id']);
                    Bot::isFine($bot);
                } catch (\Exception $e) {
                    \Log::error('[' . $bot->screen_name . '] Can\'t retweet trending : ' . $e->getMessage());
                    Bot::addError($bot);
                }
            }
        }
    }

    /*
     * Getting tweets from our interesting users and publish one
     * in 3 ways : tweet with the URL, retweet, or tweet
     * original content
     */
    public static function tweetInterest(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        $random = rand(0, 8);
        switch ($random) {

            // Retweet from the database
            case 0:

                if ($tweet = Tweet::getNext($bot)) {
                    \Log::info('[' . $bot->screen_name . '] Retweeting and liking from the DB : ' . html_entity_decode($tweet->text));

                    try {
                        \Twitter::postRt($tweet->id);
                        \Twitter::postFavorite(['id' => $tweet->id]);
                        Tweet::flagRetweeted($tweet->id);
                        Bot::isFine($bot);
                    } catch (\Exception $e) {
                        \Log::error('[' . $bot->screen_name . '] Retweeting and liking from the DB : ' . $e->getMessage());
                        Bot::addError($bot);
                    }
                }

                break;

            // Tweeting original content
            case 1:
            case 2:
            case 3:
            case 4:

                $tweets = self::getRandomTweets($bot);
                $tweet = $tweets[(rand(0, 10))];

                \Log::info('[' . $bot->screen_name . '] Tweeting something interesting : ' . html_entity_decode($tweet['text']));
                self::runRequest($bot, 'postTweet', ['status' => html_entity_decode($tweet['text'])]);

                break;

            // Retweeting
            case 5:
            case 6:
            case 7:
            case 8:

                $tweets = self::getRandomTweets($bot);
                $tweet = $tweets[(rand(0, 10))];

                \Log::info('[' . $bot->screen_name . '] Retweeting and liking something interesting : ' . html_entity_decode($tweet['text']));

                try {
                    \Twitter::postRt($tweet['id']);
                    \Twitter::postFavorite(['id' => $tweet['id']]);
                    Bot::isFine($bot);
                } catch (\Exception $e) {
                    \Log::error('[' . $bot->screen_name . '] Retweeting and liking something interesting : ' . $e->getMessage());
                    Bot::addError($bot);
                }

                break;
        }
    }

    /*
     * Tweet an inspiring quote
     * @todo : use : http://quotes.rest/qod.json maybe
     */
    public static function tweetInspire(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

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
            'C’est à l’âge de dix ans que j’ai gagné Wimbledon pour la première fois... dans ma tête. - André Agassi',
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

        \Log::info('[' . $bot->screen_name . '] Tweeting quote : ' . $quote);
        self::runRequest($bot, 'postTweet', ['status' => html_entity_decode($quote)]);
    }

    /*
     * Save popular tweets by hashtags
     */
    public static function savePopularTweets(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        \Log::info('[' . $bot->screen_name . '] Retrieving search from search query : ' . $bot->searchQuery);
        if ($tweets = self::runRequest($bot, 'getSearch', ['q' => $bot->searchQuery, 'result_type' => 'popular'])) {
            foreach ($tweets['statuses'] as $t) {
                $data = [
                    'id'             => $t['id'],
                    'bot_id'         => $bot->id,
                    'user_id'        => $t['user']['id'],
                    'text'           => $t['text'],
                    'retweet_count'  => $t['retweet_count'],
                    'favorite_count' => $t['favorite_count'],
                    'lang'           => $t['lang']
                ];

                $tweet = Tweet::updateOrCreate(['id' => $t['id']], $data);
            }
        }
    }

    /*
     * Get and update information about a bot
     */
    public static function updateBotInformation(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        if ($user = self::runRequest($bot, 'getUsers', ['screen_name' => $bot->screen_name])) {
            $bot = Bot::find($user['id']);
            foreach ($bot->getFillable() as $p) {
                $bot->$p = $user[$p];
            }

            $bot->save();

            // Also fill the stats
            $stat = new Stat;
            $stat->bot_id = $user['id'];
            $stat->date = date('Y-m-d');
            foreach ($stat->getFillable() as $p) {
                $stat->$p = $user[$p];
            }

            \Log::info('[' . $bot->screen_name . '] Getting daily stats');
            $stat->save();
        }
    }

    /*
     * Run a Twitter request
     */
    private static function runRequest(Bot $bot, $method, $params)
    {
        $defaultParams = [
            'format' => 'array'
        ];

        $params = array_merge($params, $defaultParams);

        try {
            if ($return = \Twitter::$method($params)) {
                Bot::isFine($bot);
                return $return;
            }
        } catch (\Exception $e) {
            \Log::error('[' . $bot->screen_name . '] Method ' . $method . ' : ' . $e->getMessage());
            Bot::addError($bot);
            return false;
        }
    }

    /*
     *  Get some random tweets
     */
    private static function getRandomTweets(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        // From hardcoded interesting users
        $interesting = Collection::make($bot->interestingUsers);

        // Some from the DB (the suggested ones), merging and picking one
        $rows = User::getSuggested($bot);
        $suggested = collect($rows)->pluck('screen_name');

        // Getting tweets from account
        $target = $interesting->merge($suggested)->unique()->random();
        if ($tweets = self::runRequest($bot, 'getUserTimeline', ['screen_name' => $target])) {
            return $tweets;
        }
    }

    /*
     * Set OAuth parameters for the bot
     */
    private static function setOAuth(Bot $bot)
    {
        $botConfig = [
            'consumer_key'    => $bot->twitter_consumer_key,
            'consumer_secret' => $bot->twitter_consumer_secret,
            'token'           => $bot->twitter_access_token,
            'secret'          => $bot->twitter_access_token_secret
        ];

        try {
            if (\Twitter::reconfig($botConfig)) {
                Bot::isFine($bot);
                return true;
            };
        } catch (\Exception $e) {
            \Log::error('[' . $bot->screen_name . '] Can\'t authentificate : ' . $e->getMessage());
            Bot::addError($bot);
        }
    }
}

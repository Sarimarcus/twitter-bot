<?php

namespace App\Classes;

use Illuminate\Support\Collection;
use App\Models\Bot;
use App\Models\Stat;
use App\Models\Tweet;
use App\Models\User;
use App\Models\Quote;

/**
* Tools for playing with Twitter API
*/
class TwitterBot
{
    const NUMBER_TO_UNFOLLOW = 10; // How many should we unfollow each time
    const MAX_FOLLOW_UNTIL_STOP = 4500; // Difference between friend and followers before mass unfollow
    const DIFFERENCE_BEFORE_UNFOLLOW = 400; // Difference between friend and followers before mass unfollow

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
        // If too many following, skip this
        if ($bot->friends_count >= self::MAX_FOLLOW_UNTIL_STOP) {
            return;
        }

        // Setting OAuth parameters
        self::setOAuth($bot);

        // Some interesting users to scan
        $target = Collection::make($bot->interestingUsers)->random();

        // Getting followers from account
        if ($followers = self::runRequest($bot, 'getFollowers', ['screen_name' => $target, 'count' => 50])) {
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
        // It looks Twitter need enough difference between friend and followers to mass unfollow
        if (($bot->friends_count - $bot->followers_count) <= self::DIFFERENCE_BEFORE_UNFOLLOW) {
            return;
        }

        // Setting OAuth parameters
        self::setOAuth($bot);

        // Getting the users to unfollow (if he's not following me, i'm a gentleman)
        $users = User::getUsersToUnfollow($bot, self::NUMBER_TO_UNFOLLOW);

        if (count($users>0)) {
            foreach ($users as $u) {
                // Let's unfollow him !
                if (self::runRequest($bot, 'postUnfollow', ['user_id' => $u->id])) {
                    \Log::info('[' . $bot->screen_name . '] Unfollowing and deleting user : ' . $u->screen_name);
                }

                User::destroy($u->id);
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
     */
    public static function tweetInspire(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);

        $quote = Quote::last();
        $tweet = $quote->text . ' — ' . $quote->author;
        \Log::info('[' . $bot->screen_name . '] Tweeting quote : ' . $tweet);
        self::runRequest($bot, 'postTweet', ['status' => html_entity_decode($tweet)]);
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
     * Check API requests remaining
     */
    public static function checkBotApiLimits(Bot $bot)
    {
        // Setting OAuth parameters
        self::setOAuth($bot);
        echo $bot->screen_name;
        $apiLimits = self::runRequest($bot, 'getAppRateLimit', ['resources' => 'help,users,search,statuses']);
        echo '<xmp>';
        print_r($apiLimits);
        echo '</xmp>';
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

        // Let's try some random
        if (\App::environment('live')) {
            sleep(rand(0, 40));
        }

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

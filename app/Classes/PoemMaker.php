<?php

namespace App\Classes;

use App\Models\Alexandrine;

/**
*  Some methods to generate poems (with tweets ?)
*/
class PoemMaker
{
    const THANK_MSG = 'Merci pour ton tweet, c\'est un bel alexandrin, je vais m\'en servir pour mon poème. Plus qu\' à trouver des rimes !';

    // Poem language
    private $language;

    public function __construct($language)
    {
        // Set language
        $this->language = $language;

        // Configure the bot
        $botConfig = $this->getBotConfig();
        \Twitter::reconfig($botConfig);

        // Set cache folder
        \Syllable::setCacheDir(storage_path().'/framework/cache');
    }

    /*
     * Search tweets from a place to find inspiration
     */
    public function getInspiration()
    {
        $params = [
            'q'           => 'place:09f6a7707f18e0b1', // Hardcoding Paris, FR for now
            'lang'        => $this->language,
            'result_type' => 'recent',
            'count'       => 100,
            'format'      => 'array'
        ];

        try {
            \Log::info('// Poem Maker : getting inspiration');
            $inspiration = \Twitter::getSearch($params);

            // Looking for an alexandrine !
            $found = [];
            foreach ($inspiration['statuses'] as $key => $tweet) {
                // Not taking tweets with mentions or links
                if (false === strpos($tweet['text'], '@') && false === strpos($tweet['text'], 'http')) {
                    if ($this->isAlexandrine($tweet['text'])) {
                        $data = [
                            'tweet_id' => $tweet['id'],
                            'user_id'  => $tweet['user']['id'],
                            'text'     => $tweet['text'],
                            'lang'     => $tweet['lang']
                        ];

                        // Store in DB
                        $alexandrine = Alexandrine::updateOrCreate(['tweet_id' => $tweet['id']], $data);

                        // Let's thank the author of this !
                        $this->thankSource($tweet);

                        $found[] = $data;
                    }
                }
            }

            \Log::info('// Found ' . count($found) . ' alexandrine(s)');

            return $found;
        } catch (\Exception $e) {
            \Log::error('// Can\'t get inspiration : ' . $e->getMessage());
        }
    }

    /*
     * Check if a string is an alexandrine
     * @param string $string
     * @php return boolean
     */
    private function isAlexandrine($text)
    {
        $syllable = new \Syllable($this->language);

        $histogram = $syllable->histogramText($text);
        $syllabesCount = $this->sumSyllabes($histogram);
        return (12 == $syllabesCount) ? true : false;
    }

    /*
     * Calculate the total number of syllabes from a text histogram
     * @param array $histogram array from \Syllable->histogramText
     * @return int
     */
    private function sumSyllabes($histogram)
    {
        $sum = 0;
        foreach ($histogram as $syllable_count => $number) {
            $sum += $syllable_count * $number;
        }

        return $sum;
    }

    /*
     * Send a tweet to the writer of the alexandrin and like the tweet
     * $param array the original tweet
     * @return boolean
     */
    public function thankSource($tweet)
    {
        try {
            // Like the tweet
            \Twitter::postFavorite(['id' => $tweet['id_str']]);

            // Send the message
            $params = [
                'status'                => html_entity_decode('@' . $tweet['user']['screen_name'] . ' ' . self::THANK_MSG),
                'in_reply_to_status_id' => $tweet['id_str'],
                'format'                => 'array'
            ];
            \Twitter::postTweet($params);
        } catch (\Exception $e) {
            \Log::error('// Can\'t thank the source : ' . $e->getMessage());
        }
    }

    /*
     * Still working on this :/
     */
    public function getLastSyllabe($text)
    {
        // Getting last word
        $words = mb_split('[^\'[:alpha:]]+', $text);
        $words = array_reverse($words);
        foreach ($words as $w) {
            if (mb_strlen($w)) {
                $lastWord = $w;
                break;
            }
        }
      /*  $syllable = new \Syllable('fr');
        return $syllable->parseWord($lastWord);*/
    }

    /*
     * Twitter app configuration
     */
    private function getBotConfig()
    {
        $botConfig = [
            'consumer_key'    => env('TWITTER_CONSUMER_KEY', ''),
            'consumer_secret' => env('TWITTER_CONSUMER_SECRET', ''),
            'token'           => env('TWITTER_ACCESS_TOKEN', ''),
            'secret'          => env('TWITTER_ACCESS_TOKEN_SECRET', '')
        ];

        return $botConfig;
    }
}

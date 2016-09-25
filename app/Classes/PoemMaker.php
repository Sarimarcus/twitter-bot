<?php

namespace App\Classes;

use App\Models\Alexandrine;

/**
*  Some methods to generate poems (with tweets ?)
*/
class PoemMaker
{

    // Poem language
    private $language;

    public function __construct($language)
    {
        // Set language
        $this->language = $language;

        // Set cache folder
        \Syllable::setCacheDir(storage_path().'/framework/cache');
    }

    /*
     * Search tweets from a place to find inspiration
     */
    public function getInspiration()
    {
        $botConfig = [
            'consumer_key'    => 'VyGD7wzlcYP8N5JcLyhA',
            'consumer_secret' => 'QAWyxUrd2oeGsMHN6FpSXk6QH0Wpr3WPdrAqCEC8pnE',
            'token'           => '12261582-0Nj2Kjv5NxARWwa44tQQUHP3Ys00GP3Ua1qt3LizQ',
            'secret'          => 'idQnDEMtw2xwlWsf8wRSA8LJVWJ21s8X3lhxshvC88'
        ];

        $params = [
            'q'           => 'place:09f6a7707f18e0b1', // Hardcoding Paris, FR for now
            'lang'        => $this->language,
            'result_type' => 'recent',
            'count'       => 100,
            'format'      => 'array'
        ];

        try {
            if (\Twitter::reconfig($botConfig)) {
                \Log::info('// Poem Maker : getting inspiration');
                $inspiration = \Twitter::getSearch($params);

                // Looking for an alexandrine !
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

                            try {
                                $alexandrine = Alexandrine::updateOrCreate(['tweet_id' => $tweet['id']], $data);
                                \Log::info('Found alexandrine : '  . $tweet['text']);
                            } catch (Exception $e) {
                                \Log::error('Can\'t dave to DB : ' . $e->getMessage());
                            }
                        }
                    }
                }
            };
        } catch (\Exception $e) {
            \Log::error('Can\'t authentificate : ' . $e->getMessage());
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
}

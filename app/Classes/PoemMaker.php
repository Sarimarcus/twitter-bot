<?php

namespace App\Classes;

use App\Models\Alexandrine;
use App\Models\Poem;
use Carbon\Carbon;

/**
*  Some methods to generate poems (with tweets ?)
*/
class PoemMaker
{
    // Tweet for thanks
    const THANK_MSG = 'Voici mon dernier poème : [POST]';

    // Number of verse of the poem
    const NUMBER_VERSE = 3;

    // Number of alexandrine in a verse
    const NUMBER_ALEXANDRINE = 2;

    // Poem language
    private $language;

    public function __construct($language)
    {
        // Set language
        $this->language = $language;
        setlocale(LC_TIME, $this->language);
        Carbon::setLocale($this->language);

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

                // Not taking tweets with mentions, links, numbers inside or hastags
                if (false === strpos($tweet['text'], '@') &&
                    false === strpos($tweet['text'], 'http') &&
                    false === strpos($tweet['text'], '#') &&
                    false == preg_match('~[0-9]+~', $tweet['text'])) {
                    if ($this->isAlexandrine($tweet['text'])) {

                        // Getting the last word (to find better rhymes)
                        $lastWord = $this->getLastWord($tweet['text']);

                        // Getting last phoneme for rhyme matching (i remember you we are here to build a poem)
                        $lastPhoneme = $this->getLastPhoneme($tweet['text']);

                        // If we can't find the phoneme, skip it
                        if (empty($lastPhoneme)) {
                            continue;
                        }

                        $data = [
                            'tweet_id'          => $tweet['id'],
                            'user_id'           => $tweet['user']['id'],
                            'text'              => $tweet['text'],
                            'lang'              => $tweet['lang'],
                            'screen_name'       => $tweet['user']['screen_name'],
                            'profile_image_url' => $tweet['user']['profile_image_url'],
                            'phoneme'           => $lastPhoneme,
                            'last_wurd'         => $lastWord
                        ];

                        // Store in DB
                        $alexandrine = Alexandrine::updateOrCreate(['tweet_id' => $tweet['id']], $data);

                        // Let's thank the author of this ! Or not, i'm spamming
                        //$this->thankSource($tweet);

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
     * Retrieve the last phoneme of the alexandrine (for rhymes matching)
     * @param text text to analyse
     * @return text the phoneme
     */
    public function getLastPhoneme($text)
    {
        $lastPhoneme = '';

        // Getting last word
        $lastWord = $this->getLastWord($text);

        // Getting last syllable
        $syllable = new \Syllable('fr');
        $syllables = $syllable->splitWord($lastWord);
        $lastSyllable = end($syllables);

        // Getting the phonem
        $lastPhoneme = SoundexFr::phonetique($lastSyllable);

        // As SoundexFr isn't working that fine, remove accent and get the 2 last letters (let's try this)
        $lastPhoneme = substr($this->removeAccents($lastPhoneme), -2);
        return $lastPhoneme;
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
     * Generate the poem
     */
    public function generatePoem()
    {
        $lines = [];

        // Get some random rhymes
        $rhymes = array_rand(array_flip($this->getRhymes()), self::NUMBER_VERSE);
        foreach ($rhymes as $k => $rhyme) {

            // Getting the alexandrines for the rhyme
            $output = $this->assembleAlexandrines($rhyme)->all();
            foreach ($output as $key => $value) {
                $lines[] = $value->id;
            }
        }

        // Insert in DB
        $poemId = $this->insertPoem($lines);

        // Send Poem to Tumblr
        $this->sendTumblr($poemId);
    }

    /*
     * Get the last word of a string
     * @param text text to analyse
     * @return text the last word
     */
    public function getLastWord($text)
    {
        $words = mb_split('[^\'[:alpha:]]+', $this->removeEmoji($text));
        $words = array_reverse($words);
        foreach ($words as $w) {
            // Don't get the word if it's empty (sometimes it happens) or an emoji
            if (mb_strlen($w)) {
                $lastWord = $w;
                break;
            }
        }

        return $lastWord;
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
     * Twitter app configuration
     */
    private function getBotConfig()
    {
        $botConfig = [
            'consumer_key'    => config('app.twitter_consumer_key'),
            'consumer_secret' => config('app.twitter_consumer_secret'),
            'token'           => config('app.twitter_access_token'),
            'secret'          => config('app.twitter_access_token_secret')
        ];

        return $botConfig;
    }

    /*
     * Send poem content to Tumblr
     */
    public function sendTumblr($poemId)
    {
        $poem = Poem::find($poemId);
        $alexandrines = $poem->alexandrines()->orderBy('rank')->get();

        $data = [
            'alexandrines' => $alexandrines
        ];

        $html = view('poem-bundler.tumblr-post', $data)->render();

        $postData = [
            'type'  => 'text',
            'title' => 'Poème #' . $poemId,
            'body'  => $html,
            'tags'  => 'poème,poem,generated',
            'tweet' => 'Voici le dernier poème que j\'ai redigé pour vous : '
        ];

        $client = app('app.tumblr.api');

        try {
            $call = $client->createPost(config('services.tumblr.blog'), $postData);
            $postUrl = 'https://' . config('services.tumblr.blog') . '/post/' . $call->id;
            \Log::info('// Posted to Tumblr ! ID : ' . $postUrl);

            // Sending it to Twitter
            $this->sendTwitter($postUrl, $alexandrines);
        } catch (\Exception $e) {
            \Log::error('// Can\'t post to Tumblr : ' . $e->getMessage());
        }
    }

    /*
     * Send link to the poem on Twitter
     */
    public function sendTwitter($postUrl, $alexandrines)
    {
        $statusPromote = str_replace('[POST]', $postUrl, self::THANK_MSG);
        $statusThanks = '';

        // Adding the usernames
        foreach ($alexandrines as $key => $value) {
            $statusThanks .= '@' . $value['screen_name'] . ', ';
        }

        $statusThanks .= 'merci pour l\'inspiration !';

        try {

            // Send the message
            $params = [
                'status'                => html_entity_decode($statusPromote),
                'format'                => 'array'
            ];

            $call = \Twitter::postTweet($params);

            // Send the second message
            $params = [
                'status'                => html_entity_decode($statusThanks),
                'in_reply_to_status_id' => $call['id'],
                'format'                => 'array'
            ];
            \Twitter::postTweet($params);
        } catch (\Exception $e) {
            \Log::error('// Can\'t thank the source : ' . $e->getMessage());
        }
    }

    /*
     * Return available rhymes
     */
    private function getRhymes()
    {
        $o = new Alexandrine();
        $count = $o->getSimilarPhonemes();

        // Let's take only available rhymes
        $rhymes = [];
        foreach ($count as $c) {
            if ($c->total > 2) {
                $rhymes[] = $c->phoneme;
            }
        }

        return $rhymes;
    }

    /*
     * Return alexandrines by phoneme
     */
    private function assembleAlexandrines($phoneme)
    {
        $rhymes = [];
        $o = new Alexandrine();
        $alexandrines = $o->getAlexandrinesByPhoneme($phoneme);
        $random = $alexandrines->random(self::NUMBER_ALEXANDRINE);

        // Need to have different words for better poem
        $unique = $random->unique('last_word');
        if (count($unique) == self::NUMBER_ALEXANDRINE) {
            return $random;
        } else {
            return $this->assembleAlexandrines($phoneme);
        }
    }

    /*
     * Insert the poem
     */
    private function insertPoem($alexandrines)
    {

        // Inserting quote in DB
        $q = new Poem;
        // $dt = Carbon::now();
        // $q->title = $dt->formatLocalized('%A') . ' poem';
        $q->title = 'Poème du jour';
        $q->save();

        $poemId = $q->id;
        $rank = 1;
        foreach ($alexandrines as $id) {
            $a = Alexandrine::find($id);
            $a->poem_id = $poemId;
            $a->rank = $rank;
            $a->save();
            $rank++;
        }

        return $poemId;
    }


    /*
     * Remove emoji
     */
    private function removeEmoji($text)
    {
        return preg_replace('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
    }

    /**
     * Unaccent the input string string. An example string like `ÀØėÿᾜὨζὅБю`
     * will be translated to `AOeyIOzoBY`. More complete than :
     *   strtr( (string)$str,
     *          "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
     *          "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn" );
     *
     * @param $str input string
     * @param $utf8 if null, function will detect input string encoding
     * @return string input string without accent
     * @source https://gist.github.com/evaisse/169594
     */
    private function removeAccents($str, $utf8=true)
    {
        $str = (string)$str;
        if (is_null($utf8)) {
            if (!function_exists('mb_detect_encoding')) {
                $utf8 = (strtolower(mb_detect_encoding($str))=='utf-8');
            } else {
                $length = strlen($str);
                $utf8 = true;
                for ($i=0; $i < $length; $i++) {
                    $c = ord($str[$i]);
                    if ($c < 0x80) {
                        $n = 0;
                    } # 0bbbbbbb
                    elseif (($c & 0xE0) == 0xC0) {
                        $n=1;
                    } # 110bbbbb
                    elseif (($c & 0xF0) == 0xE0) {
                        $n=2;
                    } # 1110bbbb
                    elseif (($c & 0xF8) == 0xF0) {
                        $n=3;
                    } # 11110bbb
                    elseif (($c & 0xFC) == 0xF8) {
                        $n=4;
                    } # 111110bb
                    elseif (($c & 0xFE) == 0xFC) {
                        $n=5;
                    } # 1111110b
                    else {
                        return false;
                    } # Does not match any model
                    for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                        if ((++$i == $length)
                            || ((ord($str[$i]) & 0xC0) != 0x80)) {
                            $utf8 = false;
                            break;
                        }
                    }
                }
            }
        }

        if (!$utf8) {
            $str = utf8_encode($str);
        }

        $transliteration = array(
        'Ĳ' => 'I', 'Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
        'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
        'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
        'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
        'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
        'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
        'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
        'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
        'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
        'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
        'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
        'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
        'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
        'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
        'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
        'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
        'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
        'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
        'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
        'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
        'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
        'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
        'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
        'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
        'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
        'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
        'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
        'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
        'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
        'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
        'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
        'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
        'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
        'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
        'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
        'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
        'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
        'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
        'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
        'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
        'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
        'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
        'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
        'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
        'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
        'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
        'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
        'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
        'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
        'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
        'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
        'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
        'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
        'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
        'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
        'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
        'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
        'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
        'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
        'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
        'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
        'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
        'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
        'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
        'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
        'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
        'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
        'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
        'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
        'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
        'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
        'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
        'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
        'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
        'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
        'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
        'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
        'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
        'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
        'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
        'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
        'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
        'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
        'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
        'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
        'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
        'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
        'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
        'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
        'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
        'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
        'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
        'ჯ' => 'j','ჰ' => 'h'
        );
        $str = str_replace(array_keys($transliteration),
                            array_values($transliteration),
                            $str);
        return $str;
    }
}

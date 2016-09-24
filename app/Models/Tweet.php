<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    protected $fillable  = ['id', 'bot_id', 'user_id', 'text', 'retweet_count', 'favorite_count', 'lang', 'created_at', 'updated_at'];

    public $incrementing = false;
    public $primaryKey   = 'id';

    /*
     * Flag a tweet as retweeted
     */
    public static function flagRetweeted($id)
    {
        if ($id != null) {
            $tweet = Tweet::find($id);
            $tweet->retweeted = 1;
            return $tweet->save();
        }
    }

    /*
     * Get the newt tweet to retweet
     */
    public static function getNext(Bot $bot)
    {
        $tweet = \DB::table('tweets')
                    ->where('bot_id', $bot->id)
                    ->whereIn('lang', (array)$bot->lang)
                    ->where('retweeted', 0)
                    ->first();

        return $tweet;
    }

    /*
     * Get tweets by language
     */
    public static function getTweetsByLanguage($language)
    {
        return \DB::table('tweets')
                    ->where('lang', $language)
                    ->get();
    }
}

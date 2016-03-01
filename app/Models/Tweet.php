<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    protected $fillable  = ['id', 'user_id', 'text', 'retweet_count', 'favorite_count', 'lang', 'created_at', 'updated_at'];

    public $incrementing = false;
    public $primaryKey   = 'id';

    /*
     * Flag a tweet as retweeted
     */
    public static function flagRetweeted($id)
    {
        if ($id != null) {
            $tweet = Tweets::find($id);
            $tweet->retweeted = 1;
            return $tweet->save();
        }
    }

    /*
     * Get the newt tweet to retweet
     */
    public static function getNext()
    {
        $user = \DB::table('tweets')
                    ->where('retweeted', 0)
                    ->first();

        return $user;
    }
}

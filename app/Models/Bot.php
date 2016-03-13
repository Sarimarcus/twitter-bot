<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $fillable  = ['statuses_count', 'favourites_count', 'followers_count', 'friends_count'];

    public $woeid;
    public $searchQuery;
    public $slugSuggestions;
    public $interestingUsers;
    public $lang;

    public $incrementing = false;
    public $primaryKey   = 'id';

    const ERRORS_FOR_OFFLINE = 5;

    /*
     * Returns the online bots
     */
    public static function scopeOnline($query)
    {
        return $query->where('online', 1);
    }

    /*
     * Set the bot configuration
     */
    public static function setConfiguration(Bot $bot)
    {
        $configuration = json_decode($bot->configuration, true);
        foreach ($configuration as $key => $value) {
            $bot->$key = $value;
        }
    }

    /*
     * Increase the number of API errors, and set offline if necessary
     */
    public static function addError(Bot $bot)
    {
        $bot = Bot::find($bot->id);
        $bot->increment('errors_count');
        if (self::ERRORS_FOR_OFFLINE == $bot->errors_count) {
            // Set the bot offline
            $bot->online = 0;
            \Log::error('[' . $bot->screen_name . '] is now offline :/');
            return $bot->save();
        }
    }

    /*
     * Reset the error counter
     */
    public static function isFine(Bot $bot)
    {
        $bot = Bot::find($bot->id);
        $bot->errors_count = 0;
        return $bot->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable  = ['id', 'bot_id', 'screen_name', 'followers_count', 'statuses_count', 'lang', 'suggested', 'created_at', 'updated_at'];

    public $incrementing = false;
    public $primaryKey   = 'id';

    /*
     * Returning a french user with some followers and tweets
     */
    public static function getMostInteresting(Bot $bot)
    {
        $user = \DB::table('users')
                    ->where('bot_id', $bot->id)
                    ->where('statuses_count', '>=', '100')
                    ->where('followers_count', '>=', '50')
                    ->where('followed', 0)
                    ->orderBy('followers_count', 'desc')
                    ->orderBy('statuses_count', 'desc')
                    ->first();

        return $user;
    }

    /*
     * Returns the interesting users
     */
    public static function getSuggested(Bot $bot)
    {
        return  \DB::table('users')
                    ->select('screen_name')
                    ->where('bot_id', $bot->id)
                    ->where('suggested', 1)
                    ->get();
    }

    /*
     * Return a user to unfollow (rule : oldest followed)
     */
    public static function getUsersToUnfollow(Bot $bot, $limit = 1)
    {
        $user = \DB::table('users')
                    ->where('bot_id', $bot->id)
                    ->where('followed', 1)
                    ->where('following', 0)
                    ->where('suggested', 0)
                    ->orderBy('created_at')
                    ->take($limit)->get();

        return $user;
    }

    /*
     * Flag a user as followed
     */
    public static function flagFollowed($id)
    {
        if ($id != null) {
            $user = User::find($id);
            $user->followed = 1;
            return $user->save();
        }
    }

    /*
     * Flag a user as following
     */
    public static function flagFollowing($id)
    {
        if ($id != null) {
            $user = User::find($id);
            $user->following = 1;
            return $user->save();
        }
    }

    /*
     * Delete a user
     */
    public static function deleteUser($id)
    {
        if ($id != null) {
            return \DB::table('users')->where('id', $id)->delete();
        }
    }

    /*
     * Removing useless ursers
     */
    public static function purgeUsers()
    {
        \DB::table('users')->where('statuses_count', '<=', 50)->delete();
        \DB::table('users')->where('followers_count', '<=', 50)->delete();
    }
}

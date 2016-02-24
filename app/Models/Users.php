<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table     = 'users';
    protected $fillable  = ['id', 'screen_name', 'followers_count', 'statuses_count', 'lang', 'interesting', 'created_at', 'updated_at'];

    public $incrementing = false;
    public $primaryKey   = 'id';

    /*
     * Returning a french user with some followers and tweets
     */
    public static function getMostInteresting()
    {
        $user = \DB::table('users')
                    ->where('lang', 'fr')
                    ->where('statuses_count', '>=' , '100')
                    ->where('followers_count', '>=' , '50')
                    ->where('followed', 0)
                    ->orderBy('statuses_count', 'desc')
                    ->orderBy('followers_count', 'desc')
                    ->first();

        return $user;
    }

    /*
     * Returns the interesting users
     */
    public static function getSuggested()
    {
        return  \DB::table('users')
                    ->select('screen_name')
                    ->where('suggested', 1)
                    ->get();
    }

    /*
     * Flag a user as followed
     */
    public static function flagFollowed($id)
    {
        if($id != null){

            $user = Users::find($id);
            $user->followed = 1;
            return $user->save();

        }
    }

    /*
     * Flag a user as following
     */
    public static function flagFollowing($id)
    {
        if($id != null){

            $user = Users::find($id);
            $user->following = 1;
            return $user->save();

        }
    }

    /*
     * Return a user to unfollow (rule : oldest followed)
     */
    public static function getUsersToUnfollow($limit = 1)
    {
        $user = \DB::table('users')
                    ->where('followed', 1)
                    ->where('following', 0)
                    ->where('suggested', 0)
                    ->orderBy('created_at')
                    ->take($limit)->get();

        return $user;
    }

    /*
     * Delete a user
     */
    public static function deleteUser($id)
    {
        if($id != null){

            return \DB::table('users')->where('id', $id)->delete();

        }
    }

    /*
     * Removing useless ursers
     */
    public static function purgeUsers()
    {
        \DB::table('users')->where('statuses_count', 0)->delete();
        \DB::table('users')->where('followers_count', '<=', 50)->delete();
    }
}

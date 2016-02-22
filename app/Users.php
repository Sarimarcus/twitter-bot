<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table     = 'users';
    protected $fillable  = ['id', 'screen_name', 'followers_count', 'statuses_count', 'lang', 'created_at', 'updated_at'];

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
     * Flag a user as followed
     */
    public static function flagFollowed($id)
    {
        $user = Users::find($id);
        $user->followed = 1;
        return $user->save();
    }
}

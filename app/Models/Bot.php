<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $fillable  = ['statuses_count', 'favourites_count', 'followers_count', 'friends_count'];

    public $incrementing = false;
    public $primaryKey   = 'id';

    /*
     * Returns the online bots
     */
    public static function scopeOnline($query)
    {
        return $query->where('online', 1);
    }
}

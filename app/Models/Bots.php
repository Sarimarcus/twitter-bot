<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bots extends Model
{
    protected $table     = 'bots';
    protected $fillable  = ['statuses_count', 'favourites_count', 'followers_count', 'friends_count'];

    public $incrementing = false;
    public $primaryKey   = 'id';
}
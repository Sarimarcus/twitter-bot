<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable  = ['statuses_count', 'favourites_count', 'followers_count', 'friends_count'];
}

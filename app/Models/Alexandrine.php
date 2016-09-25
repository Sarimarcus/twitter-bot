<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alexandrine extends Model
{
    protected $fillable  = ['tweet_id', 'user_id', 'text', 'lang', 'created_at', 'updated_at'];

    public $incrementing = false;
    public $primaryKey   = 'tweet_id';
}

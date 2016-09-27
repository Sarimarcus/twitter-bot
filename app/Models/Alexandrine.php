<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alexandrine extends Model
{
    protected $fillable  = ['tweet_id', 'user_id', 'text', 'lang', 'phoneme', 'created_at', 'updated_at'];

    /**
     * Get the poem that owns the alexandrine.
     */
    public function poem()
    {
        return $this->belongsTo('App\Models\Poem');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Alexandrine extends Model
{
    protected $fillable  = ['tweet_id', 'user_id', 'text', 'lang', 'screen_name', 'profile_image_url', 'phoneme', 'last_word', 'created_at', 'updated_at'];

    /**
     * Get the poem that owns the alexandrine.
     */
    public function poem()
    {
        return $this->belongsTo('App\Models\Poem');
    }

    /*
     * Get the alexandrines free to use
     */
    public static function scopeFree($query)
    {
        return $query->whereNull('poem_id')->where('phoneme', '<>', '️');
    }

    /*
     * Get the count of similar phonemes
     */
    public function getSimilarPhonemes()
    {
        return $this->select('phoneme', DB::raw('count(id) as total'))
                    ->groupBy('phoneme')->whereNull('poem_id')
                    ->where('phoneme', '<>', '️')->get();
    }

    /*
     * Get alexandrines by rhymes
     */
    public function getAlexandrinesByPhoneme($phoneme)
    {
        return $this->select('id', 'last_word')
                    ->where('phoneme', $phoneme)
                    ->whereNull('poem_id')
                    ->get();
    }
}

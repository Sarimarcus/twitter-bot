<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poem extends Model
{
    protected $fillable  = ['title'];

    /*
     * Get the alexandrines of the poem
     */
    public function alexandrines()
    {
        return $this->hasMany('App\Models\Alexandrine');
    }
}

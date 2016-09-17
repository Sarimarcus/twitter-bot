<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable  = ['text', 'author', 'illustration', 'length'];

    public static function last()
    {
        $quote = \DB::table('quotes')
                    ->orderBy('created_at', 'desc')
                    ->first();

        return $quote;
    }
}

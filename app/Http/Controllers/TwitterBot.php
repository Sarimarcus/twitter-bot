<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


class TwitterBot extends Controller
{
    /*
     * Returning Trends for Paris
     */
    public function retweetTrending()
    {

        /* Getting trends */
        $trends = \Twitter::getTrendsPlace(['id' => 615702, 'format' => 'array']);
        $topTrend = $trends[0]['trends'][(rand(0,4))]['name'];

        /* Getting trending tweets */
        $tweets = \Twitter::getSearch(['q' => $topTrend, 'format' => 'array']);
        $topTweet = $tweets['statuses'][(rand(0,4))]['id'];

        /* Retweeting */
        \Twitter::postRt($topTweet);
    }
}

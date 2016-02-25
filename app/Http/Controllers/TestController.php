<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function save()
    {
        \App\Classes\TwitterBot::getPopularTweets();
    }

    public function get()
    {
        \App\Classes\TwitterBot::getTweets();
    }
}

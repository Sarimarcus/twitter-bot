<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class Test extends Controller
{
    public function ApiLimits()
    {
        \App\Classes\TwitterBot::runTask('checkBotApiLimits');
    }
}

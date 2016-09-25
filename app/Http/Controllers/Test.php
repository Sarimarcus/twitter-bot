<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class Test extends Controller
{
    public function hello()
    {
        echo 'Hello World !';
    }

    public function apiLimits()
    {
        \App\Classes\TwitterBot::runTask('checkBotApiLimits');
    }

    public function poem()
    {
        $o = new \App\Classes\PoemMaker('fr');
        $data =  $o->getInspiration();
        dd($data);
   }

    public function isAlexandrine()
    {
        $text = 'Je partirai. Vois-tu, je sais que tu m\'attends.';
        $o = new \App\Classes\PoemMaker('fr');
        $data =  $o->isAlexandrine($text);
        dd($data);
    }
}

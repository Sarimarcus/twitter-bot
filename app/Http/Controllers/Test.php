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

    public function last()
    {
        $text = 'Je partirai. Vois-tu, je sais que tu m\'attends.';
       // $text = 'Can you please specify what you actually want? Just.';
        $o = new \App\Classes\PoemMaker('fr');
        $data =  $o->getLastSyllabe($text);
        dd($data);
    }

    public function alexandrine()
    {
        \Syllable::setCacheDir(storage_path().'/framework/cache');
        $text = 'Je partirai. Vois-tu, je sais que tu m\'attends.';
        echo $text;
        $syllable = new \Syllable('fr');

        $histogram = $syllable->histogramText($text);
        dd($histogram);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\ServiceProvider;
use App\Models\Poem;

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
        /*$poem = Poem::orderBy('created_at', 'desc')->first();
        $alexandrines = $poem->alexandrines()->orderBy('rank')->get();

        $data = [
            'headTitle' => 'Poem Bundler',
            'poem' => $poem,
            'alexandrines' => $alexandrines
        ];

        $html = view('poem-bundler.poem-content', $data)->render();
        dd($html);*/

        $o = new \App\Classes\PoemMaker('fr');
        $o->generatePoem();
    }

    public function tumblr(\Tumblr\API\Client $client)
    {
        //$client = \App::make(Tumblr::class);
      //  $client = $this->app->make('Tumblr\API\Client');
      //  $client = app('app.tumblr.api');
        dd($client);
    }

    public function twitter()
    {
        $o = new \App\Classes\PoemMaker('fr');
        $o->sendTumblr(254);
    }

    public function last()
    {
        $text = 'Je partirai. Vois-tu, je sais que tu m\'attends.';
       // $text = 'Can you please specify what you actually want? Just.';
        $o = new \App\Classes\PoemMaker('fr');
        $data =  $o->getLastSyllabe($text);
        dd($data);
    }
}

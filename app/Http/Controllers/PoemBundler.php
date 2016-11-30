<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Poem;
use App\Models\Alexandrine;

class PoemBundler extends Controller
{
    /*
     * Rendering homepage
     */
    public function index()
    {
        $poem = Poem::orderBy('created_at', 'desc')->first();
        $alexandrines = $poem->alexandrines()->orderBy('rank')->get();

        $data = [
            'headTitle' => 'Poem Bundler',
            'poem' => $poem,
            'alexandrines' => $alexandrines
        ];

        return view('poem-bundler.homepage', $data);
    }

    /*
     * Test method
     */
    public function create()
    {
        $call = new \App\Classes\PoemMaker('fr');
        $call->generatePoem();
    }
}

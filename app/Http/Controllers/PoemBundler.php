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
        $poem = Poem::find(1);
        $alexandrines = Poem::find(1)->alexandrines()->orderBy('rank')->get();

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
        $o = new Alexandrine();
        $count = $o->getSimilarPhonemes();

        // Let's take only available rhymes
        $rhymes = [];
        foreach ($count as $c) {
            if ($c->total > 2) {
                $rhymes[] = $c->phoneme;
                $alexandrines = $o->getAlexandrinesByPhoneeme($c->phoneme);
                dd($alexandrines);
            }
        }

        dd($rhymes);
    }
}

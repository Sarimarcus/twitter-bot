<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Poem;
use App\Models\Alexandrine;

class PoemBundler extends Controller
{
    /*
     *
     */
    public function index()
    {
        $poem = Poem::find(1)->alexandrines;
        dd($poem);
    }
}

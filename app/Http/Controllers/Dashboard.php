<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Bot;
use App\Models\Stat;

class Dashboard extends Controller
{
    public function homepage()
    {
        /*
         * Getting bots
         */
        $bots = Bot::all();

        return view('dashboard.homepage', ['bots' => $bots, 'headTitle' => 'Bots farm']);
    }
}

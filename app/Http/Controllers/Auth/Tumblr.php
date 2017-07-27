<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Requests;

use Socialite;

class Tumblr extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('tumblr')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('tumblr')->user();

        $token = $user->token;
        $tokenSecret = $user->tokenSecret;

        \Cache::forever('TumblrToken', $token);
        \Cache::forever('TumblrTokenSecret', $tokenSecret);

        \Log::info('Tumblr OAuth Token : ' . $token);
        \Log::info('Tumblr OAuth Token Secret : ' . $tokenSecret);
    }
}
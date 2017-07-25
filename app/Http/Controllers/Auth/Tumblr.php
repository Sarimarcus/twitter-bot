<?php

namespace App\Http\Controllers\Auth;

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

        \Log::info('Tumblr OAuth Token : ' . $token);
        \Log::info('Tumblr OAuth Token Secret : ' . $tokenSecret);

        // Now store these credentials somewhere as they will need to be set
        // on the tumblr api client in order to make requests.
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TumblrServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('\Tumblr\API\Client', 'app.tumblr.api');

        $this->app->singleton(\Tumblr\API\Client::class, function ($app) {
            $client = new \Tumblr\API\Client(config('services.tumblr.client_id'), config('services.tumblr.client_secret'));

            // If OAuth is OK
            if (\Cache::has('TumblrToken') && \Cache::has('TumblrTokenSecret')) {
                $client->setToken(\Cache::get('TumblrToken'), \Cache::get('TumblrTokenSecret'));
            }

            return $client;
        });
    }
}

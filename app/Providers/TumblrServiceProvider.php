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
        $this->app->singleton(\Tumblr\API\Client::class, function ($app) {
            $client = new Tumblr\API\Client($consumerKey, $consumerSecret);

            return $client;
        });
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SavePopularTweets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:save-popular-tweets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save popular tweets from search';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Classes\TwitterBot::runTask('savePopularTweets');
    }
}

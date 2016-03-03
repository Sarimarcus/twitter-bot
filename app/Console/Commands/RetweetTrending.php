<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetweetTrending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:retweet-trending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retweet one trending tweet from Paris';

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
        \App\Classes\TwitterBot::runTask('retweetTrending');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetSuggested extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:get-suggested';

    /**
     * The console command description.
     * @todo add slug as a parameter
     * @var string
     */
    protected $description = 'Getting suggested users for a specific slug';

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
        \App\Classes\TwitterBot::runTask('getSuggested');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:purge-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleaning useless users';

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
        \App\Classes\TwitterBot::purgeUsers();
    }
}

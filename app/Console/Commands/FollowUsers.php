<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FollowUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:follow-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Follow followers from interesting accounts';

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
        \App\Classes\TwitterBot::followUsers();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetQOTD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:get-qotd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get quote of the day from https://theysaidso.com';

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
        $call = new \App\Classes\Quote();
        $r = $call->getQOTD();
    }
}

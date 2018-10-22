<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePoem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poem:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the daily poem';

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
        //$call = new \App\Classes\PoemMaker('fr');
        $call = new \App\Classes\PoemMaker('es');
        $r = $call->generatePoem();
    }
}

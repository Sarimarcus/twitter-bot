<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetInspiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poem:get-inspiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search tweets for alexandrine';

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
        //$o = new \App\Classes\PoemMaker('fr');
        $o = new \App\Classes\PoemMaker('es');
        $o->getInspiration();
    }
}

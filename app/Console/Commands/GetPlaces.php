<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poem:get-places';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Places from coordinates';

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
        $call = new \App\Classes\PoemMaker('fr');
        $r = $call->getPlaceID();
    }
}

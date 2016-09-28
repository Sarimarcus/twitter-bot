<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alexandrine;

class SearchPhoneme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poem:search-phoneme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get alexandrines and search for last phoneme';

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
        $alexandrines = Alexandrine::all();
        foreach ($alexandrines as $alexandrine) {
            $o = new \App\Classes\PoemMaker('fr');
            $phoneme = $o->getLastPhoneme($alexandrine['text']);

            $a = Alexandrine::find($alexandrine['id']);
            $a->phoneme = $phoneme;
            $a->save();
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Bot;
use App\Models\Stat;
use Khill\Lavacharts\Lavacharts;

class Stats extends Controller
{
    public function test()
    {
        /*
         * Getting bots
         */
        $bots = Bot::all();

        /*
         * Time for charts !
         */
        $stocksTable = \Lava::DataTable();  // Lava::DataTable() if using Laravel

        $stocksTable->addDateColumn('Day of Month');

        $data = [];
        foreach ($bots as $bot) {
            /*
             * Columns
             */
            $stocksTable->addNumberColumn($bot->screen_name);

            /* Preparing data */
            $stats = $bot->stats;
            foreach ($stats as $stat) {
                $data[$stat->date][$bot->id] = [
                    'followers_count' => $stat->followers_count,
                    'friends_count' => $stat->friends_count
                ];
            }
        }

        asort($data);

        //dd($data);

        /*// Random Data For Example
        for ($a = 1; $a < 30; $a++) {
            $rowData = [
              "2014-8-$a", rand(800, 1000), rand(800, 1000), rand(800, 1000), rand(800, 1000)
            ];

            $stocksTable->addRow($rowData);
        }*/

        \Lava::LineChart('Stocks', $stocksTable, ['title' => 'Bots stats']);
        return view('stats.test');
    }
}

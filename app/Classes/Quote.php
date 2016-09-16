<?php

namespace App\Classes;

/**
*  Retrieve quotes from  https://theysaidso.com/api/
*/
class Quote
{
    const API_URL = 'http://quotes.rest/';

    private $client;


    function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => self::API_URL]);
    }

    public function getQOTD()
    {
        $response = $this->client->request('GET', 'qod.json');
        return $response;
    }
}
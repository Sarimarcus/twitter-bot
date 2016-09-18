<?php

namespace App\Classes;

use App\Models\Quote;

/**
*  Retrieve quotes from  https://theysaidso.com/api/
*/
class QuoteApi
{
    const API_URL = 'http://quotes.rest/';

    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => self::API_URL]);
    }

    public function getQOTD()
    {
        // Making the call
        try {
            $response = $this->client->request('GET', 'qod.json');
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            \Log::error('Can\'t get quote of the day : ' . $e->getMessage());
        }

        if (200 === $response->getStatusCode()) {
            $data = $response->getBody();
            $quote = json_decode($data);

            // Inserting quote in DB
            $q = new Quote;
            $q->text = $quote->contents->quotes[0]->quote;
            $q->author = $quote->contents->quotes[0]->author;
            $q->illustration = $quote->contents->quotes[0]->background;
            $q->length = $quote->contents->quotes[0]->length;
            $q->save();

            \Log::info('Getting quote of the day');
        }
    }
}

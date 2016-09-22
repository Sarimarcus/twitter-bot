<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

class Wurstify extends Controller
{
    const URL = 'https://wurstify.me/proxy?since=0&url=';

    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => self::URL]);
    }

    /**
     * Return a wurstified image
     *
     * @param  Request  $request
     * @return Response
     */
    public function make(Request $request)
    {
        //$img = $request->input('img');
        $data = $request->all();

        // Making the call
        try {
            $response = $this->client->request('GET', 'proxy', [
                'query' => ['since' => 0, 'url' => $data['url']]
            ]);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            \Log::error('Can\'t Wurstify : ' . $e->getMessage());
        }

        if (200 === $response->getStatusCode()) {
            // Wurstified img
            $wurstified = $response->getBody();

            // Same headers as original picture
            $headers = array('Content-Type' => $response->getHeader('Content-Type'));

            // Showing the picture
            $response = \Response::make($wurstified, 200, $headers);
            ob_end_clean();

            return $response;
        }
    }
}

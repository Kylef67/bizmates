<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    public function getTile($z, $x, $y)
    {
        $apiKey = env('GEOAPIFY_API_KEY');

        //dd($apiKey);
        $url = "https://maps02.geoapify.com/v1/tile/osm-carto/{$z}/{$x}/{$y}.png?apiKey=44c4591e6901447fa142bd3a3a5ebdcf";
        $response = Http::get($url);

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type'));
    }
}

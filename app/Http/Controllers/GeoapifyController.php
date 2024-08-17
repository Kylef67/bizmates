<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoapifyController extends Controller
{
    protected $apiKey;
    protected $mapUrl;
    protected $apiUrl;

    /**
     * Constructor to initialize API key, map URL, and API URL.
     */
    public function __construct()
    {
        $this->apiKey = config('app.geoapify_api_key');
        $this->mapUrl = config('app.geoapify_map_url');
        $this->apiUrl = config('app.geoapify_api_url');
    }

    /**
     * Retrieve a map tile from Geoapify.
     *
     * @param int $z Zoom level of the tile.
     * @param int $x X-coordinate of the tile.
     * @param int $y Y-coordinate of the tile.
     * @return \Illuminate\Http\Response
     */
    public function getTile($z, $x, $y)
    {
        $url = "{$this->mapUrl}/v1/tile/osm-carto/{$z}/{$x}/{$y}.png?apiKey={$this->apiKey}";
        $response = Http::withOptions([
            'verify' => false,
        ])->get($url);

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type'));
    }

    /**
     * Search for places using the Geoapify API.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $this->validate($request, [
            'bias' => 'required|string',
            'categories' => 'required|string',
        ]);

        $bias = $request->input('bias');
        $categories = $request->input('categories');

        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->get("{$this->apiUrl}/v2/places", [
                'bias' => $bias,
                'categories' => $categories,
                'apiKey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $features = $response->json()['features'];
                return response()->json($features);
            } else {
                return response()->json(['error' => 'Failed to fetch places'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error searching for places: ' . $e->getMessage()], 500);
        }
    }
}

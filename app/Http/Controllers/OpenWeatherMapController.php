<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenWeatherMapController extends Controller
{
    protected $openWeatherApiKey;
    protected $openWeatherApiUrl;

    /**
     * Constructor to initialize OpenWeatherMap API key and URL.
     */
    public function __construct()
    {
        $this->openWeatherApiKey = config('app.openweathermap_api_key');
        $this->openWeatherApiUrl = config('app.openweathermap_api_url');
    }

    /**
     * Retrieve the weather forecast for a specified city.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForecast(Request $request)
    {
        $this->validate($request, [
            'city' => 'required|string',
            'units' => 'required|string'
        ]);

        $city = $request->input('city');

        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->get("{$this->openWeatherApiUrl}/data/2.5/forecast", [
                'q' => $city,
                'appid' => $this->openWeatherApiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'Failed to fetch weather data'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching weather data: ' . $e->getMessage()], 500);
        }
    }
}

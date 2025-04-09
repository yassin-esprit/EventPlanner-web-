<?php
// src/Service/WeatherService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WeatherService
{
    private $httpClient;
    private $apiKey;

public function __construct(
    HttpClientInterface $httpClient,
    ParameterBagInterface $params
) {
    $this->httpClient = $httpClient;
    $this->apiKey = $params->get('openweathermap.api_key');
}
    public function getWeatherForecast(string $location, \DateTimeInterface $date): ?array
    {
        try {
            // Calculate days difference between now and event date
            $today = new \DateTime();
            $diff = $today->diff($date);
            $daysDiff = (int)$diff->format('%a');

            // OpenWeatherMap API endpoint (using 5 day forecast)
            $url = 'https://api.openweathermap.org/data/2.5/forecast';
            
            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'q' => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric' // or 'imperial' for Fahrenheit
                ]
            ]);

            $data = $response->toArray();

            // Find the forecast closest to the event date/time
            $eventTimestamp = $date->getTimestamp();
            $closestForecast = null;
            $smallestDiff = PHP_INT_MAX;

            foreach ($data['list'] as $forecast) {
                $forecastTimestamp = $forecast['dt'];
                $timeDiff = abs($forecastTimestamp - $eventTimestamp);
                
                if ($timeDiff < $smallestDiff) {
                    $smallestDiff = $timeDiff;
                    $closestForecast = $forecast;
                }
            }

            if ($closestForecast) {
                return [
                    'temperature' => $closestForecast['main']['temp'],
                    'conditions' => $closestForecast['weather'][0]['description'],
                    'icon' => $closestForecast['weather'][0]['icon'],
                    'humidity' => $closestForecast['main']['humidity'],
                    'wind_speed' => $closestForecast['wind']['speed'],
                    'days_until' => $daysDiff
                ];
            }

            return null;
        } catch (\Exception $e) {
            // Log error or handle it appropriately
            return null;
        }
    }

    public function getWeatherRecommendation(array $weatherData): string
    {
        if (!$weatherData) {
            return 'Weather information is currently unavailable.';
        }

        $temp = $weatherData['temperature'];
        $conditions = strtolower($weatherData['conditions']);
        $recommendation = '';

        // Temperature recommendations
        if ($temp < 5) {
            $recommendation .= 'Very cold! Wear heavy winter clothing. ';
        } elseif ($temp < 15) {
            $recommendation .= 'Chilly. A jacket or sweater is recommended. ';
        } elseif ($temp < 25) {
            $recommendation .= 'Pleasant temperatures. Light clothing should be comfortable. ';
        } else {
            $recommendation .= 'Hot weather. Wear light, breathable clothing and stay hydrated. ';
        }

        // Weather condition recommendations
        if (strpos($conditions, 'rain') !== false) {
            $recommendation .= 'Bring an umbrella or raincoat. ';
        } elseif (strpos($conditions, 'snow') !== false) {
            $recommendation .= 'Dress warmly and be prepared for snow. ';
        } elseif (strpos($conditions, 'clear') !== false) {
            $recommendation .= 'Sunny weather expected. Consider sunscreen. ';
        } elseif (strpos($conditions, 'cloud') !== false) {
            $recommendation .= 'Cloudy skies. ';
        }

        return trim($recommendation);
    }
}
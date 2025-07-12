<?php

namespace CoreDev\Meteo\services;

use CoreDev\Meteo\models\Settings;
use Craft;
use craft\base\Component;
use GuzzleHttp\Exception\RequestException;

class WeatherService extends Component
{
    private string $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    // Weather code to icon/description mapping for Weather Icons CSS Lib
    private static array $weatherCodeMap = [
        0 => ["description" => "Clear sky", "day" => "wi wi-day-sunny", "night" => "wi wi-night-clear"],
        1 => ["description" => "Mainly clear", "day" => "wi wi-day-sunny-overcast", "night" => "wi wi-night-alt-partly-cloudy"],
        2 => ["description" => "Partly cloudy", "day" => "wi wi-day-cloudy", "night" => "wi wi-night-alt-cloudy"],
        3 => ["description" => "Overcast", "icon" => "wi wi-cloudy"],
        45 => ["description" => "Fog", "icon" => "wi wi-fog"],
        48 => ["description" => "Depositing rime fog", "icon" => "wi wi-fog"],
        51 => ["description" => "Light drizzle", "icon" => "wi wi-sprinkle"],
        53 => ["description" => "Moderate drizzle", "icon" => "wi wi-sprinkle"],
        55 => ["description" => "Dense drizzle", "icon" => "wi wi-sprinkle"],
        56 => ["description" => "Light freezing drizzle", "icon" => "wi wi-rain-mix"],
        57 => ["description" => "Dense freezing drizzle", "icon" => "wi wi-rain-mix"],
        61 => ["description" => "Slight rain", "icon" => "wi wi-showers"],
        63 => ["description" => "Moderate rain", "icon" => "wi wi-rain"],
        65 => ["description" => "Heavy rain", "icon" => "wi wi-rain"],
        66 => ["description" => "Light freezing rain", "icon" => "wi wi-rain-mix"],
        67 => ["description" => "Heavy freezing rain", "icon" => "wi wi-rain-mix"],
        71 => ["description" => "Slight snowfall", "icon" => "wi wi-snow"],
        73 => ["description" => "Moderate snowfall", "icon" => "wi wi-snow"],
        75 => ["description" => "Heavy snowfall", "icon" => "wi wi-snow"],
        77 => ["description" => "Snow grains", "icon" => "wi wi-snowflake-cold"],
        80 => ["description" => "Slight rain showers", "icon" => "wi wi-showers"],
        81 => ["description" => "Moderate rain showers", "icon" => "wi wi-showers"],
        82 => ["description" => "Violent rain showers", "icon" => "wi wi-storm-showers"],
        85 => ["description" => "Slight snow showers", "icon" => "wi wi-snow-wind"],
        86 => ["description" => "Heavy snow showers", "icon" => "wi wi-snow-wind"],
        95 => ["description" => "Thunderstorm", "icon" => "wi wi-thunderstorm"],
        96 => ["description" => "Thunderstorm with slight hail", "icon" => "wi wi-hail"],
        99 => ["description" => "Thunderstorm with heavy hail", "icon" => "wi wi-hail"],
    ];

    public function getWeather(?float $lat = null, ?float $lon = null, ?array $options = null): ?array
    {
        // Get plugin settings
        $plugin = \CoreDev\Meteo\Meteo::getInstance();
        $settings = $plugin->getSettings();

        // Use settings if lat/lon not provided
        $lat = $lat ?? $settings->latitude;
        $lon = $lon ?? $settings->longitude;

        // Build all params here
        $params = [
            'latitude' => $lat,
            'longitude' => $lon,
            'timezone' => $settings->timezone,
            'forecast_days' => $settings->forecastDays,
            'past_days' => $settings->pastDays,
            'temperature_unit' => $settings->temperatureUnit,
            'windspeed_unit' => $settings->windspeedUnit,
            'precipitation_unit' => $settings->precipitationUnit,
            'timeformat' => $settings->timeformat,
        ];
        if ($options) {
            $params = array_merge($params, $options);
        }

        foreach (array_keys(Settings::$granularityOptions) as $granularity) {
            $validVars = array_keys($settings->getGranularityOptionsFor($granularity));
            $selectedVars = (array)($settings->selectedVariables[$granularity] ?? []);
            // Always include 'is_day' if weather_code is selected for this granularity
            if (in_array('weather_code', $selectedVars, true) && in_array('is_day', $validVars, true)) {
                if (!in_array('is_day', $selectedVars, true)) {
                    $selectedVars[] = 'is_day';
                }
            }
            // Add extra variables from settings.extraVariables[granularity] (comma-separated)
            $extraVars = [];
            if (!empty($settings->extraVariables[$granularity])) {
                $extraVars = array_map('trim', explode(',', $settings->extraVariables[$granularity]));
            }
            $allVars = array_unique(array_filter(array_merge($selectedVars, $extraVars)));
            if (!empty($allVars)) {
                $params[$granularity] = implode(',', $allVars);
            }
        }

        // Build cache key from all params
        $cacheKey = 'weather_data';
        $cacheDuration = $settings->cacheDuration ?? 3600; // 1 hour

        $result = Craft::$app->cache->getOrSet($cacheKey, function() use ($params) {
            $data = $this->fetchFromApi($params);
            Craft::info('WeatherService: Fresh data fetched from API', __METHOD__);
            return $data;
        }, $cacheDuration);

        // Post-process: append icon/description if weather_code present
        if (is_array($result)) {
            $result = $this->appendWeatherMeta($result);
        }
        return $result;
    }

    // Renamed for clarity: appendWeatherIcons -> appendWeatherMeta
    private function appendWeatherMeta(array $data): array
    {
        $granularities = array_keys(Settings::$granularityOptions);
        foreach ($granularities as $granularity) {
            if (isset($data[$granularity]['weather_code'])) {
                $weatherCode = $data[$granularity]['weather_code'];
                $isDay = null;
                if (isset($data[$granularity]['is_day'])) {
                    // is_day can be array or int
                    $isDay = $data[$granularity]['is_day'];
                }
                if (is_array($weatherCode)) {
                    foreach ($weatherCode as $i => $code) {
                        $iconData = self::$weatherCodeMap[$code] ?? null;
                        if ($iconData) {
                            $meta = $iconData;
                            if (isset($iconData['day'], $iconData['night']) && isset($isDay[$i])) {
                                $meta['icon'] = $isDay[$i] ? $iconData['day'] : $iconData['night'];
                            } elseif (isset($iconData['day'], $iconData['night']) && is_int($isDay)) {
                                $meta['icon'] = $isDay ? $iconData['day'] : $iconData['night'];
                            } elseif (isset($iconData['icon'])) {
                                $meta['icon'] = $iconData['icon'];
                            }
                            $data[$granularity]['weather_code_meta'][$i] = $meta;
                        }
                    }
                } elseif (is_int($weatherCode) || ctype_digit((string)$weatherCode)) {
                    $iconData = self::$weatherCodeMap[$weatherCode] ?? null;
                    if ($iconData) {
                        $meta = $iconData;
                        if (isset($iconData['day'], $iconData['night'])) {
                            if (is_int($isDay)) {
                                $meta['icon'] = $isDay ? $iconData['day'] : $iconData['night'];
                            }
                            // else do not set icon if is_day is missing
                        } elseif (isset($iconData['icon'])) {
                            $meta['icon'] = $iconData['icon'];
                        }
                        $data[$granularity]['weather_code_meta'] = $meta;
                    }
                }
            }
        }
       
        return $data;
    }

    private function fetchFromApi(array $params): ?array
    {
        $client = Craft::createGuzzleClient();
        $url = $this->baseUrl . '?' . http_build_query($params);

        try {
            $response = $client->get($url)->getBody()->getContents();
            $data = \craft\helpers\Json::decode($response);
            return $data;
        } catch (RequestException $e) {
            Craft::error('Meteo API request failed: ' . $e->getMessage(), __METHOD__);
        } catch (\Exception $e) {
            Craft::error('Unexpected error fetching weather: ' . $e->getMessage(), __METHOD__);
        }
        return null;
    }
}

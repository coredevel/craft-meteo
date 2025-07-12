<?php

namespace CoreDev\Meteo\models;

use craft\base\Model;

class Settings extends Model
{
    public const GRANULARITY_CURRENT = 'current';
    public const GRANULARITY_MINUTELY_15 = 'minutely_15';
    public const GRANULARITY_HOURLY = 'hourly';
    public const GRANULARITY_DAILY = 'daily';

    public float $latitude = 0.0;
    public float $longitude = 0.0;
    public string $timezone = 'UTC';

    public int $forecastDays = 1;
    public int $pastDays = 0;
    public string $temperatureUnit = 'celsius';
    public string $windspeedUnit = 'kmh';
    public string $precipitationUnit = 'mm';
    public string $timeformat = 'iso8601';

    // Common options for all granularities
    public static array $commonWeatherOptions = [
        'weather_code' => 'Weather code',
    ];

    // Common forecast options for current, minutely_15, hourly
    public static array $commonForecastOptions = [
        "temperature_2m" => "Temperature (2 m)",
        "relative_humidity_2m" => "Relative Humidity (2 m)",
        "apparent_temperature" => "Apparent Temperature",
        'is_day' => 'Is Day or Night',
        "precipitation" => "Precipitation",
        "rain" => "Rain",
        "snowfall" => "Snowfall",
        "wind_speed_10m" => "Wind Speed (10 m)",
        "wind_direction_10m" => "Wind Direction (10 m)",
        "wind_gusts_10m" => "Wind Gusts (10 m)",
        "visibility" => "Visibility",
    ];

    public static array $granularityOptions = [
        self::GRANULARITY_CURRENT => [
            'cloudcover' => 'Cloud cover Total',
            'pressure_msl' => 'Sealevel Pressure',
            'surface_pressure' => 'Surface Pressure',
        ],
        self::GRANULARITY_MINUTELY_15 => [
            
        ],
        self::GRANULARITY_HOURLY => [
            'cloudcover' => 'Cloud cover Total',
            'pressure_msl' => 'Sealevel Pressure',
            'surface_pressure' => 'Surface Pressure',
        ],
        self::GRANULARITY_DAILY => [
            'sunrise' => 'Sunrise',
            'sunset' => 'Sunset',
        ],
    ];


    public array $selectedVariables = [
        self::GRANULARITY_CURRENT => [],
        self::GRANULARITY_MINUTELY_15 => [],
        self::GRANULARITY_HOURLY => [],
        self::GRANULARITY_DAILY => [],
    ];

    public array $extraVariables  = [
        self::GRANULARITY_CURRENT => [],
        self::GRANULARITY_MINUTELY_15 => [],
        self::GRANULARITY_HOURLY => [],
        self::GRANULARITY_DAILY => [],
    ];

    public int $cacheDuration = 3600; // seconds

    public function rules(): array
    {
        return [
            [['latitude', 'longitude'], 'number'],
            [['timezone', 'temperatureUnit', 'windspeedUnit', 'precipitationUnit', 'timeformat'], 'string'],
            [['forecastDays', 'pastDays', 'cacheDuration'], 'integer'],
            [['selectedVariables', 'extraVariables'], 'safe'],
        ];
    }

    // Renamed to avoid Yii magic getter conflict
    public function getGranularityOptionsFor(string $granularity): array
    {
        // For daily, only merge commonWeatherOptions + daily
        if ($granularity === self::GRANULARITY_DAILY) {
            return array_merge(self::$commonWeatherOptions, self::$granularityOptions[$granularity] ?? []);
        }
        // For others, merge commonWeatherOptions + commonForecastOptions + specific
        return array_merge(self::$commonWeatherOptions, self::$commonForecastOptions, self::$granularityOptions[$granularity] ?? []);
    }

    // Expose static granularityOptions as an instance property for Twig/Yii
    public function getGranularityOptions(): array
    {
        return self::$granularityOptions;
    }
}

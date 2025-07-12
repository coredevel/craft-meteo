<?php

namespace CoreDev\Meteo\variables;

use CoreDev\Meteo\Meteo;

class MeteoVariable
{
    public function getWeather(?float $lat = null, ?float $lon = null, ?array $options = null): ?array
    {
        return Meteo::$plugin->weather->getWeather($lat, $lon, $options);
    }
}

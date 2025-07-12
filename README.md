# craft-meteo

A headless weather plugin for Craft CMS using the Open-Meteo API.

---

## Features
- **Headless, frontend-agnostic**: No frontend output, just robust weather data for your templates or APIs.
- **Granular variable selection**: Choose which weather variables to fetch for each granularity (current, minutely_15, hourly, daily).
- **Weather code to icon/description mapping**: Built-in mapping for Weather Icons CSS, including day/night logic.
- **Admin panel**: Configure API variables, location, units, and cache from a Craft CP section.
- **Twig variable**: Access weather data anywhere in your templates: `craft.meteo.weather()`
- **Cache management**: Weather data is cached for performance; clear cache from the admin panel.
- **Extensible**: Clean, service-based architecture for easy extension.

---

## Requirements
- Craft CMS 5.8.0+
- PHP 8.2+

---

## Installation

**From the Plugin Store:**
- Go to the Plugin Store in your project’s Control Panel and search for “meteo”.
- Press “Install”.

**With Composer:**
```bash
composer require coredev/craft-meteo
./craft plugin/install craft-meteo
```

---

## Configuration & Usage

### Settings
- **Location**: Set latitude, longitude, and timezone.
- **Units**: Choose temperature, windspeed, precipitation units, and time format.
- **Variables**: For each granularity (current, minutely_15, hourly, daily), select which weather variables to fetch.
- **Cache Duration**: Set how long weather data is cached (in seconds).

All settings are managed in the Craft CP under the “Meteo Settings” section.

### Twig Usage
Fetch weather data anywhere in your templates:
```twig
{% set weather = craft.meteo.weather() %}
{{ dump(weather) }}
```
- You can pass `lat`, `lon`, or override any settings:
```twig
{% set weather = craft.meteo.weather(49.2, -2.1, { forecast_days: 2 }) %}
```

### Data Structure
The returned array mirrors the Open-Meteo API, with additional `weather_code_meta` for icon/description mapping:
```json
{
  "current": {
    "weather_code": 2,
    "is_day": 1,
    "weather_code_meta": {
      "description": "Partly cloudy",
      "icon": "wi wi-day-cloudy"
    },
    ...
  },
  "hourly": { ... },
  "daily": { ... }
}
```
- If `is_day` is present, icon will be day/night specific. If not, a generic icon is used.

### Weather Icons CSS Integration
To display weather icons in your frontend, include the [Weather Icons CSS library](https://erikflowers.github.io/weather-icons/) in your HTML:

```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.10/css/weather-icons.min.css">
```

- See the [Weather Icons project](https://erikflowers.github.io/weather-icons/) for icon previews and documentation.
- The plugin provides the correct icon class in the `weather_code_meta.icon` property for each weather data point.

**Example usage in Twig:**
```twig
<i class="{{ weather.current.weather_code_meta.icon }}"></i>
<span>{{ weather.current.weather_code_meta.description }}</span>
```

---

## Admin Tools
- **Clear Cache**: Use the “Clear Cache” button in the Meteo admin panel to force-refresh weather data.
- **Settings Persistence**: All settings are stored per environment and persist across deployments.

---

## Developer Notes
- **Extending**: Add new variables or mapping by editing `Settings.php` and `WeatherService.php`.
- **API**: All param-building logic is in `WeatherService::getWeather`. No logic in `fetchFromApi`.
- **Debugging**: Debug logs are written for variable selection and API param-building.

---

## Credits & Support
- **Core Development x iPop Digital Collaboration**
- Support: info@ipopdigital.com
- MIT License

---

## Changelog
See [CHANGELOG.md](CHANGELOG.md) for release notes.

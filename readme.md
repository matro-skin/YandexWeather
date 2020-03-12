# Laravel Yandex Weather API
Here are a few short examples of what you can do:
```php
use Matroskin\YandexWeather\YandexWeather;
// get weather "now"
$fact = YandexWeather::fact(55.75, 37.61);
$fact = YandexWeather::tariff(1)->fact(55.75, 37.61);
// or get forecast
$forecast = YandexWeather::tariff(1)->locale('ru_RU')->forecast(55.75, 37.61);
```
## Installation
You can install this package via composer using this command:
```bash
composer require "matroskin/yandex-weather"
```
The package will automatically register itself.

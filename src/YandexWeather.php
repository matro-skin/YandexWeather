<?php

namespace Matroskin\YandexWeather;

use Ixudra\Curl\Facades\Curl;

class YandexWeather {

    const LOCALE = 'ru_RU';

    public $lat;
    public $long;

    protected $locale;

    /*
     * 0 - тариф «Тестовый»
     * 1 - тариф «Погода на вашем сайте»
     */
    protected $tariff;

    protected $key;
    protected $response = [];

    protected static $_instance;

    /**
     * YandexWeather constructor.
     *
     * @param string $lat
     * @param string $long
     * @param string|null $locale
     *
     * @throws \Exception
     */
    private function __construct($tariff = 0)
    {
        $this->locale = self::LOCALE;
        $this->tariff = $tariff;
        $this->key = env('YANDEX_WEATHER_API');
        if(! $this->key) {
            throw new \Exception('Place your Yandex Weather API key to .env: YANDEX_WEATHER_API');
        }
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public static function locale(string $locale)
    {
        $instance = self::getInstance();
        $instance->locale = $locale;
        return $instance;
    }

    /**
     * @param string $tariff
     *
     * @return $this
     */
    public static function tariff(string $tariff)
    {
        $instance = self::getInstance();
        $instance->tariff = $tariff;
        return $instance;
    }

    /**
     * @param string $lat
     * @param string $long
     * @param string $key
     *
     * @return object|null
     */
    protected static function prepare(string $lat, string $long, string $key)
    {
        $instance = self::getInstance();
        try {
            $instance->response = $instance->getResponse( $lat, $long );
        } catch ( \Exception $e ) {
            return null;
        }
        if( $instance->response->isEmpty() ) {
            return null;
        }
        return $instance->response[$key] ?? null;
    }

    /**
     * Return "fact" of weather object
     *
     * @return object|null
     * @throws \Exception
     */
    public static function fact(string $lat, string $long)
    {
        return self::prepare($lat, $long,'fact');
    }

    /**
     * Return "forecast" of weather object
     *
     * @return object|null
     * @throws \Exception
     */
    public static function forecast(string $lat, string $long)
    {
        return self::prepare($lat, $long,'forecast');
    }

    /**
     * Return weather object
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function getResponse($lat, $long)
    {
        $this->lat = $lat;
        $this->long = $long;
        try {
            $this->response = Curl::to( $this->getUrl() )
                                  ->withHeader( sprintf("X-Yandex-API-Key: %s", $this->key ))
                                  ->asJson()
                                  ->get();
        }
        catch (\Exception $e) {
            report($e);
        }
        if( isset($this->response->status) && $this->response->status > 200 ) {
            throw new \Exception( $this->response->message );
        }
        return collect( $this->response );
    }

    /**
     * Return API url
     *
     * @return string
     */
    protected function getUrl()
    {
        return sprintf("%s?lat=%s&lon=%s&%s",
            $this->getBaseUrl(),
            $this->lat,
            $this->long,
            $this->locale
        );
    }

    /**
     * Return API base url
     *
     * @return string
     */
    protected function getBaseUrl()
    {
        switch ($this->tariff) {
            case 1 :
                return 'https://api.weather.yandex.ru/v1/informers';
            case 0 :
            default :
                return 'https://api.weather.yandex.ru/v1/forecast';
        }
    }

    private function __clone() {}

    private function __wakeup() {}

}

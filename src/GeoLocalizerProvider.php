<?php

namespace WPKirk\GeoLocalizer;

class GeoLocalizerProvider
{
    /**
     * Used to get geocoding information by IP address
     */

    const GEOIP_ENDPOINT = 'http://api.ipstack.com/check?access_key=';

    /**
     * Google Maps - used for reverse geocoding
     */
    const GOOGLE_REVERSE_GEOCODING = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=';

    /**
     * Output format: json, xml, csv
     *
     * @var string
     */
    protected $format = '&output=json&legacy=1';

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $method = "callable" . ucfirst($name);

        $instance = new self;

        if (method_exists($instance, $method)) {
            return call_user_func_array([$instance, $method], $arguments);
        }

        return $instance;
    }

    /**
     * @return array|bool
     */
    protected function callableGeoIp()
    {
        return $this->geoIP();
    }

    /**
     * Return the geo IP information by ip address.
     *
     *    array(17) {
     *      ["longitude"]=> float(12.4833)
     *      ["latitude"]=> float(41.9)
     *      ["asn"]=> string(6) "AS3269"
     *      ["offset"]=> string(1) "2"
     *      ["ip"]=> string(12) "87.3.222.157"
     *      ["area_code"]=> string(1) "0"
     *      ["continent_code"]=> string(2) "EU"
     *      ["dma_code"]=> string(1) "0"
     *      ["city"]=> string(4) "Rome"
     *      ["timezone"]=> string(11) "Europe/Rome"
     *      ["region"]=> string(5) "Lazio"
     *      ["country_code"]=> string(2) "IT"
     *      ["isp"]=> string(21) "Telecom Italia S.p.a."
     *      ["postal_code"]=> string(5) "00141"
     *      ["country"]=> string(5) "Italy"
     *      ["country_code3"]=> string(3) "ITA"
     *      ["region_code"]=> string(2) "07"
     *    }
     *
     * @param string $ip Optional. The ip address or empty for `$_SERVER['REMOTE_ADDR']`,
     *
     * @return array|bool
     */
    protected function geoIP($ip = '')
    {

        // Get the ip stack API Key
        $api_key = apply_filters('wpbones_geolocalizer_ipstack_api_key', '');

        if (empty($api_key)) {
            return false;
        }

        // Get current ip
        $ip = empty($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;

        // use below for debug, insert define( 'GEOLOCALIZER_DEMO', true ); in your wp-config.php
        // to enable demo IP
        if (defined('GEOLOCALIZER_DEMO')) {
            $ip = "80.181.80.86";
        }

        // Build endpoint API
        $endpoint = self::GEOIP_ENDPOINT . $api_key . $this->format;

        $response = wp_remote_get($endpoint);

        // Dead connection
        if (200 != wp_remote_retrieve_response_code($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);

        return (array)json_decode($body);
    }

    /**
     * @param array $countries
     */
    protected function callableHasCountries($countries = [])
    {
        if (!empty($countries)) {
            // Get GEO info
            $geo = $this->geoIP();

            if (isset($geo['error'])) {
                return false;
            }

            /*
             * {
             *   "ip":"80.181.80.86",
             *   "country_code":"it",
             *   "country_name":"italy",
             *   "region_code":"62",
             *   "region_name":"latium",
             *   "city":"rome",
             *   "zip_code":"00199",
             *   "time_zone":"europe\/rome",
             *   "latitude":"41.8919","longitude":"12.5113",
             *   "metro_code":"0"
             * }
             */

            // Turn all geo info in lowercase
            $geo = array_map(
                function ($value) {
                    return is_string($value) ? strtolower($value) : $value;
                },
                $geo
            );

            // Turn all $countries info in lowercase
            $countries = array_map(
                function ($value) {
                    return is_string($value) ? strtolower($value) : $value;
                },
                $countries
            );

            return in_array($geo['country_name'], $countries);
        }

        return false;
    }

    protected function callableCountries()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . "countries";

        return $wpdb->get_results("SELECT country FROM {$tableName} ORDER BY country");

    }

    /**
     * Display a content of shortcode only if the user geo localization info respect the shortcodes params.
     *
     *     [wp_geolocalizer city="Rome"]
     *       Only for Rome
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer city="rome"]
     *       Only for Rome
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer city="rome,london"]
     *       Only for Rome and Landon
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer region_name="lazio"]
     *       Only for region (Italy) Lazio
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer country_code="IT"]
     *       Italian only
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer country_name="italy"]
     *       Italian only
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer zip_code="00137"]
     *       Wow
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer ip="80.182.82.82"]
     *       Only for me
     *     [/wp_geolocalizer]
     *
     *     [wp_geolocalizer time_zone="europe\rome"]
     *       Rome/Berlin time zone
     *     [/wp_geolocalizer]
     *
     * @param array $atts Attribute into the shortcode
     * @param string $content Optional. $content HTML content
     *
     * @return string
     */
    protected function callableShortcode($atts, $content = null)
    {
        // Defaults
        $defaults = [
            'ip' => '',
            'country_code' => '',
            'country_name' => '',
            'region_code' => '',
            'region_name' => '',
            'city' => '',
            'zip' => '',
            'debug' => false,
        ];

        // Merge with shortcode.
        $atts = shortcode_atts($defaults, $atts, 'wp_geolocalizer');

        if ($atts['debug']) {
            $info = $this->geoIP();

            // display $info in json format
            echo !empty($info) ? '<h4>Below the Geolocalizer info</h4><pre>' . json_encode($info, JSON_PRETTY_PRINT) . '</pre>' : "";

            return null;
        }

        unset($atts['debug']);

        $isEmpty = true;

        foreach (array_keys($defaults) as $key) {
            if (!empty($atts[$key])) {
                $isEmpty = false;
                break;
            }
        }

        // Check for empty
        if ($isEmpty) {
            return !is_null($content) ? $content : '';
        }

        // Get GEO info
        $geo = $this->geoIP();

        if (isset($geo['error'])) {
            return !is_null($content) ? $content : '';
        }

        /*
        (
            [ip] => 104.219.249.235
            [type] => ipv4
            [continent_code] => NA
            [continent_name] => North America
            [country_code] => US
            [country_name] => United States
            [region_code] => CA
            [region_name] => California
            [city] => Los Angeles
            [zip] => 90064
            [latitude] => 34.037078857422
            [longitude] => -118.42788696289
            [location] => stdClass Object
                (
                    [geoname_id] => 5368361
                    [capital] => Washington D.C.
                    [languages] => Array
                        (
                            [0] => stdClass Object
                                (
                                    [code] => en
                                    [name] => English
                                    [native] => English
                                )

                        )

                    [country_flag] => https://assets.ipstack.com/flags/us.svg
                    [country_flag_emoji] => 🇺🇸
                    [country_flag_emoji_unicode] => U+1F1FA U+1F1F8
                    [calling_code] => 1
                    [is_eu] =>
                )

        )
        */

        /*
         * {
         *   "ip":"80.181.80.86",
         *   "country_code":"it",
         *   "country_name":"italy",
         *   "region_code":"62",
         *   "region_name":"latium",
         *   "city":"rome",
         *   "zip_code":"00199",
         *   "time_zone":"europe\/rome",
         *   "latitude":"41.8919","longitude":"12.5113",
         *   "metro_code":"0"
         * }
         */

        // Turn all geo info in lowercase
        $geo = array_map(
            function ($value) {
                return is_string($value) ? strtolower($value) : $value;
            },
            $geo
        );

        // Turn all args in lowercase
        $atts = array_map(
            function ($value) {
                return strtolower($value);
            },
            $atts
        );

        $found = false;
        foreach (array_keys($defaults) as $key) {
            $array = explode(',', $atts[$key]);
            if (in_array($geo[$key], $array)) {
                $found = true;
                break;
            }
        }

        // Check pass
        if ($found) {
            return !is_null($content) ? do_shortcode($content) : '';
        }

        return '';
    }

    /**
     * Return an array with reverse geocoding information.
     *
     * @param array $geo Optional. Array returned by `self::geoIP()` method. If empty `self::geoIP()` without ip is
     *                   called.
     *
     * @return array
     */
    protected function reverseGeocoding($geo = [])
    {
        // Sanitize
        $geo = empty($geo) ? $this->geoIP() : $geo;

        if (isset($geo['error'])) {
            return [];
        }

        return $this->reverseGeocodingWithLatLng($geo['latitude'], $geo['longitude']);
    }

    /**
     * Return an array with reverse geocoding information.
     *
     * @param float $lat Latitude value.
     * @param float $lng Longitude value.
     *
     * @return array
     */
    protected function reverseGeocodingWithLatLng($lat, $lng)
    {

        // Build the endpoint
        $endpoint = sprintf('%s%s,%s', self::GOOGLE_REVERSE_GEOCODING, $lat, $lng);

        $response = wp_remote_get($endpoint);

        // Dead connection
        if (200 != wp_remote_retrieve_response_code($response)) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);

        $result = (array)json_decode($body);

        return $result['results'];
    }

    /**
     * Return the route.
     *
     * @param array $reverse_geocoding The array with reverse geocoding information retuned by
     *                                 `reverseGeocodingWithLatLng()`
     *
     * @return string
     * @todo change name
     *
     */
    protected function route($reverse_geocoding)
    {
        return $this->getWithType($reverse_geocoding, 'route');
    }

    /**
     * Return a single property/type.
     *
     * @brief  Get property with type.
     * @access private
     *
     * @param array $reverse_geocoding The array with reverse geocoding information returned by
     *                                  `reverseGeocodingWithLatLng()`
     * @param string $type The type.
     * @param string $property Optional. Default 'long_name'
     *
     * @return mixed
     */
    private function getWithType($reverse_geocoding, $type, $property = 'long_name')
    {
        foreach ($reverse_geocoding as $object) {

            foreach ($object->address_components as $address_components) {
                if (in_array($type, $address_components->types)) {
                    return $address_components->$property;
                }
            }
        }
        return '';
    }

    /**
     * Return the street number.
     *
     * @param array $reverse_geocoding The array with reverse geocoding information returned by
     *                                 `reverseGeocodingWithLatLng()`
     *
     * @return string
     * @todo change name
     *
     */
    protected function street_number($reverse_geocoding)
    {
        return $this->getWithType($reverse_geocoding, 'street_number');
    }

}
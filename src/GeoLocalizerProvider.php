<?php

namespace WPKirk\GeoLocalizer;

class GeoLocalizerProvider
{
  /**
   * Used to get geocoding information by IP address
   */
  const GEOIP_ENDPOINT = 'http://freegeoip.net/';

  /**
   * Google Maps - used for reverse geocoding
   */
  const GOOGLE_REVERSE_GEOCODING = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=';

  /**
   * Outut format: json, xml, csv
   *
   * @var string
   */
  protected $format = 'json';

  public static function __callStatic( $name, $arguments )
  {
    $method = "callable" . ucfirst( $name );

    $instance = new self;

    if ( method_exists( $instance, $method ) ) {
      return call_user_func_array( [ $instance, $method ], $arguments );
    }

    return $instance;
  }

  protected function callableHasCountries( $countries = [] )
  {
    if ( ! empty( $countries ) ) {
      // Get GEO info
      $geo = $this->geoIP();

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
        function ( $value ) {
          return strtolower( $value );
        },
        $geo
      );

      // Turn all $countries info in lowercase
      $countries = array_map(
        function ( $value ) {
          return strtolower( $value );
        },
        $countries
      );

      return in_array( $geo[ 'country_name' ], $countries );
    }

    return false;
  }

  protected function callableCountries()
  {
    global $wpdb;

    $tablename = $wpdb->prefix . "countries";

    $result = $wpdb->get_results( "SELECT country FROM {$tablename} ORDER BY country" );

    return $result;

  }

  /**
   * Display a content of shortcode only if the user geo localization info rispect the shortcodes params.
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
   * @param array  $atts    Attribute into the shortcode
   * @param string $content Optional. $content HTML content
   *
   * @return bool|string
   */
  protected function callableShortcode( $atts, $content = null )
  {
    // Defaults
    $defaults = [
      'ip'           => '',
      'country_code' => '',
      'country_name' => '',
      'region_code'  => '',
      'region_name'  => '',
      'city'         => '',
      'zip_code'     => '',
      'time_zone'    => '',
    ];

    // Merge with shortcode.
    $atts = shortcode_atts( $defaults, $atts, 'wp_geolocalizer' );

    $isEmpty = true;

    foreach ( array_keys( $defaults ) as $key ) {
      if ( ! empty( $atts[ $key ] ) ) {
        $isEmpty = false;
        break;
      }
    }

    // Check for empty
    if ( $isEmpty ) {
      return ! is_null( $content ) ? $content : '';
    }

    // Get GEO info
    $geo = $this->geoIP();

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
      function ( $value ) {
        return strtolower( $value );
      },
      $geo
    );

    // Turn all args in lowercase
    $atts = array_map(
      function ( $value ) {
        return strtolower( $value );
      },
      $atts
    );

    $found = false;
    foreach ( array_keys( $defaults ) as $key ) {
      $array = explode( ',', $atts[ $key ] );
      if ( in_array( $geo[ $key ], $array ) ) {
        $found = true;
        break;
      }
    }

    // Check pass
    if ( $found ) {
      return ! is_null( $content ) ? $content : '';
    }
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
  protected function geoIP( $ip = '' )
  {
    // Get current ip
    $ip = empty( $ip ) ? $_SERVER[ 'REMOTE_ADDR' ] : $ip;

    // Build endpoint API
    $endpoint = self::GEOIP_ENDPOINT . $this->format . '/' . $ip;

    $response = wp_remote_get( $endpoint );

    // Dead connection
    if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
      return false;
    }

    $body = wp_remote_retrieve_body( $response );

    return (array) json_decode( $body );
  }

  /**
   * Return an array with reverse geocoding information.
   *
   * @param array $geo Optional. Array retuned by `self::geoIP()` method. If empty `self::geoIP()` without ip is called.
   *
   * @return array
   */
  protected function reverseGeocoding( $geo = [] )
  {
    // Sanitize
    $geo = empty( $geo ) ? $this->geoIP() : $geo;

    return $this->reverseGeocodingWithLatLng( $geo[ 'latitude' ], $geo[ 'longitude' ] );
  }

  /**
   * Return an array with reverse geocoding information.
   *
   * @param float $lat Latitude value.
   * @param float $lng Longitude value.
   *
   * @return array
   */
  protected function reverseGeocodingWithLatLng( $lat, $lng )
  {

    // Build the endpoit
    $endpoint = sprintf( '%s%s,%s', self::GOOGLE_REVERSE_GEOCODING, $lat, $lng );

    $response = wp_remote_get( $endpoint );

    // Dead connection
    if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
      return [];
    }

    $body = wp_remote_retrieve_body( $response );

    $result = (array) json_decode( $body );

    return $result[ 'results' ];
  }

  /**
   * Return the route.
   *
   * @todo change name
   *
   * @param array $reverse_geocoding The array with reverse geocoding information retuned by
   *                                 `reverseGeocodingWithLatLng()`
   *
   * @return string
   */
  protected function route( $reverse_geocoding )
  {
    return $this->getWithType( $reverse_geocoding, 'route' );
  }

  /**
   * Return the street number.
   *
   * @todo change name
   *
   * @param array $reverse_geocoding The array with reverse geocoding information retuned by
   *                                 `reverseGeocodingWithLatLng()`
   *
   * @return string
   */
  protected function street_number( $reverse_geocoding )
  {
    return $this->getWithType( $reverse_geocoding, 'street_number' );
  }

  /**
   * Return a single property/type.
   *
   * @brief  Get property with type.
   * @access private
   *
   * @param array  $reverse_geocoding The array with reverse geocoding information retuned by
   *                                  `reverseGeocodingWithLatLng()`
   * @param string $type              The type.
   * @param string $property          Optional. Default 'long_name'
   *
   * @return mixed
   */
  private function getWithType( $reverse_geocoding, $type, $property = 'long_name' )
  {
    foreach ( $reverse_geocoding as $object ) {

      foreach ( $object->address_components as $address_components ) {
        if ( in_array( $type, $address_components->types ) ) {
          return $address_components->$property;
        }
      }
    }
  }

}
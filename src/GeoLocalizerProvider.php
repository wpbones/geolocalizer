<?php

namespace WPBannerize\GeoLocalizer;

class GeoLocalizerProvider
{
  /**
   * TELIZE end point api http://www.telize.com/
   * Used to get geocoding information by IP address
   */
  const TELIZE_END_POINT = 'http://www.telize.com/geoip/';

  /**
   * Google Maps - used for reverse geocoding
   */
  const GOOGLE_REVERSE_GEOCODIND = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=';

  public static function __callStatic( $name, $arguments )
  {
    $method = "callable" . ucfirst( $name );

    $instance = new self;

    if ( method_exists( $instance, $method ) ) {
      return call_user_func_array( [ $instance, $method ], $arguments );
    }

    return $instance;
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
   *     [wp_geolocalizer region="lazio"]
   *       Only for region (Italy) Lazio
   *     [/wp_geolocalizer]
   *
   *     [wp_geolocalizer country_code="IT"]
   *       Italian only
   *     [/wp_geolocalizer]
   *
   *     [wp_geolocalizer country="italy"]
   *       Italian only
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
      'city'         => '',
      'region'       => '',
      'country_code' => '',
      'country'      => '',
    ];

    // Merge with shortcode.
    $args = shortcode_atts( $defaults, $atts, 'wp_geolocalizer' );

    // Check for empty
    if ( empty( $args[ 'city' ] ) && empty( $args[ 'region' ] ) && empty( $args[ 'country_code' ] ) && empty( $args[ 'country' ] ) ) {
      return ! is_null( $content ) ? $content : '';
    }

    // Get GEO info
    $geo = $this->geoIP();

    // Turn all geo info in lowercase
    $geo = array_map( create_function( '$a', 'return strtolower($a);' ), $geo );

    // Turn all args in lowercase
    $args = array_map( create_function( '$a', 'return strtolower($a);' ), $args );

    // Sanitize
    $cities        = explode( ',', $args[ 'city' ] );
    $regions       = explode( ',', $args[ 'region' ] );
    $country_codes = explode( ',', $args[ 'country_code' ] );
    $countries     = explode( ',', $args[ 'country' ] );

    // Flags in OR
    $city_bool         = in_array( $geo[ 'city' ], $cities );
    $region_bool       = in_array( $geo[ 'region' ], $regions );
    $country_code_bool = in_array( $geo[ 'country_code' ], $country_codes );
    $country_bool      = in_array( $geo[ 'country' ], $countries );

    // Check pass
    if ( $city_bool || $region_bool || $country_code_bool || $country_bool ) {
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
    $endpoint = self::TELIZE_END_POINT . $ip;

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
    $endpoint = sprintf( '%s%s,%s', self::GOOGLE_REVERSE_GEOCODIND, $lat, $lng );

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
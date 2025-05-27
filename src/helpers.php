<?php

if (!function_exists('wpbones_geo')) {

  /**
   * Helper function to return the GeolocalizerProvider instance.
   *
   * @example
   *
   * wpbones_geo()->;
   *
   *
   * @return WPKirk\GeoLocalizer\GeoLocalizerProvider
   */
  function wpbones_geo()
  {
    return new \WPKirk\GeoLocalizer\GeoLocalizerProvider();
  }
}

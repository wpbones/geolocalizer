# Geo Localizer packager for WP Bones

<div align="center">

[![Latest Stable Version](https://poser.pugx.org/wpbones/geolocalizer/v/stable?style=for-the-badge)](https://packagist.org/packages/wpbones/geolocalizer) &nbsp;
[![Latest Unstable Version](https://poser.pugx.org/wpbones/geolocalizer/v/unstable?style=for-the-badge)](https://packagist.org/packages/wpbones/geolocalizer) &nbsp;
[![Total Downloads](https://poser.pugx.org/wpbones/geolocalizer/downloads?style=for-the-badge)](https://packagist.org/packages/wpbones/geolocalizer) &nbsp;
[![License](https://poser.pugx.org/wpbones/geolocalizer/license?style=for-the-badge)](https://packagist.org/packages/wpbones/geolocalizer) &nbsp;
[![Monthly Downloads](https://poser.pugx.org/wpbones/geolocalizer/d/monthly?style=for-the-badge)](https://packagist.org/packages/wpbones/geolocalizer)

</div>

Geo Localizer provides a set of utilities to manage geolocation for WordPress/WP Bones

## Requirements

This package works with a WordPress plugin written with [WP Bones framework library](https://github.com/wpbones/WPBones).

## Installation

You can install third party packages by using:

```sh copy
php bones require wpbones/geolocalizer
```

I advise to use this command instead of `composer require` because doing this an automatic renaming will done.

You can use composer to install this package:

```sh copy
composer require wpbones/geolocalizer
```

You may also to add `"wpbones/geolocalizer": "^1.0"` in the `composer.json` file of your plugin:

```json copy filename="composer.json" {4}
  "require": {
    "php": ">=7.4",
    "wpbones/wpbones": "~0.8",
    "wpbones/geolocalizer": "~1.0"
  },
```

and run

```sh copy
composer install
```

Alternatively, you can get the single files `src/resources/assets/js/actions-and-filters.js` in your WP Bones plugin and compile it with `gulp`.
Also, you can get pre-compiled minified version `src/public/js/actions-and-filters.min.js`.

## Migration

In the `database/migrations` you'll find the default migration database table used for the countries.
Also. in the `database/seeders` you'll find the data for countries database table.

Anyway, you just copy these folders in your plugin `database/` folder.

## Geo services

This version is using the [IPStack](https://ipstack.com/) service to get the country code and the rest of the data.
You have to create an account in IPStack and get your API key.
In your plugin you may use the API key b yusing the filter:

```php copy
add_filter('wpbones_geolocalizer_ipstack_api_key', function () {
    // get your api key rom your settings
    // for example, MyPlugin::$plugin->options->get('General/ipstack_api_key');
    return $your_api_key;
});
```

## Testing

In order to check if your API key is valid you can use the following command:

```php copy
$info = MyPlugin\GeoLocalizer\GeoLocalizerProvider::geoIP();
```

You should receive all information starting from your IP address. Otherwise, you'll receive an error from IPStack service.

## Shortcode

Geolocalizer provides a shortcode method. You can define you own shortcode in the your shortcode provider class:

```php copy
use WPMyPlugin\WPBones\Foundation\WordPressShortcodesServiceProvider as ServiceProvider;
use WPMyPlugin\GeoLocalizer\GeoLocalizerProvider;

class WPMyPluginShortcode extends ServiceProvider
{

  /**
   * List of registred shortcodes. {shortcode}/method
   *
   * @var array
   */
  protected $shortcodes = [
    'my_shortocde_geo' => 'my_shortocde_geo',
  ];


  public function my_shortocde_geo( $atts = [], $content = null )
  {
    return GeoLocalizerProvider::shortcode( $atts, $content );
  }

```

The you can use:

```txt copy
[my_shortocde_geo city="Rome"]
  Only for Rome
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo city="rome"]
  Only for Rome
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo city="rome,london"]
  Only for Rome and Landon
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo region_name="lazio"]
  Only for region (Italy) Lazio
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo country_code="IT"]
  Italian only
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo country_name="italy"]
  Italian only
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo zip_code="00137"]
  Wow
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo ip="80.182.82.82"]
  Only for me
[/my_shortocde_geo]
```

```txt copy
[my_shortocde_geo time_zone="europe\rome"]
  Rome/Berlin time zone
[/my_shortocde_geo]
```

# Geo Localizer packager for WP Bones

Geo Localizer provides a set of utilies to manage the geolocalization for WordPress/WP Bones

[![Latest Stable Version](https://poser.pugx.org/wpbones/geolocalizer/v/stable)](https://packagist.org/packages/wpbones/geolocalizer)
[![Total Downloads](https://poser.pugx.org/wpbones/geolocalizer/downloads)](https://packagist.org/packages/wpbones/geolocalizer)
[![License](https://poser.pugx.org/wpbones/geolocalizer/license)](https://packagist.org/packages/wpbones/geolocalizer)

## Installation

You can install third party packages by using:

    $ php bones require wpbones/geolocalizer
   
I advise to use this command instead of `composer require` because doing this an automatic renaming will done.  

You can use composer to install this package:

    $ composer require wpbones/geolocalizer

You may also to add `"wpbones/geolocalizer": "~0.5"` in the `composer.json` file of your plugin:
 
```json
  "require": {
    "php": ">=5.5.9",
    "wpbones/wpbones": "~0.8",
    "wpbones/geolocalizer": "~0.5"
  },
```


and run 

    $ composer install
    
## Migration

In the `database/migrations` you'll find the default migration database table used for the countries.
Also. in the `database/seeds` you'll find the data for countries database table.

Anyway, you just copy these folders in your plugin `database/` folder.

## Shortcode

Geolocalizer provides a shortcode method. You can define you own shortocde in the your shortcode provider class:
 
```php
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

``` 
[my_shortocde_geo city="Rome"]
  Only for Rome
[/my_shortocde_geo]

[my_shortocde_geo city="rome"]
  Only for Rome
[/my_shortocde_geo]

[my_shortocde_geo city="rome,london"]
  Only for Rome and Landon
[/my_shortocde_geo]

[my_shortocde_geo region_name="lazio"]
  Only for region (Italy) Lazio
[/my_shortocde_geo]

[my_shortocde_geo country_code="IT"]
  Italian only
[/my_shortocde_geo]

[my_shortocde_geo country_name="italy"]
  Italian only
[/my_shortocde_geo]

[my_shortocde_geo zip_code="00137"]
  Wow
[/my_shortocde_geo]

[my_shortocde_geo ip="80.182.82.82"]
  Only for me
[/my_shortocde_geo]

[my_shortocde_geo time_zone="europe\rome"]
  Rome/Berlin time zone
[/my_shortocde_geo]
```
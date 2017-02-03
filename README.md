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
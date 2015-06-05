# PHP OAuth 2.0 Server for Lumen

[OAuth 2.0](http://tools.ietf.org/wg/oauth/draft-ietf-oauth-v2/) authorization server and resource server for the Laravel framework. 
Standard compliant thanks to the amazing work by [The League of Extraordinary Packages](http://www.thephpleague.com) OAuth 2.0 authorization server and resource server.

The package assumes you have a good-enough knowledge of the principles behind the [OAuth 2.0 Specification](http://tools.ietf.org/html/rfc6749).

## Version Compability

 Lumen    | OAuth Server | PHP
:---------|:-------------|:----
 5.0.x    | 4.1.x        |>= 5.5

## Documentation

This package features an [extensive wiki](https://github.com/amaroRafael/oauth2server-lumen/wiki) to help you getting started implementing an OAuth 2.0 Server in your Laravel app.

## Support

Bugs and feature request are tracked on [GitHub](https://github.com/amaroRafael/oauth2server-lumen/issues)

## License

This package is released under [the MIT License](LICENSE).

## Credits

#The code on which this package are based:

 - [OAuth2 server](https://github.com/php-loep/oauth2-server/), is principally developed and maintained by [Alex Bilbie](https://twitter.com/alexbilbie).
 - [Oauth2 server Laravel](https://github.com/lucadegasperi/oauth2-server-laravel), is principally developed and maintained by [Luca Degasperi](http://www.lucadegasperi.com).

### OAuth2Server-Lumen

PHP OAuth 2.0 Server for Lumen

## Installation

### Via composer

Run ```composer require 'rapiro/oauth2server-lumen:0.1.*'```

### Register package

In your ```bootstrap/app.php``` register service providers

```
$app->register('Rapiro\OAuth2Server\Providers\StorageServiceProvider');
$app->register('Rapiro\OAuth2Server\Providers\OAuth2ServerServiceProvider');
```

... and middleware

```
$app->middleware([
    'Rapiro\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware'
]);
```

... and route middleware

```
$app->routeMiddleware([
    'check-authorization-params' => 'Rapiro\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware',
    'csrf' => 'Laravel\Lumen\Http\Middleware\VerifyCsrfToken',
    'oauth' => 'Rapiro\OAuth2Server\Middleware\OAuthMiddleware',
    'oauth-owner' => 'Rapiro\OAuth2Server\Middleware\OAuthOwnerMiddleware'
]);
```

### Copy config

Copy ```vendor/Rapiro/oauth2server-lumen/config/oauth2.php``` to your own config folder (```config/oauth2.php``` in your project root).
Copy ```vendor/Rapiro/oauth2server-lumen/config/auth.php``` to your own config folder (```config/oauth2.php``` in your project root).

It has to be the correct config folder as it is registered using ```$app->configure()```.

### Copy models

Copy ```vendor/Rapiro/oauth2server-lumen/Models/``` folder to your own app folder (```app/``` in your project root).

### Migrate

In ```bootstrap/app.php``` file and uncomment ```$app->withFacades();``` and ```$app->withEloquent();```

Run ```php artisan migrate --path=vendor/Rapiro/oauth2server-lumen/database/migrations```

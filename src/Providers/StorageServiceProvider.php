<?php namespace Rapiro\OAuth2Server\Providers;
/**
 * Fluent Storage Service Provider for the OAuth 2.0 Server
 *
 */

use Rapiro\OAuth2Server\Storage\AccessTokenStorage;
use Rapiro\OAuth2Server\Storage\AuthCodeStorage;
use Rapiro\OAuth2Server\Storage\ClientStorage;
use Rapiro\OAuth2Server\Storage\RefreshTokenStorage;
use Rapiro\OAuth2Server\Storage\ScopeStorage;
use Rapiro\OAuth2Server\Storage\SessionStorage;
use Illuminate\Support\ServiceProvider;

final class StorageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorageBindings();
        $this->registerInterfaceBindings();
    }

    /**
     * Bind the storage implementations to the IoC container
     * @return void
     */
    public function registerStorageBindings()
    {
        $provider = $this;

        $this->app->bindShared('Rapiro\OAuth2Server\Storage\AccessTokenStorage', function () use ($provider) {
            $storage = new AccessTokenStorage($provider->app['db']);
            return $storage;
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Storage\AuthCodeStorage', function () use ($provider) {
            $storage = new AuthCodeStorage($provider->app['db']);
            return $storage;
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Storage\ClientStorage', function ($app) use ($provider) {
            $limitClientsToGrants = $app['config']->get('oauth2.limit_clients_to_grants');
            $storage = new ClientStorage($provider->app['db'], $limitClientsToGrants);
            return $storage;
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Storage\RefreshTokenStorage', function () use ($provider) {
            $storage = new RefreshTokenStorage($provider->app['db']);
            return $storage;
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Storage\ScopeStorage', function ($app) use ($provider) {
            $limitClientsToScopes = $app['config']->get('oauth2.limit_clients_to_scopes');
            $limitScopesToGrants = $app['config']->get('oauth2.limit_scopes_to_grants');
            $storage = new ScopeStorage($provider->app['db'], $limitClientsToScopes, $limitScopesToGrants);
            return $storage;
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Storage\SessionStorage', function () use ($provider) {
            $storage = new SessionStorage($provider->app['db']);
            return $storage;
        });
    }

    /**
     * Bind the interfaces to their implementations
     * @return void
     */
    public function registerInterfaceBindings()
    {
        $this->app->bind('League\OAuth2\Server\Storage\ClientInterface',       'Rapiro\OAuth2Server\Storage\ClientStorage');
        $this->app->bind('League\OAuth2\Server\Storage\ScopeInterface',        'Rapiro\OAuth2Server\Storage\ScopeStorage');
        $this->app->bind('League\OAuth2\Server\Storage\SessionInterface',      'Rapiro\OAuth2Server\Storage\SessionStorage');
        $this->app->bind('League\OAuth2\Server\Storage\AuthCodeInterface',     'Rapiro\OAuth2Server\Storage\AuthCodeStorage');
        $this->app->bind('League\OAuth2\Server\Storage\AccessTokenInterface',  'Rapiro\OAuth2Server\Storage\AccessTokenStorage');
        $this->app->bind('League\OAuth2\Server\Storage\RefreshTokenInterface', 'Rapiro\OAuth2Server\Storage\RefreshTokenStorage');
    }
}
 

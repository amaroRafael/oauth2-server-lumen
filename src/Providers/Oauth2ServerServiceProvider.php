<?php  namespace Rapiro\OAuth2Server\Providers;
/**
 * Created by PhpStorm.
 * User: ramaro
 * Date: 6/3/15
 * Time: 10:44 PM
 */

use Rapiro\OAuth2Server\Authorizer;
use Rapiro\OAuth2Server\Storage\AccessTokenStorage;
use Rapiro\OAuth2Server\Storage\AuthCodeStorage;
use Rapiro\OAuth2Server\Storage\ClientStorage;
use Rapiro\OAuth2Server\Storage\RefreshTokenStorage;
use Rapiro\OAuth2Server\Storage\ScopeStorage;
use Rapiro\OAuth2Server\Storage\SessionStorage;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;

final class Oauth2ServerServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->registerConfiguration();
        $this->registerAuthorizer();
        $this->registerFilterBindings();
    }

    public function registerConfiguration() {
        $this->app->configure("oauth2");
        $this->app->configure("auth");
    }

    /**
     * Register the Authorization server with the IoC container
     * @return void
     */
    public function registerAuthorizer()
    {
        $this->app->bindShared('oauth2-server.authorizer', function ($app) {

            $config = $app['config']->get('oauth2');

            $limitClientsToGrants = $config['limit_clients_to_grants'];
            $limitClientsToScopes = $config['limit_clients_to_scopes'];

            // Authorization server
            $issuer = new AuthorizationServer();
            $issuer->setSessionStorage(new SessionStorage($app['db']));
            $issuer->setAccessTokenStorage(new AccessTokenStorage($app['db']));
            $issuer->setRefreshTokenStorage(new RefreshTokenStorage($app['db']));
            $issuer->setClientStorage(new ClientStorage($app['db'], $limitClientsToGrants));
            $issuer->setScopeStorage(new ScopeStorage($app['db'], $limitClientsToScopes, $limitClientsToGrants));
            $issuer->setAuthCodeStorage(new AuthCodeStorage($app['db']));
            $issuer->requireScopeParam($config['scope_param']);
            $issuer->setDefaultScope($config['default_scope']);
            $issuer->requireStateParam($config['state_param']);
            $issuer->setScopeDelimiter($config['scope_delimiter']);
            $issuer->setAccessTokenTTL($config['access_token_ttl']);

            // add the supported grant types to the authorization server
            foreach ($config['grant_types'] as $grantIdentifier => $grantParams) {
                $grant = new $grantParams['class'];
                $grant->setAccessTokenTTL($grantParams['access_token_ttl']);

                if (array_key_exists('callback', $grantParams)) {
                    $grant->setVerifyCredentialsCallback($grantParams['callback']);
                }
                if (array_key_exists('auth_token_ttl', $grantParams)) {
                    $grant->setAuthTokenTTL($grantParams['auth_token_ttl']);
                }
                if (array_key_exists('refresh_token_ttl', $grantParams)) {
                    $grant->setRefreshTokenTTL($grantParams['refresh_token_ttl']);
                }
                $issuer->addGrantType($grant);
            }

            // Resource server
            $sessionStorage = new SessionStorage($app['db']);
            $accessTokenStorage = new AccessTokenStorage($app['db']);
            $clientStorage = new ClientStorage($app['db'], $limitClientsToGrants);
            $scopeStorage = new ScopeStorage($app['db'], $limitClientsToScopes, $limitClientsToGrants);

            $checker = new ResourceServer(
                $sessionStorage,
                $accessTokenStorage,
                $clientStorage,
                $scopeStorage
            );

            $authorizer = new Authorizer($issuer, $checker);
            $authorizer->setRequest($app['request']);
            $authorizer->setTokenType($app->make($config['token_type']));

            $app->refresh('request', $authorizer, 'setRequest');

            return $authorizer;
        });

        $this->app->bind('Rapiro\OAuth2Server\Authorizer', function($app)
        {
            return $app['oauth2-server.authorizer'];
        });
    }

    /**
     * Register the Filters to the IoC container because some filters need additional parameters
     * @return void
     */
    public function registerFilterBindings()
    {
        $this->app->bindShared('Rapiro\OAuth2Server\Filters\CheckAuthCodeRequestFilter', function ($app) {
            return new CheckAuthCodeRequestFilter($app['oauth2-server.authorizer']);
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Filters\OAuthFilter', function ($app) {
            $httpHeadersOnly = $app['config']->get('oauth2.http_headers_only');
            return new OAuthFilter($app['oauth2-server.authorizer'], $httpHeadersOnly);
        });

        $this->app->bindShared('Rapiro\OAuth2Server\Filters\OAuthOwnerFilter', function ($app) {
            return new OAuthOwnerFilter($app['oauth2-server.authorizer']);
        });
    }

    /**
     * Get the services provided by the provider.
     * @return string[]
     * @codeCoverageIgnore
     */
    public function provides()
    {
        return ['oauth2-server.authorizer'];
    }
}
<?php namespace Rapiro\OAuth2Server\Middleware;

use Closure;
use Rapiro\OAuth2Server\Filters\OAuthFilter;

final class OAuthMiddleware extends OAuthFilter {

    public function handle($request, Closure $next)
    {
        // Will throw exception on failure
        parent::filter();

        return $next($request);
    }
}
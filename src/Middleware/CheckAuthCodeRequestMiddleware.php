<?php namespace Rapiro\OAuth2Server\Middleware;

use Closure;
use Rapiro\OAuth2Server\Filters\CheckAuthCodeRequestFilter;

class CheckAuthCodeRequestMiddleware extends CheckAuthCodeRequestFilter {

    public function handle($request, Closure $next)
    {
        // Will throw exception on failure
        parent::filter();

        return $next($request);
    }
}
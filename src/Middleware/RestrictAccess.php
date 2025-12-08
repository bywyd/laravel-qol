<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if maintenance mode is enabled
        if (config('laravel-qol.access_restriction.enabled', false)) {
            // Allow specific IPs
            if ($this->isAllowedIp($request)) {
                return $next($request);
            }

            // Allow authenticated users with specific roles
            if ($this->isAllowedUser($request)) {
                return $next($request);
            }

            // Allow if user has bypass token
            if ($this->hasValidBypassToken($request)) {
                return $next($request);
            }

            return $this->denyAccess($request);
        }

        return $next($request);
    }

    /**
     * Check if the request IP is allowed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isAllowedIp(Request $request): bool
    {
        $allowedIps = config('laravel-qol.access_restriction.allowed_ips', []);
        
        if (empty($allowedIps)) {
            return false;
        }

        $requestIp = $request->ip();

        foreach ($allowedIps as $ip) {
            if ($this->ipMatches($requestIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP matches pattern (supports wildcards and CIDR).
     *
     * @param  string  $requestIp
     * @param  string  $pattern
     * @return bool
     */
    protected function ipMatches(string $requestIp, string $pattern): bool
    {
        // Exact match
        if ($requestIp === $pattern) {
            return true;
        }

        // Wildcard match (e.g., 192.168.*.*)
        if (strpos($pattern, '*') !== false) {
            $regex = '/^' . str_replace(['.', '*'], ['\.', '.*'], $pattern) . '$/';
            return (bool) preg_match($regex, $requestIp);
        }

        // CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($pattern, '/') !== false) {
            list($subnet, $mask) = explode('/', $pattern);
            return (ip2long($requestIp) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
        }

        return false;
    }

    /**
     * Check if the authenticated user is allowed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isAllowedUser(Request $request): bool
    {
        if (!$request->user()) {
            return false;
        }

        $allowedRoles = config('laravel-qol.access_restriction.allowed_roles', []);
        
        if (empty($allowedRoles)) {
            return false;
        }

        // Check if user has HasRoles trait
        if (!method_exists($request->user(), 'hasAnyRole')) {
            return false;
        }

        return $request->user()->hasAnyRole($allowedRoles);
    }

    /**
     * Check if request has valid bypass token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasValidBypassToken(Request $request): bool
    {
        $bypassToken = config('laravel-qol.access_restriction.bypass_token');
        
        if (!$bypassToken) {
            return false;
        }

        // Check query parameter
        if ($request->get('bypass_token') === $bypassToken) {
            return true;
        }

        // Check header
        if ($request->header('X-Bypass-Token') === $bypassToken) {
            return true;
        }

        return false;
    }

    /**
     * Deny access to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function denyAccess(Request $request): Response
    {
        $message = config('laravel-qol.access_restriction.message', 'Service temporarily unavailable.');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 503);
        }

        // Check if custom view exists
        $view = config('laravel-qol.access_restriction.view', 'laravel-qol::maintenance');
        
        if (view()->exists($view)) {
            return response()->view($view, ['message' => $message], 503);
        }

        return response($message, 503);
    }
}

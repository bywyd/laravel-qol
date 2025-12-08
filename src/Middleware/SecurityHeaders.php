<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
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
        $response = $next($request);

        $headers = config('laravel-qol.security_headers', [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        ]);

        foreach ($headers as $key => $value) {
            if ($value !== null && $value !== false) {
                $response->headers->set($key, $value);
            }
        }

        // Add HSTS header for HTTPS requests
        if ($request->secure() && config('laravel-qol.security_headers.enable_hsts', true)) {
            $maxAge = config('laravel-qol.security_headers.hsts_max_age', 31536000);
            $response->headers->set(
                'Strict-Transport-Security',
                "max-age={$maxAge}; includeSubDomains; preload"
            );
        }

        // Content Security Policy
        if ($csp = config('laravel-qol.security_headers.content_security_policy')) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}

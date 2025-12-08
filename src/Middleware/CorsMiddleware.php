<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
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
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);

        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Handle preflight request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function handlePreflightRequest(Request $request)
    {
        $response = response('', 200);
        
        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Add CORS headers to response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return mixed
     */
    protected function addCorsHeaders(Request $request, $response)
    {
        $origin = $request->header('Origin');
        
        // Allowed origins
        $allowedOrigins = config('laravel-qol.cors.allowed_origins', ['*']);
        
        if ($this->isOriginAllowed($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin ?: '*');
        } elseif (in_array('*', $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        // Allowed methods
        $allowedMethods = config('laravel-qol.cors.allowed_methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $allowedMethods));

        // Allowed headers
        $allowedHeaders = config('laravel-qol.cors.allowed_headers', ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept']);
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));

        // Exposed headers
        $exposedHeaders = config('laravel-qol.cors.exposed_headers', []);
        if (!empty($exposedHeaders)) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $exposedHeaders));
        }

        // Max age
        $maxAge = config('laravel-qol.cors.max_age', 3600);
        $response->headers->set('Access-Control-Max-Age', $maxAge);

        // Allow credentials
        if (config('laravel-qol.cors.allow_credentials', false)) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    /**
     * Check if origin is allowed.
     *
     * @param  string|null  $origin
     * @param  array  $allowedOrigins
     * @return bool
     */
    protected function isOriginAllowed(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }

        foreach ($allowedOrigins as $allowed) {
            if ($allowed === '*') {
                return true;
            }

            // Exact match
            if ($origin === $allowed) {
                return true;
            }

            // Wildcard subdomain match (e.g., *.example.com)
            if (strpos($allowed, '*.') === 0) {
                $pattern = '/^https?:\/\/' . str_replace('*.', '.*\.', preg_quote(substr($allowed, 2), '/')) . '$/';
                if (preg_match($pattern, $origin)) {
                    return true;
                }
            }
        }

        return false;
    }
}

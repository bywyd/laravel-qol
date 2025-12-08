<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
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
        // Force Accept header to application/json
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Ensure response is JSON
        if (!$response->headers->has('Content-Type') || 
            strpos($response->headers->get('Content-Type'), 'application/json') === false) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}

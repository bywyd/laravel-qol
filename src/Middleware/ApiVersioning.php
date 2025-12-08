<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersioning
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $version
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $version = null)
    {
        $requestedVersion = $this->getRequestedVersion($request);

        // Set the API version on the request
        $request->attributes->set('api_version', $requestedVersion);

        // Validate version if specified
        if ($version && $requestedVersion !== $version) {
            return response()->json([
                'message' => 'API version mismatch',
                'required' => $version,
                'provided' => $requestedVersion,
            ], 400);
        }

        // Check if version is supported
        if (!$this->isVersionSupported($requestedVersion)) {
            return response()->json([
                'message' => 'Unsupported API version',
                'version' => $requestedVersion,
                'supported_versions' => config('laravel-qol.api_versioning.supported_versions', ['v1']),
            ], 400);
        }

        return $next($request);
    }

    /**
     * Get the requested API version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getRequestedVersion(Request $request): string
    {
        // Priority 1: Header (Accept: application/vnd.api.v1+json)
        if ($accept = $request->header('Accept')) {
            if (preg_match('/application\/vnd\.api\.(v\d+)\+json/', $accept, $matches)) {
                return $matches[1];
            }
        }

        // Priority 2: Custom header (X-API-Version)
        if ($version = $request->header('X-API-Version')) {
            return $version;
        }

        // Priority 3: Query parameter
        if ($version = $request->query('version')) {
            return $version;
        }

        // Priority 4: URL segment (e.g., /api/v1/users)
        if (preg_match('/\/(v\d+)\//', $request->path(), $matches)) {
            return $matches[1];
        }

        // Default version
        return config('laravel-qol.api_versioning.default_version', 'v1');
    }

    /**
     * Check if the API version is supported.
     *
     * @param  string  $version
     * @return bool
     */
    protected function isVersionSupported(string $version): bool
    {
        $supportedVersions = config('laravel-qol.api_versioning.supported_versions', ['v1']);
        
        return in_array($version, $supportedVersions);
    }
}

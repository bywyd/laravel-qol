<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogRequestResponse
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
        $startTime = microtime(true);

        $this->logRequest($request);

        $response = $next($request);

        $duration = microtime(true) - $startTime;
        $this->logResponse($request, $response, $duration);

        return $response;
    }

    /**
     * Log the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logRequest(Request $request): void
    {
        if (!config('laravel-qol.logging.log_requests', true)) {
            return;
        }

        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ];

        if (config('laravel-qol.logging.log_request_body', false)) {
            $data['body'] = $this->sanitizeData($request->all());
        }

        if (config('laravel-qol.logging.log_headers', false)) {
            $data['headers'] = $this->sanitizeHeaders($request->headers->all());
        }

        \Log::channel(config('laravel-qol.logging.channel', 'stack'))
            ->info('HTTP Request', $data);
    }

    /**
     * Log the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @param  float  $duration
     * @return void
     */
    protected function logResponse(Request $request, $response, float $duration): void
    {
        if (!config('laravel-qol.logging.log_responses', true)) {
            return;
        }

        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->status(),
            'duration' => round($duration * 1000, 2) . 'ms',
        ];

        if (config('laravel-qol.logging.log_response_body', false) && method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if ($this->isJson($content)) {
                $data['body'] = json_decode($content, true);
            }
        }

        $level = $this->getLogLevel($response->status());
        
        \Log::channel(config('laravel-qol.logging.channel', 'stack'))
            ->{$level}('HTTP Response', $data);
    }

    /**
     * Sanitize sensitive data.
     *
     * @param  array  $data
     * @return array
     */
    protected function sanitizeData(array $data): array
    {
        $sensitiveKeys = config('laravel-qol.logging.sensitive_keys', [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'api_secret',
            'secret',
            'credit_card',
        ]);

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }

    /**
     * Sanitize headers.
     *
     * @param  array  $headers
     * @return array
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * Check if content is JSON.
     *
     * @param  string  $content
     * @return bool
     */
    protected function isJson(string $content): bool
    {
        json_decode($content);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Get log level based on status code.
     *
     * @param  int  $status
     * @return string
     */
    protected function getLogLevel(int $status): string
    {
        if ($status >= 500) {
            return 'error';
        }

        if ($status >= 400) {
            return 'warning';
        }

        return 'info';
    }
}

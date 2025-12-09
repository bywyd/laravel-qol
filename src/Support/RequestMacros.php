<?php

namespace Bywyd\LaravelQol\Support;

use Illuminate\Http\Request;

class RequestMacros
{
    public static function register(): void
    {
        Request::macro('hasAny', function (array $keys) {
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    return true;
                }
            }
            return false;
        });

        Request::macro('hasAll', function (array $keys) {
            foreach ($keys as $key) {
                if (!$this->has($key)) {
                    return false;
                }
            }
            return true;
        });

        Request::macro('boolean', function (string $key, bool $default = false) {
            $value = $this->input($key, $default);
            if (is_bool($value)) return $value;
            if (is_string($value)) {
                return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
            }
            return (bool) $value;
        });

        Request::macro('ids', function (string $key = 'ids') {
            $value = $this->input($key, []);
            if (is_string($value)) {
                $value = explode(',', $value);
            }
            return array_filter(array_map('intval', (array) $value));
        });

        Request::macro('search', function (string $key = 'search', ?string $default = null) {
            $value = $this->input($key, $default);
            if (!is_string($value)) return $default;
            return trim(preg_replace('/\s+/', ' ', $value));
        });

        Request::macro('realIp', function () {
            $headers = [
                'HTTP_CF_CONNECTING_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_REAL_IP',
                'HTTP_CLIENT_IP',
            ];
            foreach ($headers as $header) {
                if ($ip = $this->server($header)) {
                    $ip = trim(explode(',', $ip)[0]);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
            return $this->ip();
        });

        Request::macro('isMobile', function () {
            $userAgent = $this->userAgent();
            return $userAgent && preg_match('/mobile|android|iphone|ipad|phone/i', $userAgent);
        });

        Request::macro('sort', function (string $defaultColumn = 'created_at', string $defaultDirection = 'desc') {
            $sortBy = $this->input('sort_by', $defaultColumn);
            $sortDir = strtolower($this->input('sort_dir', $defaultDirection));
            $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : $defaultDirection;
            return ['column' => $sortBy, 'direction' => $sortDir];
        });

        Request::macro('filters', function (array $allowed = []) {
            $filters = $this->only($allowed);
            return array_filter($filters, function ($value) {
                if (is_null($value)) return false;
                if (is_string($value) && trim($value) === '') return false;
                return true;
            });
        });
    }
}

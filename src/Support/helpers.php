<?php

if (!function_exists('array_get_recursive')) {
    function array_get_recursive(array $array, string $key, $default = null)
    {
        return data_get($array, $key, $default);
    }
}

if (!function_exists('str_limit_words')) {
    function str_limit_words(string $string, int $words = 10, string $end = '...'): string
    {
        $wordsArray = explode(' ', $string);
        if (count($wordsArray) <= $words) {
            return $string;
        }
        return implode(' ', array_slice($wordsArray, 0, $words)) . $end;
    }
}

if (!function_exists('money_format_simple')) {
    function money_format_simple(float $amount, string $currency = '$', int $decimals = 2): string
    {
        return $currency . number_format($amount, $decimals);
    }
}

if (!function_exists('percentage')) {
    function percentage(float $value, float $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }
        return round(($value / $total) * 100, $decimals);
    }
}

if (!function_exists('array_filter_recursive')) {
    function array_filter_recursive(array $array, ?callable $callback = null): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = array_filter_recursive($value, $callback);
            }
        }
        return $callback ? array_filter($array, $callback) : array_filter($array);
    }
}

if (!function_exists('sanitize_filename')) {
    function sanitize_filename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);
        return preg_replace('/_+/', '_', $filename);
    }
}

if (!function_exists('generate_random_string')) {
    function generate_random_string(int $length = 16, bool $alphanumeric = true): string
    {
        if ($alphanumeric) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_+=';
        }
        
        $string = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, $max)];
        }
        
        return $string;
    }
}

if (!function_exists('bytes_to_human')) {
    function bytes_to_human(int $bytes, int $decimals = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $decimals) . ' ' . $units[$i];
    }
}

if (!function_exists('human_to_bytes')) {
    function human_to_bytes(string $size): int
    {
        $size = trim($size);
        $unit = strtoupper(substr($size, -2));
        $value = (int) $size;
        
        $units = ['KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824, 'TB' => 1099511627776];
        
        return isset($units[$unit]) ? $value * $units[$unit] : $value;
    }
}

if (!function_exists('is_json')) {
    function is_json(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('carbon_parse_safe')) {
    function carbon_parse_safe($date, $default = null)
    {
        try {
            return $date ? \Carbon\Carbon::parse($date) : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('active_route')) {
    function active_route(string|array $routes, string $activeClass = 'active'): string
    {
        $routes = is_array($routes) ? $routes : [$routes];
        
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return $activeClass;
            }
        }
        
        return '';
    }
}

if (!function_exists('get_client_browser')) {
    function get_client_browser(): string
    {
        $userAgent = request()->userAgent() ?? '';
        
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        
        return 'Unknown';
    }
}

if (!function_exists('truncate_middle')) {
    function truncate_middle(string $string, int $length = 50, string $separator = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        $separatorLength = strlen($separator);
        $charsToShow = $length - $separatorLength;
        $frontChars = ceil($charsToShow / 2);
        $backChars = floor($charsToShow / 2);
        
        return substr($string, 0, $frontChars) . $separator . substr($string, -$backChars);
    }
}

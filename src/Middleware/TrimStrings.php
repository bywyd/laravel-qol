<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrimStrings
{
    /**
     * The attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        $input = $this->trimInput($input);

        $request->merge($input);

        return $next($request);
    }

    /**
     * Trim input recursively.
     *
     * @param  array  $data
     * @return array
     */
    protected function trimInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->getExceptList())) {
                continue;
            }

            if (is_string($value)) {
                $data[$key] = trim($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->trimInput($value);
            }
        }

        return $data;
    }

    /**
     * Get the list of attributes that should not be trimmed.
     *
     * @return array
     */
    protected function getExceptList(): array
    {
        return array_merge(
            $this->except,
            config('laravel-qol.trim_strings.except', [])
        );
    }
}

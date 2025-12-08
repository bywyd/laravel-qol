<?php

namespace Bywyd\LaravelQol\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
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
        $locale = $this->getLocale($request);

        if ($locale && $this->isValidLocale($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getLocale(Request $request): ?string
    {
        // Priority 1: Query parameter
        if ($request->has('locale')) {
            return $request->get('locale');
        }

        // Priority 2: Session
        if ($request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        // Priority 3: Authenticated user preference
        if ($request->user() && method_exists($request->user(), 'getPreferredLocale')) {
            return $request->user()->getPreferredLocale();
        }

        // Priority 4: Cookie
        if ($request->hasCookie('locale')) {
            return $request->cookie('locale');
        }

        // Priority 5: Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            return $this->parseAcceptLanguage($acceptLanguage);
        }

        return null;
    }

    /**
     * Parse Accept-Language header.
     *
     * @param  string  $header
     * @return string|null
     */
    protected function parseAcceptLanguage(string $header): ?string
    {
        $languages = explode(',', $header);
        
        foreach ($languages as $language) {
            $locale = trim(explode(';', $language)[0]);
            $locale = str_replace('-', '_', $locale);
            
            // Try exact match first
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
            
            // Try language code only (e.g., 'en' from 'en_US')
            $shortLocale = explode('_', $locale)[0];
            if ($this->isValidLocale($shortLocale)) {
                return $shortLocale;
            }
        }

        return null;
    }

    /**
     * Check if locale is valid.
     *
     * @param  string  $locale
     * @return bool
     */
    protected function isValidLocale(string $locale): bool
    {
        $supportedLocales = config('laravel-qol.localization.supported_locales', ['en']);
        
        return in_array($locale, $supportedLocales);
    }
}

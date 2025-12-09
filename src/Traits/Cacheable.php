<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Store tracked cache keys.
     *
     * @var array
     */
    protected $trackedCacheKeys = [];

    /**
     * Clear the cache for this model.
     *
     * @return void
     */
    public function clearCache(): void
    {
        // Clear base cache keys
        Cache::forget($this->getCacheKey());
        Cache::forget($this->getCacheKey('all'));

        // Clear all tracked cache keys
        foreach ($this->trackedCacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear additional custom keys if defined
        if (method_exists($this, 'getAdditionalCacheKeys')) {
            foreach ($this->getAdditionalCacheKeys() as $key) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Get a cache key for this model.
     *
     * @param string|null $suffix
     * @return string
     */
    public function getCacheKey(?string $suffix = null): string
    {
        $prefix = $this->getCachePrefix();
        $key = $prefix . '.' . $this->getKey();

        if ($suffix) {
            $key .= '.' . $suffix;
        }

        return $key;
    }

    /**
     * Get the cache prefix.
     *
     * @return string
     */
    protected function getCachePrefix(): string
    {
        if (property_exists($this, 'cachePrefix')) {
            return $this->cachePrefix;
        }

        return strtolower(class_basename($this));
    }

    /**
     * Get the cache TTL in seconds.
     *
     * @return int
     */
    protected function getCacheTtl(): int
    {
        return property_exists($this, 'cacheTtl') ? $this->cacheTtl : 3600;
    }

    /**
     * Cache and retrieve a value.
     *
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $cacheKey = $this->getCacheKey($key);
        $ttl = $ttl ?? $this->getCacheTtl();

        // Track this cache key for later clearing
        if (!in_array($cacheKey, $this->trackedCacheKeys)) {
            $this->trackedCacheKeys[] = $cacheKey;
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache and retrieve a value forever.
     *
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function rememberForever(string $key, callable $callback)
    {
        $cacheKey = $this->getCacheKey($key);

        // Track this cache key for later clearing
        if (!in_array($cacheKey, $this->trackedCacheKeys)) {
            $this->trackedCacheKeys[] = $cacheKey;
        }

        return Cache::rememberForever($cacheKey, $callback);
    }

    /**
     * Boot the Cacheable trait.
     */
    protected static function bootCacheable(): void
    {
        static::saved(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }
}

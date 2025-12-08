<?php

namespace Bywyd\LaravelQol\Services;

use Bywyd\LaravelQol\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsManager
{
    /**
     * Get an app-wide setting value.
     *
     * @param string $key
     * @param mixed $default
     * @param string|null $group
     * @return mixed
     */
    public function get(string $key, $default = null, ?string $group = 'general')
    {
        $cacheKey = $this->getCacheKey($group, $key);

        return Cache::remember($cacheKey, config('laravel-qol.settings.cache_ttl', 3600), function () use ($key, $group, $default) {
            $setting = Setting::appWide()
                ->where('key', $key)
                ->where('group', $group)
                ->first();

            return $setting ? $setting->getCastedValue() : $default;
        });
    }

    /**
     * Set an app-wide setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param bool $isPublic
     * @param array $metadata
     * @return Setting
     */
    public function set(
        string $key,
        $value,
        ?string $group = 'general',
        bool $isPublic = false,
        array $metadata = []
    ): Setting {
        $setting = Setting::updateOrCreate(
            [
                'settable_type' => null,
                'settable_id' => null,
                'key' => $key,
                'group' => $group,
            ],
            [
                'is_public' => $isPublic,
                'metadata' => $metadata,
            ]
        );

        $setting->setCastedValue($value);
        $setting->save();

        return $setting;
    }

    /**
     * Check if an app-wide setting exists.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function has(string $key, ?string $group = 'general'): bool
    {
        return Setting::appWide()
            ->where('key', $key)
            ->where('group', $group)
            ->exists();
    }

    /**
     * Remove an app-wide setting.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function remove(string $key, ?string $group = 'general'): bool
    {
        return Setting::appWide()
            ->where('key', $key)
            ->where('group', $group)
            ->delete();
    }

    /**
     * Get all settings in a group.
     *
     * @param string $group
     * @return Collection
     */
    public function getGroup(string $group): Collection
    {
        return Setting::appWide()
            ->where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastedValue()];
            });
    }

    /**
     * Get all app-wide settings.
     *
     * @param bool $publicOnly
     * @return Collection
     */
    public function all(bool $publicOnly = false): Collection
    {
        $query = Setting::appWide();

        if ($publicOnly) {
            $query->where('is_public', true);
        }

        return $query->get()->mapWithKeys(function ($setting) {
            $key = $setting->group ? "{$setting->group}.{$setting->key}" : $setting->key;
            return [$key => $setting->getCastedValue()];
        });
    }

    /**
     * Set multiple settings at once.
     *
     * @param array $settings
     * @param string|null $group
     * @return void
     */
    public function setMultiple(array $settings, ?string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    /**
     * Clear all app-wide settings.
     *
     * @param string|null $group
     * @return bool
     */
    public function clear(?string $group = null): bool
    {
        $query = Setting::appWide();

        if ($group) {
            $query->where('group', $group);
        }

        return $query->delete();
    }

    /**
     * Increment a numeric setting.
     *
     * @param string $key
     * @param int $amount
     * @param string|null $group
     * @return mixed
     */
    public function increment(string $key, int $amount = 1, ?string $group = 'general')
    {
        $value = $this->get($key, 0, $group);
        $newValue = $value + $amount;
        $this->set($key, $newValue, $group);
        return $newValue;
    }

    /**
     * Decrement a numeric setting.
     *
     * @param string $key
     * @param int $amount
     * @param string|null $group
     * @return mixed
     */
    public function decrement(string $key, int $amount = 1, ?string $group = 'general')
    {
        return $this->increment($key, -$amount, $group);
    }

    /**
     * Toggle a boolean setting.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function toggle(string $key, ?string $group = 'general'): bool
    {
        $value = $this->get($key, false, $group);
        $newValue = !$value;
        $this->set($key, $newValue, $group);
        return $newValue;
    }

    /**
     * Get cache key for a setting.
     *
     * @param string $group
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $group, string $key): string
    {
        return "setting:app:{$group}:{$key}";
    }
}

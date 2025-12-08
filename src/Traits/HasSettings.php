<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Models\Setting;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasSettings
{
    /**
     * Get all settings for this model.
     */
    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'settable');
    }

    /**
     * Get a setting value.
     *
     * @param string $key
     * @param mixed $default
     * @param string|null $group
     * @return mixed
     */
    public function getSetting(string $key, $default = null, ?string $group = 'general')
    {
        $cacheKey = $this->getSettingCacheKey($group, $key);

        return Cache::remember($cacheKey, config('laravel-qol.settings.cache_ttl', 3600), function () use ($key, $group, $default) {
            $setting = $this->settings()
                ->where('key', $key)
                ->where('group', $group)
                ->first();

            return $setting ? $setting->getCastedValue() : $default;
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param bool $isPublic
     * @param array $metadata
     * @return Setting
     */
    public function setSetting(
        string $key,
        $value,
        ?string $group = 'general',
        bool $isPublic = false,
        array $metadata = []
    ): Setting {
        $setting = $this->settings()->updateOrCreate(
            [
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
     * Check if setting exists.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function hasSetting(string $key, ?string $group = 'general'): bool
    {
        return $this->settings()
            ->where('key', $key)
            ->where('group', $group)
            ->exists();
    }

    /**
     * Remove a setting.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function removeSetting(string $key, ?string $group = 'general'): bool
    {
        return $this->settings()
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
    public function getSettingsGroup(string $group): Collection
    {
        return $this->settings()
            ->where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastedValue()];
            });
    }

    /**
     * Get all settings as key-value pairs.
     *
     * @param bool $publicOnly
     * @return Collection
     */
    public function getAllSettings(bool $publicOnly = false): Collection
    {
        $query = $this->settings();

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
    public function setSettings(array $settings, ?string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($key, $value, $group);
        }
    }

    /**
     * Clear all settings.
     *
     * @param string|null $group
     * @return bool
     */
    public function clearSettings(?string $group = null): bool
    {
        $query = $this->settings();

        if ($group) {
            $query->where('group', $group);
        }

        return $query->delete();
    }

    /**
     * Get cache key for a setting.
     *
     * @param string $group
     * @param string $key
     * @return string
     */
    protected function getSettingCacheKey(string $group, string $key): string
    {
        return "setting:" . get_class($this) . ":{$this->id}:{$group}:{$key}";
    }

    /**
     * Increment a numeric setting.
     *
     * @param string $key
     * @param int $amount
     * @param string|null $group
     * @return mixed
     */
    public function incrementSetting(string $key, int $amount = 1, ?string $group = 'general')
    {
        $value = $this->getSetting($key, 0, $group);
        $newValue = $value + $amount;
        $this->setSetting($key, $newValue, $group);
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
    public function decrementSetting(string $key, int $amount = 1, ?string $group = 'general')
    {
        return $this->incrementSetting($key, -$amount, $group);
    }

    /**
     * Toggle a boolean setting.
     *
     * @param string $key
     * @param string|null $group
     * @return bool
     */
    public function toggleSetting(string $key, ?string $group = 'general'): bool
    {
        $value = $this->getSetting($key, false, $group);
        $newValue = !$value;
        $this->setSetting($key, $newValue, $group);
        return $newValue;
    }
}

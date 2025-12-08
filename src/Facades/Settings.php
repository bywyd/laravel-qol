<?php

namespace Bywyd\LaravelQol\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null, string|null $group = 'general')
 * @method static \Bywyd\LaravelQol\Models\Setting set(string $key, mixed $value, string|null $group = 'general', bool $isPublic = false, array $metadata = [])
 * @method static bool has(string $key, string|null $group = 'general')
 * @method static bool remove(string $key, string|null $group = 'general')
 * @method static \Illuminate\Support\Collection getGroup(string $group)
 * @method static \Illuminate\Support\Collection all(bool $publicOnly = false)
 * @method static void setMultiple(array $settings, string|null $group = 'general')
 * @method static bool clear(string|null $group = null)
 * @method static mixed increment(string $key, int $amount = 1, string|null $group = 'general')
 * @method static mixed decrement(string $key, int $amount = 1, string|null $group = 'general')
 * @method static bool toggle(string $key, string|null $group = 'general')
 *
 * @see \Bywyd\LaravelQol\Services\SettingsManager
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-qol.settings';
    }
}

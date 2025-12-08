<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'settable_type',
        'settable_id',
        'group',
        'key',
        'value',
        'type',
        'is_public',
        'metadata',
    ];

    protected $casts = [
        'value' => 'json',
        'is_public' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the parent settable model.
     */
    public function setTable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the setting value casted to appropriate type.
     *
     * @return mixed
     */
    public function getCastedValue()
    {
        return match($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array' => is_array($this->value) ? $this->value : json_decode($this->value, true),
            'json' => $this->value,
            default => $this->value,
        };
    }

    /**
     * Set the setting value.
     *
     * @param mixed $value
     * @return void
     */
    public function setCastedValue($value): void
    {
        $this->type = $this->determineType($value);
        $this->value = $value;
    }

    /**
     * Determine the type of value.
     *
     * @param mixed $value
     * @return string
     */
    protected function determineType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value)) {
            return 'array';
        }

        return 'string';
    }

    /**
     * Get cache key for this setting.
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        if ($this->settable_type && $this->settable_id) {
            return "setting:{$this->settable_type}:{$this->settable_id}:{$this->group}:{$this->key}";
        }

        return "setting:app:{$this->group}:{$this->key}";
    }

    /**
     * Clear cache for this setting.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            $setting->clearCache();
        });

        static::deleted(function ($setting) {
            $setting->clearCache();
        });
    }

    /**
     * Scope to filter by group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter public settings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter app-wide settings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppWide($query)
    {
        return $query->whereNull('settable_type')
                     ->whereNull('settable_id');
    }
}

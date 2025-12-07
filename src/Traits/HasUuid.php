<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the HasUuid trait for a model.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the column name for the UUID.
     *
     * @return string
     */
    public function getUuidColumn(): string
    {
        return property_exists($this, 'uuidColumn') ? $this->uuidColumn : 'uuid';
    }

    /**
     * Scope a query to find a model by UUID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $uuid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereUuid($query, string $uuid)
    {
        return $query->where($this->getUuidColumn(), $uuid);
    }

    /**
     * Find a model by its UUID.
     *
     * @param string $uuid
     * @return static|null
     */
    public static function findByUuid(string $uuid)
    {
        return static::whereUuid($uuid)->first();
    }

    /**
     * Find a model by its UUID or fail.
     *
     * @param string $uuid
     * @return static
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByUuidOrFail(string $uuid)
    {
        return static::whereUuid($uuid)->firstOrFail();
    }
}

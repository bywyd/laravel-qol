<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the HasSlug trait for a model.
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getSlugColumn()})) {
                $model->{$model->getSlugColumn()} = $model->generateSlug();
            }
        });

        static::updating(function ($model) {
            if ($model->shouldRegenerateSlug()) {
                $model->{$model->getSlugColumn()} = $model->generateSlug();
            }
        });
    }

    /**
     * Generate a unique slug.
     *
     * @return string
     */
    protected function generateSlug(): string
    {
        $sourceColumn = $this->getSlugSourceColumn();
        $slugColumn = $this->getSlugColumn();
        $source = $this->{$sourceColumn};

        if (empty($source)) {
            return '';
        }

        $slug = Str::slug($source);
        $originalSlug = $slug;
        $count = 1;

        // Ensure uniqueness
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Check if a slug exists in the database.
     *
     * @param string $slug
     * @return bool
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where($this->getSlugColumn(), $slug);

        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }

    /**
     * Get the column name for the slug.
     *
     * @return string
     */
    public function getSlugColumn(): string
    {
        return property_exists($this, 'slugColumn') ? $this->slugColumn : 'slug';
    }

    /**
     * Get the column name to generate slug from.
     *
     * @return string
     */
    public function getSlugSourceColumn(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'title';
    }

    /**
     * Determine if the slug should be regenerated.
     *
     * @return bool
     */
    protected function shouldRegenerateSlug(): bool
    {
        if (property_exists($this, 'regenerateSlugOnUpdate') && $this->regenerateSlugOnUpdate === false) {
            return false;
        }

        $sourceColumn = $this->getSlugSourceColumn();
        return $this->isDirty($sourceColumn);
    }

    /**
     * Scope a query to find a model by slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereSlug($query, string $slug)
    {
        return $query->where($this->getSlugColumn(), $slug);
    }

    /**
     * Find a model by its slug.
     *
     * @param string $slug
     * @return static|null
     */
    public static function findBySlug(string $slug)
    {
        return static::whereSlug($slug)->first();
    }

    /**
     * Find a model by its slug or fail.
     *
     * @param string $slug
     * @return static
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findBySlugOrFail(string $slug)
    {
        return static::whereSlug($slug)->firstOrFail();
    }
}

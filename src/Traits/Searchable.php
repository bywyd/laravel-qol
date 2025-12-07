<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait Searchable
{
    /**
     * Scope a query to search across specified columns.
     *
     * @param Builder $query
     * @param string $search
     * @param array|null $columns
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $search, ?array $columns = null): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $columns = $columns ?? $this->getSearchableColumns();
        $search = '%' . $search . '%';

        return $query->where(function ($query) use ($columns, $search) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    // Handle relationship columns
                    $this->searchRelationship($query, $column, $search);
                } else {
                    $query->orWhere($column, 'like', $search);
                }
            }
        });
    }

    /**
     * Search within a relationship.
     *
     * @param Builder $query
     * @param string $column
     * @param string $search
     * @return void
     */
    protected function searchRelationship(Builder $query, string $column, string $search): void
    {
        [$relation, $field] = explode('.', $column, 2);

        $query->orWhereHas($relation, function ($query) use ($field, $search) {
            $query->where($field, 'like', $search);
        });
    }

    /**
     * Get the columns to search in.
     *
     * @return array
     */
    protected function getSearchableColumns(): array
    {
        if (property_exists($this, 'searchable')) {
            return $this->searchable;
        }

        // Default to common columns
        return ['name', 'title', 'description'];
    }

    /**
     * Scope for full-text search (MySQL only).
     *
     * @param Builder $query
     * @param string $search
     * @param array|null $columns
     * @return Builder
     */
    public function scopeFullTextSearch(Builder $query, string $search, ?array $columns = null): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $columns = $columns ?? $this->getSearchableColumns();
        $columns = implode(',', $columns);

        return $query->whereRaw(
            "MATCH({$columns}) AGAINST(? IN BOOLEAN MODE)",
            [$search]
        );
    }
}

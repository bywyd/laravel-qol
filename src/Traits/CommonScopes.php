<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * CommonScopes trait provides useful query scope methods.
 */
trait CommonScopes
{
    /**
     * Scope to get recent records.
     */
    public function scopeRecent(Builder $query, int $days = 7, string $column = 'created_at'): Builder
    {
        return $query->where($column, '>=', now()->subDays($days));
    }

    /**
     * Scope to get older records.
     */
    public function scopeOlder(Builder $query, int $days = 30, string $column = 'created_at'): Builder
    {
        return $query->where($column, '<=', now()->subDays($days));
    }

    /**
     * Scope to get records from today.
     */
    public function scopeToday(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereDate($column, today());
    }

    /**
     * Scope to get records from this week.
     */
    public function scopeThisWeek(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope to get records from this month.
     */
    public function scopeThisMonth(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereMonth($column, now()->month)
            ->whereYear($column, now()->year);
    }

    /**
     * Scope to get records from this year.
     */
    public function scopeThisYear(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereYear($column, now()->year);
    }

    /**
     * Scope to get records between dates.
     */
    public function scopeBetweenDates(
        Builder $query,
        string|\DateTime $from,
        string|\DateTime $to,
        string $column = 'created_at'
    ): Builder {
        return $query->whereBetween($column, [$from, $to]);
    }

    /**
     * Scope to order by latest.
     */
    public function scopeLatest(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'desc');
    }

    /**
     * Scope to order by oldest.
     */
    public function scopeOldest(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'asc');
    }

    /**
     * Scope to filter by multiple IDs.
     */
    public function scopeWhereIds(Builder $query, array $ids): Builder
    {
        return $query->whereIn($this->getKeyName(), $ids);
    }

    /**
     * Scope to exclude multiple IDs.
     */
    public function scopeWhereNotIds(Builder $query, array $ids): Builder
    {
        return $query->whereNotIn($this->getKeyName(), $ids);
    }

    /**
     * Scope to search in multiple columns.
     */
    public function scopeWhereLike(Builder $query, string $search, array $columns): Builder
    {
        return $query->where(function ($query) use ($search, $columns) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Scope to get records with null values in a column.
     */
    public function scopeWhereEmpty(Builder $query, string $column): Builder
    {
        return $query->where(function ($query) use ($column) {
            $query->whereNull($column)
                ->orWhere($column, '');
        });
    }

    /**
     * Scope to get records with non-null values in a column.
     */
    public function scopeWhereNotEmpty(Builder $query, string $column): Builder
    {
        return $query->whereNotNull($column)
            ->where($column, '!=', '');
    }

    /**
     * Scope to randomly order results.
     */
    public function scopeRandom(Builder $query): Builder
    {
        return $query->inRandomOrder();
    }

    /**
     * Scope to get popular records based on a count column.
     */
    public function scopePopular(Builder $query, string $column = 'views_count', int $threshold = 100): Builder
    {
        return $query->where($column, '>=', $threshold)
            ->orderBy($column, 'desc');
    }

    /**
     * Scope to get featured records.
     */
    public function scopeFeatured(Builder $query, string $column = 'is_featured'): Builder
    {
        return $query->where($column, true);
    }

    /**
     * Scope to get published records.
     */
    public function scopePublished(Builder $query, string $column = 'published_at'): Builder
    {
        return $query->whereNotNull($column)
            ->where($column, '<=', now());
    }

    /**
     * Scope to get draft records.
     */
    public function scopeDraft(Builder $query, string $column = 'published_at'): Builder
    {
        return $query->whereNull($column)
            ->orWhere($column, '>', now());
    }

    /**
     * Scope to paginate with optional per_page from request.
     */
    public function scopeSmartPaginate(
        Builder $query,
        int $defaultPerPage = 15,
        int $maxPerPage = 100
    ) {
        $perPage = min(
            (int) request()->input('per_page', $defaultPerPage),
            $maxPerPage
        );

        return $query->paginate($perPage);
    }
}

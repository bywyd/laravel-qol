<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * Boot the Sortable trait for a model.
     */
    protected static function bootSortable(): void
    {
        static::creating(function ($model) {
            if (is_null($model->{$model->getSortColumn()})) {
                $model->{$model->getSortColumn()} = $model->getNextSortOrder();
            }
        });
    }

    /**
     * Get the next sort order value.
     *
     * @return int
     */
    protected function getNextSortOrder(): int
    {
        $column = $this->getSortColumn();
        $max = static::max($column);

        return is_null($max) ? 1 : $max + 1;
    }

    /**
     * Get the column name for sorting.
     *
     * @return string
     */
    public function getSortColumn(): string
    {
        return property_exists($this, 'sortColumn') ? $this->sortColumn : 'order';
    }

    /**
     * Scope a query to order by sort column.
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    public function scopeOrdered(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy($this->getSortColumn(), $direction);
    }

    /**
     * Move this record up in sort order.
     *
     * @return bool
     */
    public function moveUp(): bool
    {
        $column = $this->getSortColumn();
        $currentOrder = $this->{$column};

        $previous = static::where($column, '<', $currentOrder)
            ->orderBy($column, 'desc')
            ->first();

        if (!$previous) {
            return false;
        }

        $previousOrder = $previous->{$column};
        $previous->{$column} = $currentOrder;
        $this->{$column} = $previousOrder;

        $previous->save();
        return $this->save();
    }

    /**
     * Move this record down in sort order.
     *
     * @return bool
     */
    public function moveDown(): bool
    {
        $column = $this->getSortColumn();
        $currentOrder = $this->{$column};

        $next = static::where($column, '>', $currentOrder)
            ->orderBy($column, 'asc')
            ->first();

        if (!$next) {
            return false;
        }

        $nextOrder = $next->{$column};
        $next->{$column} = $currentOrder;
        $this->{$column} = $nextOrder;

        $next->save();
        return $this->save();
    }

    /**
     * Move this record to a specific position.
     *
     * @param int $position
     * @return bool
     */
    public function moveTo(int $position): bool
    {
        $column = $this->getSortColumn();
        $currentOrder = $this->{$column};

        if ($currentOrder === $position) {
            return true;
        }

        if ($position < $currentOrder) {
            // Moving up
            static::whereBetween($column, [$position, $currentOrder - 1])
                ->increment($column);
        } else {
            // Moving down
            static::whereBetween($column, [$currentOrder + 1, $position])
                ->decrement($column);
        }

        $this->{$column} = $position;
        return $this->save();
    }

    /**
     * Swap position with another record.
     *
     * @param self $other
     * @return bool
     */
    public function swapWith(self $other): bool
    {
        $column = $this->getSortColumn();
        $thisOrder = $this->{$column};
        $otherOrder = $other->{$column};

        $this->{$column} = $otherOrder;
        $other->{$column} = $thisOrder;

        $other->save();
        return $this->save();
    }
}

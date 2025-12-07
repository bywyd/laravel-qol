<?php

namespace Bywyd\LaravelQol\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    /**
     * Scope a query to only include active records.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->getStatusColumn(), $this->getActiveStatusValue());
    }

    /**
     * Scope a query to only include inactive records.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where($this->getStatusColumn(), '!=', $this->getActiveStatusValue());
    }

    /**
     * Scope a query by status.
     *
     * @param Builder $query
     * @param mixed $status
     * @return Builder
     */
    public function scopeStatus(Builder $query, $status): Builder
    {
        return $query->where($this->getStatusColumn(), $status);
    }

    /**
     * Check if the record is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->{$this->getStatusColumn()} === $this->getActiveStatusValue();
    }

    /**
     * Check if the record is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return !$this->isActive();
    }

    /**
     * Activate the record.
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->{$this->getStatusColumn()} = $this->getActiveStatusValue();
        return $this->save();
    }

    /**
     * Deactivate the record.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->{$this->getStatusColumn()} = $this->getInactiveStatusValue();
        return $this->save();
    }

    /**
     * Toggle the status.
     *
     * @return bool
     */
    public function toggleStatus(): bool
    {
        if ($this->isActive()) {
            return $this->deactivate();
        }

        return $this->activate();
    }

    /**
     * Get the column name for status.
     *
     * @return string
     */
    protected function getStatusColumn(): string
    {
        return property_exists($this, 'statusColumn') ? $this->statusColumn : 'status';
    }

    /**
     * Get the value for active status.
     *
     * @return mixed
     */
    protected function getActiveStatusValue()
    {
        return property_exists($this, 'activeStatusValue') ? $this->activeStatusValue : 1;
    }

    /**
     * Get the value for inactive status.
     *
     * @return mixed
     */
    protected function getInactiveStatusValue()
    {
        return property_exists($this, 'inactiveStatusValue') ? $this->inactiveStatusValue : 0;
    }
}

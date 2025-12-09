<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Enums\HistoryLogTypes;
use Bywyd\LaravelQol\Models\ModelHistory;
use Illuminate\Support\Facades\Auth;

trait HasHistory
{
    /**
     * Models with history logging temporarily disabled.
     *
     * @var array
     */
    protected static $modelsWithHistoryDisabled = [];

    /**
     * Boot the HasHistory trait for a model.
     */
    protected static function bootHasHistory()
    {
        static::created(function ($model) {
            if ($model->shouldLogHistory('created')) {
                $model->logHistory(HistoryLogTypes::CREATED, 'Model created');
            }
        });

        static::updated(function ($model) {
            if ($model->shouldLogHistory('updated') && $model->wasChanged()) {
                $model->logHistory(HistoryLogTypes::UPDATED, 'Model updated');
            }
        });

        static::deleted(function ($model) {
            if ($model->shouldLogHistory('deleted')) {
                $model->logHistory(HistoryLogTypes::DELETED, 'Model deleted');
            }
            
            // Delete associated histories AFTER logging the deletion
            if ($model->shouldDeleteHistoriesOnDelete()) {
                $model->histories()->delete();
            }
        });
    }

    /**
     * Get all histories for this model.
     */
    public function histories()
    {
        return $this->morphMany(ModelHistory::class, 'modelable')->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest history record.
     */
    public function latestHistory()
    {
        return $this->morphOne(ModelHistory::class, 'modelable')->latestOfMany();
    }

    /**
     * Log a history record for this model.
     *
     * @param HistoryLogTypes|int|null $type
     * @param string|null $description
     * @param array $additionalData
     * @return ModelHistory
     */
    public function logHistory(
        HistoryLogTypes|int|null $type = null,
        ?string $description = null,
        array $additionalData = []
    ): ModelHistory {
        $history = $this->createHistoryRecord($type, $description, $additionalData);
        $history->save();

        return $history;
    }

    /**
     * Create a new history record without saving it.
     *
     * @param HistoryLogTypes|int|null $type
     * @param string|null $description
     * @param array $additionalData
     * @return ModelHistory
     */
    public function newHistory(
        HistoryLogTypes|int|null $type = null,
        ?string $description = null,
        array $additionalData = []
    ): ModelHistory {
        return $this->createHistoryRecord($type, $description, $additionalData);
    }

    /**
     * Create a history record instance.
     *
     * @param HistoryLogTypes|int|null $type
     * @param string|null $description
     * @param array $additionalData
     * @return ModelHistory
     */
    protected function createHistoryRecord(
        HistoryLogTypes|int|null $type = null,
        ?string $description = null,
        array $additionalData = []
    ): ModelHistory {
        $originalData = $this->getOriginal();
        $changedData = $this->getChanges();

        // Filter out attributes that should not be logged
        $excludedAttributes = $this->getHistoryExcludedAttributes();
        if (!empty($excludedAttributes)) {
            $changedData = array_diff_key($changedData, array_flip($excludedAttributes));
            $originalData = array_diff_key($originalData, array_flip($excludedAttributes));
        }

        // Get old values only for changed attributes
        $oldData = !empty($changedData) 
            ? array_intersect_key($originalData, $changedData) 
            : null;

        $history = new ModelHistory([
            'modelable_type' => get_class($this),
            'modelable_id' => $this->getKey(),
            'type' => $type,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => !empty($changedData) ? $changedData : null,
            'created_by' => $additionalData['created_by'] ?? $this->resolveHistoryUser(),
            'updated_by' => $additionalData['updated_by'] ?? $this->resolveHistoryUser(),
        ]);

        return $history;
    }

    /**
     * Resolve the user ID for history logging.
     *
     * @return int|null
     */
    protected function resolveHistoryUser(): ?int
    {
        if (Auth::check()) {
            return Auth::id();
        }

        // Check if the model has a user_id or created_by attribute
        if (isset($this->user_id)) {
            return $this->user_id;
        }

        if (isset($this->created_by)) {
            return $this->created_by;
        }

        return null;
    }

    /**
     * Get attributes that should be excluded from history logging.
     *
     * @return array
     */
    protected function getHistoryExcludedAttributes(): array
    {
        if (property_exists($this, 'historyExcludedAttributes')) {
            return $this->historyExcludedAttributes;
        }

        return config('laravel-qol.history.excluded_attributes', ['password', 'remember_token', 'updated_at']);
    }

    /**
     * Determine if history should be logged for the given event.
     *
     * @param string $event
     * @return bool
     */
    protected function shouldLogHistory(string $event): bool
    {
        $modelKey = get_class($this) . ':' . ($this->getKey() ?? 'new');
        
        if (isset(static::$modelsWithHistoryDisabled[$modelKey])) {
            return false;
        }

        if (property_exists($this, 'historyEvents')) {
            return in_array($event, $this->historyEvents);
        }

        return in_array($event, config('laravel-qol.history.tracked_events', ['created', 'updated', 'deleted']));
    }

    /**
     * Determine if histories should be deleted when the model is deleted.
     *
     * @return bool
     */
    protected function shouldDeleteHistoriesOnDelete(): bool
    {
        if (property_exists($this, 'deleteHistoriesOnDelete')) {
            return $this->deleteHistoriesOnDelete;
        }

        return config('laravel-qol.history.delete_on_model_delete', true);
    }

    /**
     * Temporarily disable history logging for a callback.
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutHistory(callable $callback)
    {
        $modelKey = get_class($this) . ':' . ($this->getKey() ?? 'new');
        static::$modelsWithHistoryDisabled[$modelKey] = true;

        try {
            return $callback($this);
        } finally {
            unset(static::$modelsWithHistoryDisabled[$modelKey]);
        }
    }

    /**
     * Get the history by type.
     *
     * @param HistoryLogTypes|int $type
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function historiesByType(HistoryLogTypes|int $type)
    {
        $typeValue = $type instanceof HistoryLogTypes ? $type->value : $type;
        
        return $this->histories()->where('type', $typeValue);
    }
}

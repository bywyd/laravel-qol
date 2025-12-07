<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelHistory extends Model
{
    protected $table = 'model_histories';

    protected $fillable = [
        'modelable_type',
        'modelable_id',
        'type',
        'description',
        'old_data',
        'new_data',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'type' => 'integer',
    ];

    /**
     * Get the parent modelable model.
     */
    public function modelable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this history record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get the user who last updated this history record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    /**
     * Get a summary of changes.
     *
     * @return array
     */
    public function getChangesSummary(): array
    {
        if (empty($this->new_data)) {
            return [];
        }

        $changes = [];
        foreach ($this->new_data as $key => $newValue) {
            $oldValue = $this->old_data[$key] ?? null;
            $changes[$key] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $changes;
    }

    /**
     * Check if a specific attribute was changed.
     *
     * @param string $attribute
     * @return bool
     */
    public function hasAttributeChanged(string $attribute): bool
    {
        return isset($this->new_data[$attribute]);
    }

    /**
     * Get the old value of an attribute.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getOldValue(string $attribute)
    {
        return $this->old_data[$attribute] ?? null;
    }

    /**
     * Get the new value of an attribute.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getNewValue(string $attribute)
    {
        return $this->new_data[$attribute] ?? null;
    }
}

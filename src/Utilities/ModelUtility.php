<?php

namespace Bywyd\LaravelQol\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelUtility
{
    public static function getTableColumns(Model|string $model): array
    {
        $table = is_string($model) ? (new $model)->getTable() : $model->getTable();
        return Schema::getColumnListing($table);
    }

    public static function getFillableColumns(Model|string $model): array
    {
        $instance = is_string($model) ? new $model : $model;
        return $instance->getFillable();
    }

    public static function getHiddenColumns(Model|string $model): array
    {
        $instance = is_string($model) ? new $model : $model;
        return $instance->getHidden();
    }

    public static function getDirtyAttributes(Model $model): array
    {
        return $model->getDirty();
    }

    public static function getChangedAttributes(Model $model): array
    {
        return $model->getChanges();
    }

    public static function getOriginalAttributes(Model $model): array
    {
        return $model->getOriginal();
    }

    public static function hasAttribute(Model $model, string $attribute): bool
    {
        return array_key_exists($attribute, $model->getAttributes());
    }

    public static function toArrayWithRelations(Model $model, array $relations = []): array
    {
        $array = $model->toArray();
        
        foreach ($relations as $relation) {
            if ($model->relationLoaded($relation)) {
                $array[$relation] = $model->$relation?->toArray();
            }
        }
        
        return $array;
    }

    public static function cloneModel(Model $model, array $except = []): Model
    {
        $clone = $model->replicate($except);
        return $clone;
    }

    public static function getModelClass(Model $model): string
    {
        return get_class($model);
    }

    public static function getModelTable(Model|string $model): string
    {
        $instance = is_string($model) ? new $model : $model;
        return $instance->getTable();
    }

    public static function getModelKey(Model|string $model): string
    {
        $instance = is_string($model) ? new $model : $model;
        return $instance->getKeyName();
    }

    public static function exists(Model $model): bool
    {
        return $model->exists;
    }

    public static function wasRecentlyCreated(Model $model): bool
    {
        return $model->wasRecentlyCreated;
    }

    public static function getRelations(Model $model): array
    {
        return $model->getRelations();
    }

    public static function getLoadedRelations(Model $model): array
    {
        return array_keys($model->getRelations());
    }

    public static function hasRelation(Model $model, string $relation): bool
    {
        return method_exists($model, $relation);
    }

    public static function isRelationLoaded(Model $model, string $relation): bool
    {
        return $model->relationLoaded($relation);
    }

    public static function diff(Model $original, Model $modified): array
    {
        $originalAttrs = $original->getAttributes();
        $modifiedAttrs = $modified->getAttributes();
        
        $diff = [];
        
        foreach ($modifiedAttrs as $key => $value) {
            if (!array_key_exists($key, $originalAttrs) || $originalAttrs[$key] !== $value) {
                $diff[$key] = [
                    'old' => $originalAttrs[$key] ?? null,
                    'new' => $value,
                ];
            }
        }
        
        return $diff;
    }
}

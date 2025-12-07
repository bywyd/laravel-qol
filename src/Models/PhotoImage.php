<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class PhotoImage extends Model
{
    protected $table = 'photo_images';

    protected $fillable = [
        'uuid',
        'path',
        'disk',
        'original_name',
        'mime_type',
        'size',
        'order',
        'tag',
    ];

    protected $casts = [
        'order' => 'integer',
        'size' => 'integer',
    ];

    /**
     * Get the parent modelable model.
     */
    public function modelable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL to the image.
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.images.disk', 'public');
        
        return Storage::disk($disk)->url($this->path);
    }

    /**
     * Get the full path to the image.
     *
     * @return string|null
     */
    public function getFullPathAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.images.disk', 'public');
        
        return Storage::disk($disk)->path($this->path);
    }

    /**
     * Check if the image file exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $disk = $this->disk ?? config('laravel-qol.images.disk', 'public');
        
        return Storage::disk($disk)->exists($this->path);
    }

    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function getHumanSizeAttribute(): string
    {
        if (!$this->size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

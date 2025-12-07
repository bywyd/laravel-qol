<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    protected $table = 'videos';

    protected $fillable = [
        'uuid',
        'path',
        'disk',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'duration',
        'width',
        'height',
        'thumbnail_path',
        'order',
        'tag',
        'metadata',
    ];

    protected $casts = [
        'order' => 'integer',
        'size' => 'integer',
        'duration' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the parent modelable model.
     */
    public function modelable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL to the video.
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.videos.disk', 'public');
        
        return Storage::disk($disk)->url($this->path);
    }

    /**
     * Get the full URL to the thumbnail.
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.videos.disk', 'public');
        
        return Storage::disk($disk)->url($this->thumbnail_path);
    }

    /**
     * Get the full path to the video.
     *
     * @return string|null
     */
    public function getFullPathAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.videos.disk', 'public');
        
        return Storage::disk($disk)->path($this->path);
    }

    /**
     * Check if the video file exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $disk = $this->disk ?? config('laravel-qol.videos.disk', 'public');
        
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

    /**
     * Get human-readable duration.
     *
     * @return string
     */
    public function getHumanDurationAttribute(): string
    {
        if (!$this->duration) {
            return '0:00';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get aspect ratio.
     *
     * @return string|null
     */
    public function getAspectRatioAttribute(): ?string
    {
        if (!$this->width || !$this->height) {
            return null;
        }

        $gcd = function($a, $b) use (&$gcd) {
            return $b ? $gcd($b, $a % $b) : $a;
        };

        $divisor = $gcd($this->width, $this->height);
        
        return ($this->width / $divisor) . ':' . ($this->height / $divisor);
    }

    /**
     * Check if video is HD (720p or higher).
     *
     * @return bool
     */
    public function isHD(): bool
    {
        return $this->height >= 720;
    }

    /**
     * Check if video is Full HD (1080p or higher).
     *
     * @return bool
     */
    public function isFullHD(): bool
    {
        return $this->height >= 1080;
    }

    /**
     * Check if video is 4K (2160p or higher).
     *
     * @return bool
     */
    public function is4K(): bool
    {
        return $this->height >= 2160;
    }

    /**
     * Download the video.
     *
     * @param string|null $name
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(?string $name = null)
    {
        $disk = $this->disk ?? config('laravel-qol.videos.disk', 'public');
        $fileName = $name ?? $this->original_name ?? basename($this->path);
        
        return Storage::disk($disk)->download($this->path, $fileName);
    }
}

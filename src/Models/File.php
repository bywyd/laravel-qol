<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'uuid',
        'path',
        'disk',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'order',
        'tag',
        'metadata',
    ];

    protected $casts = [
        'order' => 'integer',
        'size' => 'integer',
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
     * Get the full URL to the file.
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.files.disk', 'public');
        
        return Storage::disk($disk)->url($this->path);
    }

    /**
     * Get the full path to the file.
     *
     * @return string|null
     */
    public function getFullPathAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? config('laravel-qol.files.disk', 'public');
        
        return Storage::disk($disk)->path($this->path);
    }

    /**
     * Check if the file exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $disk = $this->disk ?? config('laravel-qol.files.disk', 'public');
        
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
     * Download the file.
     *
     * @param string|null $name
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(?string $name = null)
    {
        $disk = $this->disk ?? config('laravel-qol.files.disk', 'public');
        $fileName = $name ?? $this->original_name ?? basename($this->path);
        
        return Storage::disk($disk)->download($this->path, $fileName);
    }

    /**
     * Get file contents.
     *
     * @return string|null
     */
    public function contents(): ?string
    {
        $disk = $this->disk ?? config('laravel-qol.files.disk', 'public');
        
        if (!$this->exists()) {
            return null;
        }
        
        return Storage::disk($disk)->get($this->path);
    }

    /**
     * Check if file is a document.
     *
     * @return bool
     */
    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];

        return in_array($this->mime_type, $documentMimes);
    }

    /**
     * Check if file is an archive.
     *
     * @return bool
     */
    public function isArchive(): bool
    {
        $archiveMimes = [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/gzip',
        ];

        return in_array($this->mime_type, $archiveMimes);
    }
}

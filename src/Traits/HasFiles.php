<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFiles
{
    /**
     * Get all files for this model.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'modelable')->orderBy('order');
    }

    /**
     * Upload and attach a file to this model.
     *
     * @param UploadedFile $file
     * @param int $order
     * @param string|null $tag
     * @param array $metadata
     * @return File
     */
    public function uploadFile(
        UploadedFile $file, 
        int $order = 0, 
        ?string $tag = null,
        array $metadata = []
    ): File {
        $disk = config('laravel-qol.files.disk', 'public');
        $basePath = config('laravel-qol.files.path', 'files');
        
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($basePath, $filename, $disk);

        $fileModel = new File();
        $fileModel->uuid = Str::uuid();
        $fileModel->path = $path;
        $fileModel->disk = $disk;
        $fileModel->original_name = $file->getClientOriginalName();
        $fileModel->mime_type = $file->getMimeType();
        $fileModel->extension = $file->getClientOriginalExtension();
        $fileModel->size = $file->getSize();
        $fileModel->order = $order;
        $fileModel->tag = $tag;
        $fileModel->metadata = $metadata;
        
        $this->files()->save($fileModel);

        return $fileModel;
    }

    /**
     * Delete a file from storage and database.
     *
     * @param File $file
     * @return void
     */
    public function deleteFile(File $file): void
    {
        $disk = $file->disk ?? config('laravel-qol.files.disk', 'public');
        
        if (Storage::disk($disk)->exists($file->path)) {
            Storage::disk($disk)->delete($file->path);
        }
        
        $file->delete();
    }

    /**
     * Delete all files for this model.
     *
     * @return void
     */
    public function deleteAllFiles(): void
    {
        foreach ($this->files as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * Get files by tag.
     *
     * @param string $tag
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function filesByTag(string $tag)
    {
        return $this->files()->where('tag', $tag);
    }

    /**
     * Get files by mime type.
     *
     * @param string $mimeType
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function filesByMimeType(string $mimeType)
    {
        return $this->files()->where('mime_type', $mimeType);
    }

    /**
     * Get document files.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function documents()
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

        return $this->files()->whereIn('mime_type', $documentMimes)->get();
    }

    /**
     * Reorder files.
     *
     * @param array $fileIds Array of file IDs in the desired order
     * @return void
     */
    public function reorderFiles(array $fileIds): void
    {
        foreach ($fileIds as $order => $fileId) {
            $this->files()->where('id', $fileId)->update(['order' => $order]);
        }
    }
}

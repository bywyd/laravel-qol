<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Models\PhotoImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImages
{
    /**
     * Get all images for this model.
     */
    public function images()
    {
        return $this->morphMany(PhotoImage::class, 'modelable')->orderBy('order');
    }

    /**
     * Upload and attach an image to this model.
     *
     * @param UploadedFile $file
     * @param int $order
     * @param string|null $tag
     * @return PhotoImage
     */
    public function uploadImage(UploadedFile $file, int $order = 0, ?string $tag = null): PhotoImage
    {
        $disk = config('laravel-qol.images.disk', 'public');
        $basePath = config('laravel-qol.images.path', 'images');
        
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($basePath, $filename, $disk);

        $image = new PhotoImage();
        $image->uuid = Str::uuid();
        $image->path = $path;
        $image->disk = $disk;
        $image->original_name = $file->getClientOriginalName();
        $image->mime_type = $file->getMimeType();
        $image->size = $file->getSize();
        $image->order = $order;
        $image->tag = $tag;
        
        $this->images()->save($image);

        return $image;
    }

    /**
     * Delete an image from storage and database.
     *
     * @param PhotoImage $image
     * @return void
     */
    public function deleteImage(PhotoImage $image): void
    {
        $disk = $image->disk ?? config('laravel-qol.images.disk', 'public');
        
        if (Storage::disk($disk)->exists($image->path)) {
            Storage::disk($disk)->delete($image->path);
        }
        
        $image->delete();
    }

    /**
     * Delete all images for this model.
     *
     * @return void
     */
    public function deleteAllImages(): void
    {
        foreach ($this->images as $image) {
            $this->deleteImage($image);
        }
    }

    /**
     * Get images by tag.
     *
     * @param string $tag
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function imagesByTag(string $tag)
    {
        return $this->images()->where('tag', $tag);
    }

    /**
     * Get the primary/first image.
     *
     * @return PhotoImage|null
     */
    public function primaryImage(): ?PhotoImage
    {
        return $this->images()->orderBy('order')->first();
    }

    /**
     * Reorder images.
     *
     * @param array $imageIds Array of image IDs in the desired order
     * @return void
     */
    public function reorderImages(array $imageIds): void
    {
        foreach ($imageIds as $order => $imageId) {
            $this->images()->where('id', $imageId)->update(['order' => $order]);
        }
    }
}

<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasVideos
{
    /**
     * Get all videos for this model.
     */
    public function videos()
    {
        return $this->morphMany(Video::class, 'modelable')->orderBy('order');
    }

    /**
     * Upload and attach a video to this model.
     *
     * @param UploadedFile $file
     * @param int $order
     * @param string|null $tag
     * @param array $metadata
     * @return Video
     */
    public function uploadVideo(
        UploadedFile $file, 
        int $order = 0, 
        ?string $tag = null,
        array $metadata = []
    ): Video {
        $disk = config('laravel-qol.videos.disk', 'public');
        $basePath = config('laravel-qol.videos.path', 'videos');
        
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($basePath, $filename, $disk);

        $video = new Video();
        $video->uuid = Str::uuid();
        $video->path = $path;
        $video->disk = $disk;
        $video->original_name = $file->getClientOriginalName();
        $video->mime_type = $file->getMimeType();
        $video->extension = $file->getClientOriginalExtension();
        $video->size = $file->getSize();
        $video->order = $order;
        $video->tag = $tag;
        $video->metadata = $metadata;
        
        // Note: duration, width, height, and thumbnail would typically be
        // extracted using a video processing library like FFmpeg
        // This can be done in a job after upload
        
        $this->videos()->save($video);

        return $video;
    }

    /**
     * Delete a video from storage and database.
     *
     * @param Video $video
     * @return void
     */
    public function deleteVideo(Video $video): void
    {
        $disk = $video->disk ?? config('laravel-qol.videos.disk', 'public');
        
        if (Storage::disk($disk)->exists($video->path)) {
            Storage::disk($disk)->delete($video->path);
        }

        // Delete thumbnail if exists
        if ($video->thumbnail_path && Storage::disk($disk)->exists($video->thumbnail_path)) {
            Storage::disk($disk)->delete($video->thumbnail_path);
        }
        
        $video->delete();
    }

    /**
     * Delete all videos for this model.
     *
     * @return void
     */
    public function deleteAllVideos(): void
    {
        foreach ($this->videos as $video) {
            $this->deleteVideo($video);
        }
    }

    /**
     * Get videos by tag.
     *
     * @param string $tag
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function videosByTag(string $tag)
    {
        return $this->videos()->where('tag', $tag);
    }

    /**
     * Get HD videos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hdVideos()
    {
        return $this->videos()->where('height', '>=', 720)->get();
    }

    /**
     * Reorder videos.
     *
     * @param array $videoIds Array of video IDs in the desired order
     * @return void
     */
    public function reorderVideos(array $videoIds): void
    {
        foreach ($videoIds as $order => $videoId) {
            $this->videos()->where('id', $videoId)->update(['order' => $order]);
        }
    }
}

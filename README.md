# Laravel QoL

**Laravel Quality of Life** - A collection of helpful traits, utilities, and tools to enhance your Laravel development experience.

## Features

### Media Management
- **HasImages Trait** - Easy image management with ordering and tagging
- **HasFiles Trait** - Generic file upload and management
- **HasVideos Trait** - Video upload with metadata support
- **PhotoImage Model** - Complete image model with helper methods
- **File Model** - Flexible file handling with type detection
- **Video Model** - Video model with duration, resolution, and thumbnails

### Model Enhancements
- **HasHistory Trait** - Automatic model change tracking
- **HasUuid Trait** - Automatic UUID generation for models
- **HasSlug Trait** - Automatic slug generation from any field
- **HasStatus Trait** - Status management with active/inactive scopes
- **Sortable Trait** - Easy ordering/sorting functionality
- **Cacheable Trait** - Built-in model-level caching
- **Searchable Trait** - Simple and full-text search capabilities

## Installation

Install via Composer:

```bash
composer require bywyd/laravel-qol
```

## Configuration

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=laravel-qol-config
```

Publish the migrations:

```bash
php artisan vendor:publish --tag=laravel-qol-migrations
php artisan migrate
```

## Usage

### HasHistory Trait

Track all changes made to your models automatically:

```php
use Bywyd\LaravelQol\Traits\HasHistory;

class Post extends Model
{
    use HasHistory;

    // Optional: Exclude specific attributes from history
    protected $historyExcludedAttributes = ['views', 'updated_at'];
    
    // Optional: Only log specific events
    protected $historyEvents = ['created', 'updated'];
    
    // Optional: Keep histories when model is deleted
    protected $deleteHistoriesOnDelete = false;
}

// Usage
$post = Post::find(1);
$post->histories; // Get all history records
$post->latestHistory; // Get the latest history

// Manual history logging
$post->logHistory(HistoryLogTypes::CUSTOM, 'Custom action performed');

// Temporarily disable history logging
$post->withoutHistory(function($post) {
    $post->update(['title' => 'No history logged']);
});
```

### HasImages Trait

Manage images for your models with ease:

```php
use Bywyd\LaravelQol\Traits\HasImages;

class Product extends Model
{
    use HasImages;
}

// Usage
$product = Product::find(1);

// Upload an image
$image = $product->uploadImage($request->file('image'), 0, 'gallery');

// Get all images
$product->images;

// Get images by tag
$product->imagesByTag('gallery');

// Get primary image
$product->primaryImage();

// Reorder images
$product->reorderImages([3, 1, 2]); // Array of image IDs

// Delete an image
$product->deleteImage($image);

// Delete all images
$product->deleteAllImages();
```

### HasFiles Trait

Upload and manage any type of file:

```php
use Bywyd\LaravelQol\Traits\HasFiles;

class Document extends Model
{
    use HasFiles;
}

// Usage
$document = Document::find(1);

// Upload a file
$file = $document->uploadFile($request->file('attachment'), 0, 'contract', [
    'department' => 'Legal'
]);

// Get all files
$document->files;

// Get files by tag
$document->filesByTag('contract');

// Get document files (PDFs, DOCs, etc.)
$document->documents();

// Download a file
return $file->download();

// Delete a file
$document->deleteFile($file);
```

### HasVideos Trait

Manage video uploads with metadata:

```php
use Bywyd\LaravelQol\Traits\HasVideos;

class Course extends Model
{
    use HasVideos;
}

// Usage
$course = Course::find(1);

// Upload a video
$video = $course->uploadVideo($request->file('video'), 0, 'lesson-1');

// Access video properties
$video->url; // Public URL
$video->human_size; // "50.5 MB"
$video->human_duration; // "5:23"
$video->aspect_ratio; // "16:9"

// Quality checks
$video->isHD(); // 720p or higher
$video->isFullHD(); // 1080p or higher
$video->is4K(); // 2160p or higher

// Get HD videos
$course->hdVideos();

// Delete a video
$course->deleteVideo($video);
```

### HasUuid Trait

Automatically generate UUIDs for your models:

```php
use Bywyd\LaravelQol\Traits\HasUuid;

class User extends Model
{
    use HasUuid;
    
    // Optional: Customize UUID column
    protected $uuidColumn = 'uuid';
}

// Usage
$user = User::create(['name' => 'John']);
$user->uuid; // "550e8400-e29b-41d4-a716-446655440000"

// Find by UUID
$user = User::findByUuid('550e8400-e29b-41d4-a716-446655440000');
$user = User::findByUuidOrFail($uuid);
```

### HasSlug Trait

Automatic slug generation from any field:

```php
use Bywyd\LaravelQol\Traits\HasSlug;

class Article extends Model
{
    use HasSlug;
    
    // Optional: Customize slug source
    protected $slugSource = 'title';
    
    // Optional: Customize slug column
    protected $slugColumn = 'slug';
    
    // Optional: Prevent regeneration on update
    protected $regenerateSlugOnUpdate = false;
}

// Usage
$article = Article::create(['title' => 'Hello World']);
$article->slug; // "hello-world"

// Find by slug
$article = Article::findBySlug('hello-world');
$article = Article::findBySlugOrFail('hello-world');
```

### HasStatus Trait

Manage model status with convenient methods:

```php
use Bywyd\LaravelQol\Traits\HasStatus;

class Task extends Model
{
    use HasStatus;
    
    // Optional: Customize status column
    protected $statusColumn = 'status';
    
    // Optional: Customize status values
    protected $activeStatusValue = 1;
    protected $inactiveStatusValue = 0;
}

// Usage
$task = Task::find(1);

// Status checks
$task->isActive();
$task->isInactive();

// Status changes
$task->activate();
$task->deactivate();
$task->toggleStatus();

// Query scopes
Task::active()->get();
Task::inactive()->get();
Task::status(1)->get();
```

### Sortable Trait

Add ordering functionality to your models:

```php
use Bywyd\LaravelQol\Traits\Sortable;

class MenuItem extends Model
{
    use Sortable;
    
    // Optional: Customize sort column
    protected $sortColumn = 'order';
}

// Usage
$item = MenuItem::find(1);

// Move operations
$item->moveUp();
$item->moveDown();
$item->moveTo(5);
$item->swapWith($otherItem);

// Query scope
MenuItem::ordered()->get(); // Ordered by sort column
MenuItem::ordered('desc')->get();
```

### Cacheable Trait

Built-in model caching:

```php
use Bywyd\LaravelQol\Traits\Cacheable;

class Settings extends Model
{
    use Cacheable;
    
    // Optional: Customize cache prefix
    protected $cachePrefix = 'settings';
    
    // Optional: Customize TTL (seconds)
    protected $cacheTtl = 3600;
}

// Usage
$settings = Settings::find(1);

// Cache data
$value = $settings->remember('config', function() {
    return expensive_operation();
});

// Cache forever
$value = $settings->rememberForever('permanent', function() {
    return static_data();
});

// Clear cache
$settings->clearCache();
```

### Searchable Trait

Add search functionality:

```php
use Bywyd\LaravelQol\Traits\Searchable;

class Product extends Model
{
    use Searchable;
    
    // Define searchable columns
    protected $searchable = ['name', 'description', 'category.name'];
}

// Usage
// Simple search
Product::search('laptop')->get();

// Custom columns
Product::search('laptop', ['name', 'sku'])->get();

// Full-text search (MySQL)
Product::fullTextSearch('gaming laptop')->get();
```

## Available Traits

### Media Traits
- **HasImages** - Image management with ordering, tagging, and URLs
- **HasFiles** - Generic file management with type detection
- **HasVideos** - Video management with metadata and thumbnails

### Model Enhancement Traits
- **HasHistory** - Automatic change tracking with old/new values
- **HasUuid** - Auto-generate UUIDs on model creation
- **HasSlug** - Auto-generate unique slugs from any field
- **HasStatus** - Active/inactive status management
- **Sortable** - Ordering and reordering functionality
- **Cacheable** - Model-level caching with auto-invalidation
- **Searchable** - Simple and full-text search

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

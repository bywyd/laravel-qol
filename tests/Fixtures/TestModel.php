<?php

namespace Bywyd\LaravelQol\Tests\Fixtures;

use Bywyd\LaravelQol\Traits\HasHistory;
use Bywyd\LaravelQol\Traits\HasUuid;
use Bywyd\LaravelQol\Traits\HasSlug;
use Bywyd\LaravelQol\Traits\HasStatus;
use Bywyd\LaravelQol\Traits\Sortable;
use Bywyd\LaravelQol\Traits\Cacheable;
use Bywyd\LaravelQol\Traits\Searchable;
use Bywyd\LaravelQol\Traits\HasSettings;
use Bywyd\LaravelQol\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasHistory;
    use HasUuid;
    use HasSlug;
    use HasStatus;
    use Sortable;
    use Cacheable;
    use Searchable;
    use HasSettings;
    use CommonScopes;

    protected $table = 'test_models';

    protected $fillable = [
        'name',
        'value',
        'status',
        'order',
        'uuid',
        'slug',
        'description',
    ];

    protected $casts = [
        'value' => 'integer',
        'status' => 'integer',
        'order' => 'integer',
    ];

    // HasSlug configuration
    protected $slugSource = 'name';
    protected $slugColumn = 'slug';

    // Searchable configuration
    protected $searchable = ['name', 'description'];
}

<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class CacheableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_cache_and_retrieve_data()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $result = $model->remember('test-key', function () {
            return 'cached-value';
        });

        $this->assertEquals('cached-value', $result);
        
        // Verify it's cached
        $cached = Cache::get($model->getCacheKey('test-key'));
        $this->assertEquals('cached-value', $cached);
    }

    /** @test */
    public function it_generates_correct_cache_key()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $key = $model->getCacheKey('suffix');

        $this->assertEquals("testmodel.{$model->id}.suffix", $key);
    }

    /** @test */
    public function it_can_cache_forever()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $result = $model->rememberForever('forever-key', function () {
            return 'permanent-value';
        });

        $this->assertEquals('permanent-value', $result);
        
        $cached = Cache::get($model->getCacheKey('forever-key'));
        $this->assertEquals('permanent-value', $cached);
    }

    /** @test */
    public function it_clears_cache_on_save()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $model->remember('test-key', function () {
            return 'cached-value';
        });

        $this->assertNotNull(Cache::get($model->getCacheKey('test-key')));

        $model->update(['name' => 'Updated']);

        $this->assertNull(Cache::get($model->getCacheKey('test-key')));
    }

    /** @test */
    public function it_clears_cache_on_delete()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $model->remember('test-key', function () {
            return 'cached-value';
        });

        $this->assertNotNull(Cache::get($model->getCacheKey('test-key')));

        $model->delete();

        $this->assertNull(Cache::get($model->getCacheKey('test-key')));
    }

    /** @test */
    public function it_can_manually_clear_cache()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $model->remember('test-key', function () {
            return 'cached-value';
        });

        $this->assertNotNull(Cache::get($model->getCacheKey('test-key')));

        $model->clearCache();

        $this->assertNull(Cache::get($model->getCacheKey('test-key')));
    }
}

<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasSlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_generates_slug_on_creation()
    {
        $model = TestModel::create(['name' => 'Hello World', 'value' => 100]);

        $this->assertEquals('hello-world', $model->slug);
    }

    /** @test */
    public function it_generates_unique_slugs()
    {
        $model1 = TestModel::create(['name' => 'Test', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Test', 'value' => 200]);
        $model3 = TestModel::create(['name' => 'Test', 'value' => 300]);

        $this->assertEquals('test', $model1->slug);
        $this->assertEquals('test-1', $model2->slug);
        $this->assertEquals('test-2', $model3->slug);
    }

    /** @test */
    public function it_can_find_by_slug()
    {
        $model = TestModel::create(['name' => 'Test Model', 'value' => 100]);

        $found = TestModel::findBySlug('test-model');

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }

    /** @test */
    public function it_can_find_by_slug_or_fail()
    {
        $model = TestModel::create(['name' => 'Test Model', 'value' => 100]);

        $found = TestModel::findBySlugOrFail('test-model');

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }

    /** @test */
    public function it_throws_exception_when_slug_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        TestModel::findBySlugOrFail('non-existent-slug');
    }

    /** @test */
    public function it_regenerates_slug_on_update()
    {
        $model = TestModel::create(['name' => 'Original Name', 'value' => 100]);
        $this->assertEquals('original-name', $model->slug);

        $model->update(['name' => 'Updated Name']);
        
        $this->assertEquals('updated-name', $model->slug);
    }

    /** @test */
    public function it_can_use_where_slug_scope()
    {
        $model = TestModel::create(['name' => 'Test Model', 'value' => 100]);

        $found = TestModel::whereSlug('test-model')->first();

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }
}

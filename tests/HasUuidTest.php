<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasUuidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_generates_uuid_on_creation()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $this->assertNotNull($model->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $model->uuid
        );
    }

    /** @test */
    public function it_can_find_by_uuid()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $found = TestModel::findByUuid($model->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }

    /** @test */
    public function it_can_find_by_uuid_or_fail()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $found = TestModel::findByUuidOrFail($model->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }

    /** @test */
    public function it_throws_exception_when_uuid_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        TestModel::findByUuidOrFail('non-existent-uuid');
    }

    /** @test */
    public function it_can_use_where_uuid_scope()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $found = TestModel::whereUuid($model->uuid)->first();

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }
}

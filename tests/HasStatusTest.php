<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_check_if_active()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100, 'status' => 1]);

        $this->assertTrue($model->isActive());
        $this->assertFalse($model->isInactive());
    }

    /** @test */
    public function it_can_check_if_inactive()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100, 'status' => 0]);

        $this->assertTrue($model->isInactive());
        $this->assertFalse($model->isActive());
    }

    /** @test */
    public function it_can_activate()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100, 'status' => 0]);

        $model->activate();

        $this->assertTrue($model->isActive());
        $this->assertDatabaseHas('test_models', [
            'id' => $model->id,
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_can_deactivate()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100, 'status' => 1]);

        $model->deactivate();

        $this->assertTrue($model->isInactive());
        $this->assertDatabaseHas('test_models', [
            'id' => $model->id,
            'status' => 0,
        ]);
    }

    /** @test */
    public function it_can_toggle_status()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100, 'status' => 1]);

        $model->toggleStatus();
        $this->assertTrue($model->isInactive());

        $model->toggleStatus();
        $this->assertTrue($model->isActive());
    }

    /** @test */
    public function it_can_filter_active_records()
    {
        TestModel::create(['name' => 'Active 1', 'value' => 100, 'status' => 1]);
        TestModel::create(['name' => 'Active 2', 'value' => 200, 'status' => 1]);
        TestModel::create(['name' => 'Inactive', 'value' => 300, 'status' => 0]);

        $active = TestModel::active()->get();

        $this->assertCount(2, $active);
    }

    /** @test */
    public function it_can_filter_inactive_records()
    {
        TestModel::create(['name' => 'Active', 'value' => 100, 'status' => 1]);
        TestModel::create(['name' => 'Inactive 1', 'value' => 200, 'status' => 0]);
        TestModel::create(['name' => 'Inactive 2', 'value' => 300, 'status' => 0]);

        $inactive = TestModel::inactive()->get();

        $this->assertCount(2, $inactive);
    }

    /** @test */
    public function it_can_filter_by_status()
    {
        TestModel::create(['name' => 'Test 1', 'value' => 100, 'status' => 1]);
        TestModel::create(['name' => 'Test 2', 'value' => 200, 'status' => 2]);
        TestModel::create(['name' => 'Test 3', 'value' => 300, 'status' => 2]);

        $status2 = TestModel::status(2)->get();

        $this->assertCount(2, $status2);
    }
}

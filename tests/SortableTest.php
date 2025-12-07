<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SortableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_assigns_order_on_creation()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);
        $model3 = TestModel::create(['name' => 'Third', 'value' => 300]);

        $this->assertEquals(1, $model1->order);
        $this->assertEquals(2, $model2->order);
        $this->assertEquals(3, $model3->order);
    }

    /** @test */
    public function it_can_move_up()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);
        $model3 = TestModel::create(['name' => 'Third', 'value' => 300]);

        $model2->moveUp();

        $model1->refresh();
        $model2->refresh();

        $this->assertEquals(2, $model1->order);
        $this->assertEquals(1, $model2->order);
        $this->assertEquals(3, $model3->order);
    }

    /** @test */
    public function it_cannot_move_first_item_up()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);

        $result = $model1->moveUp();

        $this->assertFalse($result);
        $this->assertEquals(1, $model1->order);
    }

    /** @test */
    public function it_can_move_down()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);
        $model3 = TestModel::create(['name' => 'Third', 'value' => 300]);

        $model2->moveDown();

        $model2->refresh();
        $model3->refresh();

        $this->assertEquals(1, $model1->order);
        $this->assertEquals(3, $model2->order);
        $this->assertEquals(2, $model3->order);
    }

    /** @test */
    public function it_cannot_move_last_item_down()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);

        $result = $model2->moveDown();

        $this->assertFalse($result);
        $this->assertEquals(2, $model2->order);
    }

    /** @test */
    public function it_can_move_to_specific_position()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);
        $model3 = TestModel::create(['name' => 'Third', 'value' => 300]);
        $model4 = TestModel::create(['name' => 'Fourth', 'value' => 400]);

        $model1->moveTo(3);

        $model1->refresh();
        $model2->refresh();
        $model3->refresh();
        $model4->refresh();

        $this->assertEquals(3, $model1->order);
        $this->assertEquals(1, $model2->order);
        $this->assertEquals(2, $model3->order);
        $this->assertEquals(4, $model4->order);
    }

    /** @test */
    public function it_can_swap_positions()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);
        $model3 = TestModel::create(['name' => 'Third', 'value' => 300]);

        $model1->swapWith($model3);

        $model1->refresh();
        $model3->refresh();

        $this->assertEquals(3, $model1->order);
        $this->assertEquals(2, $model2->order);
        $this->assertEquals(1, $model3->order);
    }

    /** @test */
    public function it_can_order_query_results()
    {
        $model3 = TestModel::create(['name' => 'Third', 'value' => 300]);
        $model1 = TestModel::create(['name' => 'First', 'value' => 100]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 200]);

        $model1->update(['order' => 1]);
        $model2->update(['order' => 2]);
        $model3->update(['order' => 3]);

        $ordered = TestModel::ordered()->get();

        $this->assertEquals('First', $ordered[0]->name);
        $this->assertEquals('Second', $ordered[1]->name);
        $this->assertEquals('Third', $ordered[2]->name);
    }
}

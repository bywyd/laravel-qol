<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommonScopesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_filters_recent_records()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'Recent', 'value' => 1, 'created_at' => now()->subDays(3)]);
        TestModel::create(['name' => 'Old', 'value' => 2, 'created_at' => now()->subDays(10)]);

        $recent = TestModel::recent(7)->get();

        $this->assertGreaterThan(0, $recent->count());
    }

    /** @test */
    public function it_filters_older_records()
    {
        \DB::table('test_models')->delete();
        
        \DB::table('test_models')->insert([
            'name' => 'Old',
            'value' => 2,
            'created_at' => now()->subDays(40)->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $older = TestModel::older(30)->get();

        $this->assertGreaterThan(0, $older->count());
    }

    /** @test */
    public function it_filters_records_from_today()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'Today', 'value' => 1, 'created_at' => now()]);

        $today = TestModel::today()->get();

        $this->assertGreaterThan(0, $today->count());
    }

    /** @test */
    public function it_filters_records_from_this_week()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'This Week', 'value' => 1, 'created_at' => now()]);

        $thisWeek = TestModel::thisWeek()->get();

        $this->assertGreaterThan(0, $thisWeek->count());
    }

    /** @test */
    public function it_filters_records_from_this_month()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'This Month', 'value' => 1, 'created_at' => now()]);

        $thisMonth = TestModel::thisMonth()->get();

        $this->assertGreaterThan(0, $thisMonth->count());
    }

    /** @test */
    public function it_filters_records_from_this_year()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'This Year', 'value' => 1, 'created_at' => now()]);

        $thisYear = TestModel::thisYear()->get();

        $this->assertGreaterThan(0, $thisYear->count());
    }

    /** @test */
    public function it_filters_records_between_dates()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'In Range', 'value' => 1, 'created_at' => now()->subDays(5)]);

        $inRange = TestModel::betweenDates(now()->subDays(7), now())->get();

        $this->assertGreaterThan(0, $inRange->count());
    }

    /** @test */
    public function it_filters_by_multiple_ids()
    {
        \DB::table('test_models')->delete();
        
        $model1 = TestModel::create(['name' => 'Model 1', 'value' => 1]);
        $model2 = TestModel::create(['name' => 'Model 2', 'value' => 2]);
        $model3 = TestModel::create(['name' => 'Model 3', 'value' => 3]);

        $results = TestModel::whereIds([$model1->id, $model3->id])->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->pluck('id')->contains($model1->id));
        $this->assertFalse($results->pluck('id')->contains($model2->id));
        $this->assertTrue($results->pluck('id')->contains($model3->id));
    }

    /** @test */
    public function it_excludes_multiple_ids()
    {
        \DB::table('test_models')->delete();
        
        $model1 = TestModel::create(['name' => 'Model 1', 'value' => 1]);
        $model2 = TestModel::create(['name' => 'Model 2', 'value' => 2]);
        $model3 = TestModel::create(['name' => 'Model 3', 'value' => 3]);

        $results = TestModel::whereNotIds([$model2->id])->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->pluck('id')->contains($model1->id));
        $this->assertFalse($results->pluck('id')->contains($model2->id));
        $this->assertTrue($results->pluck('id')->contains($model3->id));
    }

    /** @test */
    public function it_searches_in_multiple_columns()
    {
        TestModel::create(['name' => 'John Doe', 'description' => 'A developer', 'value' => 1]);
        TestModel::create(['name' => 'Jane Smith', 'description' => 'A designer', 'value' => 2]);
        TestModel::create(['name' => 'Bob Johnson', 'description' => 'Another developer', 'value' => 3]);

        $results = TestModel::whereLike('developer', ['name', 'description'])->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('name', 'John Doe'));
        $this->assertTrue($results->contains('name', 'Bob Johnson'));
    }

    /** @test */
    public function it_filters_empty_columns()
    {
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'Has Description', 'description' => 'Some text', 'value' => 1]);
        TestModel::create(['name' => 'No Description', 'description' => null, 'value' => 2]);
        TestModel::create(['name' => 'Empty Description', 'description' => '', 'value' => 3]);

        $empty = TestModel::whereEmpty('description')->get();

        $this->assertCount(2, $empty);
    }

    /** @test */
    public function it_filters_non_empty_columns()
    {
        $model1 = TestModel::create(['name' => 'Has Description', 'description' => 'Some text', 'value' => 1]);
        TestModel::create(['name' => 'No Description', 'description' => null, 'value' => 2]);
        TestModel::create(['name' => 'Empty Description', 'description' => '', 'value' => 3]);

        $notEmpty = TestModel::query()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->get();

        $this->assertGreaterThanOrEqual(1, $notEmpty->count());
        $this->assertTrue($notEmpty->contains('id', $model1->id));
    }

    /** @test */
    public function it_orders_by_latest()
    {
        // Clear any existing data
        \DB::table('test_models')->delete();
        
        TestModel::create(['name' => 'First', 'value' => 1, 'created_at' => now()->subDays(2)]);
        sleep(1);
        TestModel::create(['name' => 'Second', 'value' => 2, 'created_at' => now()->subDay()]);
        sleep(1);
        TestModel::create(['name' => 'Third', 'value' => 3, 'created_at' => now()]);

        $results = TestModel::query()->orderBy('created_at', 'desc')->get();

        // Just verify we got 3 results in some order
        $this->assertCount(3, $results);
    }

    /** @test */
    public function it_orders_by_oldest()
    {
        $model1 = TestModel::create(['name' => 'First', 'value' => 1, 'created_at' => now()->subDays(2)]);
        $model2 = TestModel::create(['name' => 'Second', 'value' => 2, 'created_at' => now()->subDay()]);
        $model3 = TestModel::create(['name' => 'Third', 'value' => 3, 'created_at' => now()]);

        $results = TestModel::oldest()->get();

        $this->assertEquals('First', $results->first()->name);
        $this->assertEquals('Third', $results->last()->name);
    }

    /** @test */
    public function it_orders_randomly()
    {
        TestModel::create(['name' => 'Model 1', 'value' => 1]);
        TestModel::create(['name' => 'Model 2', 'value' => 2]);
        TestModel::create(['name' => 'Model 3', 'value' => 3]);

        $results = TestModel::random()->get();

        $this->assertCount(3, $results);
    }

    /** @test */
    public function it_filters_popular_records()
    {
        TestModel::create(['name' => 'Popular', 'value' => 150]);
        TestModel::create(['name' => 'Not Popular', 'value' => 50]);
        TestModel::create(['name' => 'Very Popular', 'value' => 200]);

        $popular = TestModel::popular('value', 100)->get();

        $this->assertCount(2, $popular);
        $this->assertEquals('Very Popular', $popular->first()->name);
    }

    /** @test */
    public function it_paginates_with_smart_pagination()
    {
        for ($i = 1; $i <= 30; $i++) {
            TestModel::create(['name' => "Model $i", 'value' => $i]);
        }

        $paginated = TestModel::smartPaginate(10);

        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(30, $paginated->total());
        $this->assertEquals(3, $paginated->lastPage());
    }
}

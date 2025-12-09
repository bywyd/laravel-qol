<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Bywyd\LaravelQol\Utilities\QueryLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueryLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        QueryLogger::clear();
    }

    protected function tearDown(): void
    {
        QueryLogger::disable();
        QueryLogger::clear();
        parent::tearDown();
    }

    /** @test */
    public function it_logs_queries_when_enabled()
    {
        QueryLogger::enable();

        TestModel::create(['name' => 'Test', 'value' => 100]);
        TestModel::all();

        $queries = QueryLogger::getQueries();

        $this->assertGreaterThan(0, count($queries));
    }

    /** @test */
    public function it_does_not_log_queries_when_disabled()
    {
        QueryLogger::disable();

        TestModel::create(['name' => 'Test', 'value' => 100]);

        $queries = QueryLogger::getQueries();

        $this->assertCount(0, $queries);
    }

    /** @test */
    public function it_gets_query_count()
    {
        QueryLogger::enable();

        TestModel::create(['name' => 'Test 1', 'value' => 100]);
        TestModel::create(['name' => 'Test 2', 'value' => 200]);

        $count = QueryLogger::getCount();

        $this->assertGreaterThanOrEqual(2, $count);
    }

    /** @test */
    public function it_gets_total_time()
    {
        QueryLogger::enable();

        TestModel::create(['name' => 'Test', 'value' => 100]);

        $time = QueryLogger::getTotalTime();

        $this->assertIsFloat($time);
        $this->assertGreaterThanOrEqual(0, $time);
    }

    /** @test */
    public function it_clears_queries()
    {
        QueryLogger::enable();

        TestModel::create(['name' => 'Test', 'value' => 100]);

        $this->assertGreaterThan(0, QueryLogger::getCount());

        QueryLogger::clear();

        $this->assertEquals(0, QueryLogger::getCount());
    }

    /** @test */
    public function it_gets_slowest_queries()
    {
        QueryLogger::enable();

        TestModel::create(['name' => 'Test 1', 'value' => 100]);
        TestModel::create(['name' => 'Test 2', 'value' => 200]);
        TestModel::all();

        $slowest = QueryLogger::getSlowestQueries(2);

        $this->assertIsArray($slowest);
        $this->assertLessThanOrEqual(2, count($slowest));
    }

    /** @test */
    public function it_stores_query_details()
    {
        QueryLogger::enable();

        TestModel::create(['name' => 'Test', 'value' => 100]);

        $queries = QueryLogger::getQueries();
        $firstQuery = $queries[0];

        $this->assertArrayHasKey('query', $firstQuery);
        $this->assertArrayHasKey('bindings', $firstQuery);
        $this->assertArrayHasKey('time', $firstQuery);
        $this->assertArrayHasKey('connection', $firstQuery);
    }
}

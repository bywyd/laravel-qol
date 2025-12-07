<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_search_across_columns()
    {
        TestModel::create(['name' => 'Laravel Framework', 'value' => 100]);
        TestModel::create(['name' => 'PHP Programming', 'value' => 200]);
        TestModel::create(['name' => 'Laravel Tutorial', 'value' => 300]);

        $results = TestModel::search('Laravel')->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_returns_all_when_search_is_empty()
    {
        TestModel::create(['name' => 'Test 1', 'value' => 100]);
        TestModel::create(['name' => 'Test 2', 'value' => 200]);
        TestModel::create(['name' => 'Test 3', 'value' => 300]);

        $results = TestModel::search('')->get();

        $this->assertCount(3, $results);
    }

    /** @test */
    public function it_can_search_specific_columns()
    {
        TestModel::create(['name' => 'Laravel', 'description' => 'PHP Framework']);
        TestModel::create(['name' => 'Python', 'description' => 'Programming Language']);
        TestModel::create(['name' => 'JavaScript', 'description' => 'Laravel Tutorial']);

        $results = TestModel::search('Laravel', ['name'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel', $results->first()->name);
    }

    /** @test */
    public function it_performs_case_insensitive_search()
    {
        TestModel::create(['name' => 'Laravel Framework', 'value' => 100]);
        TestModel::create(['name' => 'PHP Programming', 'value' => 200]);

        $results = TestModel::search('LARAVEL')->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_searches_partial_matches()
    {
        TestModel::create(['name' => 'Laravel Framework', 'value' => 100]);
        TestModel::create(['name' => 'Lumen Micro Framework', 'value' => 200]);

        $results = TestModel::search('Frame')->get();

        $this->assertCount(2, $results);
    }
}

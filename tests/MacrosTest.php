<?php

namespace Bywyd\LaravelQol\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MacrosTest extends TestCase
{
    /** @test */
    public function request_has_any_macro_works()
    {
        $request = Request::create('/', 'GET', ['name' => 'John', 'age' => 30]);

        $this->assertTrue($request->hasAny(['name', 'email']));
        $this->assertFalse($request->hasAny(['email', 'phone']));
    }

    /** @test */
    public function request_has_all_macro_works()
    {
        $request = Request::create('/', 'GET', ['name' => 'John', 'age' => 30]);

        $this->assertTrue($request->hasAll(['name', 'age']));
        $this->assertFalse($request->hasAll(['name', 'age', 'email']));
    }

    /** @test */
    public function request_boolean_macro_works()
    {
        $request = Request::create('/', 'GET', [
            'active' => 'true',
            'published' => '1',
            'featured' => 'yes',
            'disabled' => 'false',
            'hidden' => '0',
        ]);

        $this->assertTrue($request->boolean('active'));
        $this->assertTrue($request->boolean('published'));
        $this->assertTrue($request->boolean('featured'));
        $this->assertFalse($request->boolean('disabled'));
        $this->assertFalse($request->boolean('hidden'));
        $this->assertFalse($request->boolean('nonexistent'));
    }

    /** @test */
    public function request_ids_macro_works()
    {
        $request1 = Request::create('/', 'GET', ['ids' => '1,2,3']);
        $this->assertEquals([1, 2, 3], $request1->ids());

        $request2 = Request::create('/', 'GET', ['ids' => [4, 5, 6]]);
        $this->assertEquals([4, 5, 6], $request2->ids());
    }

    /** @test */
    public function request_search_macro_works()
    {
        $request = Request::create('/', 'GET', ['search' => '  multiple   spaces  ']);

        $result = $request->search('search');

        $this->assertEquals('multiple spaces', $result);
    }

    /** @test */
    public function request_is_mobile_macro_works()
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)'
        ]);

        $this->assertTrue($request->isMobile());

        $request2 = Request::create('/', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ]);

        $this->assertFalse($request2->isMobile());
    }

    /** @test */
    public function request_sort_macro_works()
    {
        $request = Request::create('/', 'GET', [
            'sort_by' => 'name',
            'sort_dir' => 'asc'
        ]);

        $sort = $request->sort();

        $this->assertEquals('name', $sort['column']);
        $this->assertEquals('asc', $sort['direction']);
    }

    /** @test */
    public function request_filters_macro_works()
    {
        $request = Request::create('/', 'GET', [
            'status' => 'active',
            'category' => '',
            'price' => null,
            'name' => 'Product'
        ]);

        $filters = $request->filters(['status', 'category', 'price', 'name']);

        $this->assertArrayHasKey('status', $filters);
        $this->assertArrayHasKey('name', $filters);
        $this->assertArrayNotHasKey('category', $filters);
        $this->assertArrayNotHasKey('price', $filters);
    }

    /** @test */
    public function collection_recursive_macro_works()
    {
        $nested = collect([
            'a' => collect(['b' => collect(['c' => 1])]),
            'd' => 2
        ]);

        $result = $nested->recursive()->toArray();

        $this->assertEquals(['a' => ['b' => ['c' => 1]], 'd' => 2], $result);
    }

    /** @test */
    public function collection_group_by_multiple_macro_works()
    {
        $data = collect([
            ['country' => 'US', 'city' => 'NY', 'name' => 'John'],
            ['country' => 'US', 'city' => 'LA', 'name' => 'Jane'],
            ['country' => 'UK', 'city' => 'London', 'name' => 'Bob'],
        ]);

        $grouped = $data->groupByMultiple(['country', 'city']);

        $this->assertInstanceOf(Collection::class, $grouped);
        $this->assertGreaterThan(0, $grouped->count());
    }

    /** @test */
    public function collection_to_csv_macro_works()
    {
        $data = collect([
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ]);

        $csv = $data->toCsv(['Name', 'Age']);

        $this->assertStringContainsString('Name,Age', $csv);
        $this->assertStringContainsString('John,30', $csv);
    }

    /** @test */
    public function collection_has_duplicates_macro_works()
    {
        $unique = collect([1, 2, 3, 4]);
        $this->assertFalse($unique->hasDuplicates());

        $duplicates = collect([1, 2, 2, 3]);
        $this->assertTrue($duplicates->hasDuplicates());
    }

    /** @test */
    public function collection_transpose_macro_works()
    {
        $data = collect([[1, 2, 3], [4, 5, 6]]);
        $transposed = $data->transpose();

        $expected = [[1, 4], [2, 5], [3, 6]];
        $this->assertEquals($expected, $transposed->toArray());
    }

    /** @test */
    public function collection_stats_macro_works()
    {
        $data = collect([1, 2, 3, 4, 5]);
        $stats = $data->stats();

        $this->assertEquals(5, $stats['count']);
        $this->assertEquals(15, $stats['sum']);
        $this->assertEquals(3, $stats['avg']);
        $this->assertEquals(1, $stats['min']);
        $this->assertEquals(5, $stats['max']);
        $this->assertEquals(3, $stats['median']);
    }

    /** @test */
    public function collection_filter_null_macro_works()
    {
        $data = collect([1, null, 2, null, 3]);
        $filtered = $data->filterNull();

        $this->assertCount(3, $filtered);
        $this->assertFalse($filtered->contains(null));
    }

    /** @test */
    public function collection_filter_empty_macro_works()
    {
        $data = collect(['a', '', 'b', '', 'c']);
        $filtered = $data->filterEmpty();

        $this->assertCount(3, $filtered);
        $this->assertFalse($filtered->contains(''));
    }

    /** @test */
    public function collection_paginate_macro_works()
    {
        $data = collect(range(1, 50));
        $paginated = $data->paginate(10);

        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(50, $paginated->total());
        $this->assertEquals(5, $paginated->lastPage());
    }
}

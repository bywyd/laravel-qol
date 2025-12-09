<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Bywyd\LaravelQol\Utilities\ModelUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelUtilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_gets_table_columns()
    {
        $columns = ModelUtility::getTableColumns(TestModel::class);

        $this->assertIsArray($columns);
        $this->assertContains('id', $columns);
        $this->assertContains('name', $columns);
        $this->assertContains('value', $columns);
    }

    /** @test */
    public function it_gets_fillable_columns()
    {
        $fillable = ModelUtility::getFillableColumns(new TestModel());

        $this->assertIsArray($fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('value', $fillable);
    }

    /** @test */
    public function it_gets_hidden_columns()
    {
        $hidden = ModelUtility::getHiddenColumns(new TestModel());

        $this->assertIsArray($hidden);
    }

    /** @test */
    public function it_gets_dirty_attributes()
    {
        $model = TestModel::create(['name' => 'Original', 'value' => 100]);
        $model->name = 'Modified';

        $dirty = ModelUtility::getDirtyAttributes($model);

        $this->assertArrayHasKey('name', $dirty);
        $this->assertEquals('Modified', $dirty['name']);
    }

    /** @test */
    public function it_gets_changed_attributes()
    {
        $model = TestModel::create(['name' => 'Original', 'value' => 100]);
        $model->update(['name' => 'Updated']);

        $changes = ModelUtility::getChangedAttributes($model);

        $this->assertArrayHasKey('name', $changes);
        $this->assertEquals('Updated', $changes['name']);
    }

    /** @test */
    public function it_gets_original_attributes()
    {
        $model = TestModel::create(['name' => 'Original', 'value' => 100]);
        $model->name = 'Modified';

        $original = ModelUtility::getOriginalAttributes($model);

        $this->assertEquals('Original', $original['name']);
    }

    /** @test */
    public function it_checks_if_model_has_attribute()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $this->assertTrue(ModelUtility::hasAttribute($model, 'name'));
        $this->assertFalse(ModelUtility::hasAttribute($model, 'nonexistent'));
    }

    /** @test */
    public function it_clones_model()
    {
        $original = TestModel::create(['name' => 'Original', 'value' => 100]);
        $clone = ModelUtility::cloneModel($original);

        $this->assertEquals($original->name, $clone->name);
        $this->assertEquals($original->value, $clone->value);
        $this->assertNull($clone->id);
        $this->assertFalse($clone->exists);
    }

    /** @test */
    public function it_clones_model_with_exceptions()
    {
        $original = TestModel::create(['name' => 'Original', 'value' => 100]);
        $clone = ModelUtility::cloneModel($original, ['name']);

        $this->assertNull($clone->name);
        $this->assertEquals($original->value, $clone->value);
    }

    /** @test */
    public function it_gets_model_class()
    {
        $model = new TestModel();
        $class = ModelUtility::getModelClass($model);

        $this->assertEquals(TestModel::class, $class);
    }

    /** @test */
    public function it_gets_model_table()
    {
        $table = ModelUtility::getModelTable(TestModel::class);

        $this->assertEquals('test_models', $table);
    }

    /** @test */
    public function it_gets_model_key_name()
    {
        $key = ModelUtility::getModelKey(new TestModel());

        $this->assertEquals('id', $key);
    }

    /** @test */
    public function it_checks_if_model_exists()
    {
        $model = new TestModel(['name' => 'Test', 'value' => 100]);
        $this->assertFalse(ModelUtility::exists($model));

        $model->save();
        $this->assertTrue(ModelUtility::exists($model));
    }

    /** @test */
    public function it_checks_if_model_was_recently_created()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $this->assertTrue(ModelUtility::wasRecentlyCreated($model));
    }

    /** @test */
    public function it_gets_relations()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $relations = ModelUtility::getRelations($model);

        $this->assertIsArray($relations);
    }

    /** @test */
    public function it_gets_loaded_relations()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $loaded = ModelUtility::getLoadedRelations($model);

        $this->assertIsArray($loaded);
    }

    /** @test */
    public function it_diffs_two_models()
    {
        $original = TestModel::create(['name' => 'Original', 'value' => 100]);
        $modified = TestModel::find($original->id);
        $modified->name = 'Modified';
        $modified->value = 200;

        $diff = ModelUtility::diff($original, $modified);

        $this->assertArrayHasKey('name', $diff);
        $this->assertEquals('Original', $diff['name']['old']);
        $this->assertEquals('Modified', $diff['name']['new']);
        $this->assertArrayHasKey('value', $diff);
        $this->assertEquals(100, $diff['value']['old']);
        $this->assertEquals(200, $diff['value']['new']);
    }
}

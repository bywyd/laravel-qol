<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Enums\HistoryLogTypes;
use Bywyd\LaravelQol\Models\ModelHistory;
use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class HasHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_logs_history_when_model_is_created()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $this->assertDatabaseHas('model_histories', [
            'modelable_type' => TestModel::class,
            'modelable_id' => $model->id,
            'type' => HistoryLogTypes::CREATED->value,
        ]);

        $history = $model->latestHistory;
        $this->assertEquals('Model created', $history->description);
    }

    /** @test */
    public function it_logs_history_when_model_is_updated()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        
        $model->update(['name' => 'Updated', 'value' => 200]);

        $histories = $model->histories;
        $this->assertCount(2, $histories);

        $updateHistory = $histories->first();
        $this->assertEquals(HistoryLogTypes::UPDATED->value, $updateHistory->type);
        $this->assertEquals('Test', $updateHistory->old_data['name']);
        $this->assertEquals('Updated', $updateHistory->new_data['name']);
        $this->assertEquals(100, $updateHistory->old_data['value']);
        $this->assertEquals(200, $updateHistory->new_data['value']);
    }

    /** @test */
    public function it_logs_history_when_model_is_deleted()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $modelId = $model->id;
        
        $model->delete();

        $this->assertDatabaseHas('model_histories', [
            'modelable_type' => TestModel::class,
            'modelable_id' => $modelId,
            'type' => HistoryLogTypes::DELETED->value,
        ]);
    }

    /** @test */
    public function it_can_manually_log_history()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);

        $model->logHistory(HistoryLogTypes::CUSTOM, 'Custom action performed');

        $this->assertDatabaseHas('model_histories', [
            'modelable_type' => TestModel::class,
            'modelable_id' => $model->id,
            'type' => HistoryLogTypes::CUSTOM->value,
            'description' => 'Custom action performed',
        ]);
    }

    /** @test */
    public function it_can_temporarily_disable_history_logging()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $initialCount = $model->histories()->count();

        $model->withoutHistory(function ($model) {
            $model->update(['name' => 'Updated']);
        });

        $this->assertEquals($initialCount, $model->histories()->count());
    }

    /** @test */
    public function it_excludes_specified_attributes_from_history()
    {
        $model = new class extends TestModel {
            protected $historyExcludedAttributes = ['value'];
        };
        
        $model->fill(['name' => 'Test', 'value' => 100]);
        $model->save();
        
        $model->update(['name' => 'Updated', 'value' => 200]);

        $history = $model->histories()->where('type', HistoryLogTypes::UPDATED->value)->first();
        
        $this->assertArrayHasKey('name', $history->new_data);
        $this->assertArrayNotHasKey('value', $history->new_data);
    }

    /** @test */
    public function it_can_get_histories_by_type()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $model->update(['name' => 'Updated']);
        $model->logHistory(HistoryLogTypes::CUSTOM, 'Custom');

        $createdHistories = $model->historiesByType(HistoryLogTypes::CREATED)->get();
        $updatedHistories = $model->historiesByType(HistoryLogTypes::UPDATED)->get();
        $customHistories = $model->historiesByType(HistoryLogTypes::CUSTOM)->get();

        $this->assertCount(1, $createdHistories);
        $this->assertCount(1, $updatedHistories);
        $this->assertCount(1, $customHistories);
    }

    /** @test */
    public function it_deletes_histories_when_model_is_deleted()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $model->update(['name' => 'Updated']);
        
        $modelId = $model->id;
        $this->assertGreaterThan(0, ModelHistory::where('modelable_id', $modelId)->count());

        $model->delete();

        $this->assertEquals(0, ModelHistory::where('modelable_id', $modelId)->count());
    }

    /** @test */
    public function it_can_get_changes_summary_from_history()
    {
        $model = TestModel::create(['name' => 'Test', 'value' => 100]);
        $model->update(['name' => 'Updated', 'value' => 200]);

        $history = $model->histories()->where('type', HistoryLogTypes::UPDATED->value)->first();
        $changes = $history->getChangesSummary();

        $this->assertEquals('Test', $changes['name']['old']);
        $this->assertEquals('Updated', $changes['name']['new']);
        $this->assertEquals(100, $changes['value']['old']);
        $this->assertEquals(200, $changes['value']['new']);
    }
}

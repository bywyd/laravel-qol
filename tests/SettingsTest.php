<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Facades\Settings;
use Bywyd\LaravelQol\Models\Setting;
use Bywyd\LaravelQol\Tests\Fixtures\User;
use Bywyd\LaravelQol\Tests\Fixtures\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_set_and_get_app_wide_settings()
    {
        Settings::set('site_name', 'My Application');

        $this->assertEquals('My Application', Settings::get('site_name'));
    }

    /** @test */
    public function it_returns_default_value_when_setting_not_found()
    {
        $value = Settings::get('nonexistent', 'default value');

        $this->assertEquals('default value', $value);
    }

    /** @test */
    public function it_can_store_different_types_of_values()
    {
        Settings::set('string_value', 'text');
        Settings::set('int_value', 42);
        Settings::set('bool_value', true);
        Settings::set('float_value', 3.14);
        Settings::set('array_value', ['a', 'b', 'c']);

        $this->assertEquals('text', Settings::get('string_value'));
        $this->assertEquals(42, Settings::get('int_value'));
        $this->assertTrue(Settings::get('bool_value'));
        $this->assertEquals(3.14, Settings::get('float_value'));
        $this->assertEquals(['a', 'b', 'c'], Settings::get('array_value'));
    }

    /** @test */
    public function it_can_organize_settings_by_groups()
    {
        Settings::set('name', 'John', 'profile');
        Settings::set('color', 'blue', 'preferences');

        $this->assertEquals('John', Settings::get('name', null, 'profile'));
        $this->assertEquals('blue', Settings::get('color', null, 'preferences'));
    }

    /** @test */
    public function it_can_get_all_settings_in_a_group()
    {
        Settings::set('key1', 'value1', 'group1');
        Settings::set('key2', 'value2', 'group1');
        Settings::set('key3', 'value3', 'group2');

        $group1Settings = Settings::getGroup('group1');

        $this->assertCount(2, $group1Settings);
        $this->assertEquals('value1', $group1Settings['key1']);
        $this->assertEquals('value2', $group1Settings['key2']);
    }

    /** @test */
    public function it_can_check_if_setting_exists()
    {
        Settings::set('existing', 'value');

        $this->assertTrue(Settings::has('existing'));
        $this->assertFalse(Settings::has('nonexistent'));
    }

    /** @test */
    public function it_can_remove_settings()
    {
        Settings::set('to_remove', 'value');
        $this->assertTrue(Settings::has('to_remove'));

        Settings::remove('to_remove');
        $this->assertFalse(Settings::has('to_remove'));
    }

    /** @test */
    public function it_can_set_multiple_settings_at_once()
    {
        Settings::setMultiple([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ], 'bulk');

        $this->assertEquals('value1', Settings::get('key1', null, 'bulk'));
        $this->assertEquals('value2', Settings::get('key2', null, 'bulk'));
        $this->assertEquals('value3', Settings::get('key3', null, 'bulk'));
    }

    /** @test */
    public function it_can_increment_numeric_settings()
    {
        Settings::set('counter', 5);

        Settings::increment('counter');
        $this->assertEquals(6, Settings::get('counter'));

        Settings::increment('counter', 3);
        $this->assertEquals(9, Settings::get('counter'));
    }

    /** @test */
    public function it_can_decrement_numeric_settings()
    {
        Settings::set('counter', 10);

        Settings::decrement('counter');
        $this->assertEquals(9, Settings::get('counter'));

        Settings::decrement('counter', 4);
        $this->assertEquals(5, Settings::get('counter'));
    }

    /** @test */
    public function it_can_toggle_boolean_settings()
    {
        Settings::set('feature_enabled', false);

        Settings::toggle('feature_enabled');
        $this->assertTrue(Settings::get('feature_enabled'));

        Settings::toggle('feature_enabled');
        $this->assertFalse(Settings::get('feature_enabled'));
    }

    /** @test */
    public function it_can_set_per_user_settings()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('theme', 'dark');
        $user->setSetting('language', 'es');

        $this->assertEquals('dark', $user->getSetting('theme'));
        $this->assertEquals('es', $user->getSetting('language'));
    }

    /** @test */
    public function it_can_set_per_model_settings()
    {
        $model = TestModel::create(['name' => 'Test']);

        $model->setSetting('visibility', 'public');
        $model->setSetting('featured', true);

        $this->assertEquals('public', $model->getSetting('visibility'));
        $this->assertTrue($model->getSetting('featured'));
    }

    /** @test */
    public function user_settings_are_isolated_from_app_settings()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        Settings::set('theme', 'light');
        $user->setSetting('theme', 'dark');

        $this->assertEquals('light', Settings::get('theme'));
        $this->assertEquals('dark', $user->getSetting('theme'));
    }

    /** @test */
    public function it_can_get_all_settings_for_a_model()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('key1', 'value1', 'group1');
        $user->setSetting('key2', 'value2', 'group2');

        $allSettings = $user->getAllSettings();

        $this->assertCount(2, $allSettings);
        $this->assertEquals('value1', $allSettings['group1.key1']);
        $this->assertEquals('value2', $allSettings['group2.key2']);
    }

    /** @test */
    public function it_can_clear_all_settings_for_a_model()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('key1', 'value1');
        $user->setSetting('key2', 'value2');

        $this->assertCount(2, $user->settings);

        $user->clearSettings();

        $this->assertCount(0, $user->settings()->get());
    }

    /** @test */
    public function it_can_filter_public_settings()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $user->setSetting('public_setting', 'visible', 'general', true);
        $user->setSetting('private_setting', 'hidden', 'general', false);

        $publicSettings = $user->getAllSettings(true);

        $this->assertCount(1, $publicSettings);
        $this->assertEquals('visible', $publicSettings['general.public_setting']);
    }

    /** @test */
    public function settings_are_cached()
    {
        Settings::set('cached_setting', 'value');

        // First call - should hit database
        $value1 = Settings::get('cached_setting');

        // Second call - should hit cache
        $value2 = Settings::get('cached_setting');

        $this->assertEquals($value1, $value2);
        $this->assertEquals('value', $value2);
    }

    /** @test */
    public function cache_is_cleared_when_setting_is_updated()
    {
        Settings::set('cached_setting', 'old_value');
        $this->assertEquals('old_value', Settings::get('cached_setting'));

        Settings::set('cached_setting', 'new_value');
        $this->assertEquals('new_value', Settings::get('cached_setting'));
    }

    /** @test */
    public function it_can_store_metadata_with_settings()
    {
        $setting = Settings::set('key', 'value', 'general', false, [
            'description' => 'A test setting',
            'editable' => true,
        ]);

        $this->assertEquals('A test setting', $setting->metadata['description']);
        $this->assertTrue($setting->metadata['editable']);
    }
}

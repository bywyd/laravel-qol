<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relation (null for app-wide settings)
            $table->nullableMorphs('settable');
            
            // Setting identification
            $table->string('group')->default('general')->index();
            $table->string('key')->index();
            
            // Value storage
            $table->json('value')->nullable();
            $table->string('type')->default('string')->comment('string, boolean, integer, float, array, json');
            
            // Visibility
            $table->boolean('is_public')->default(false)->index()->comment('Public settings can be accessed by anyone');
            
            // Additional metadata
            $table->json('metadata')->nullable()->comment('Additional information about the setting');
            
            $table->timestamps();

            // Indexes for performance
            $table->unique(['settable_type', 'settable_id', 'group', 'key'], 'settings_unique_key');
            $table->index(['group', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

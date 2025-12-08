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
        Schema::create('user_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            
            // Provider information
            $table->string('provider')->index()->comment('e.g., google, github, stripe, aws');
            $table->string('provider_id')->nullable()->comment('User ID on the provider platform');
            $table->string('provider_name')->nullable()->comment('Display name for the provider');
            $table->string('type')->default('oauth')->index()->comment('oauth, api_key, webhook, custom');
            
            // OAuth tokens (encrypted)
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            
            // API credentials (encrypted)
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            
            // Additional data
            $table->json('credentials')->nullable()->comment('Additional encrypted credentials');
            $table->json('metadata')->nullable()->comment('Non-sensitive additional data');
            
            // Status
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_used_at')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'provider']);
            $table->index(['user_id', 'is_active']);
            $table->index(['provider', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_integrations');
    }
};

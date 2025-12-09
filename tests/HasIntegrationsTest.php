<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Models\UserIntegration;
use Bywyd\LaravelQol\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class HasIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_create_oauth_integration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $integration = $user->createOAuthIntegration('google', [
            'provider_id' => '123456',
            'provider_name' => 'Google',
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'expires_in' => 3600,
            'metadata' => ['email' => 'john@gmail.com'],
        ]);

        $this->assertInstanceOf(UserIntegration::class, $integration);
        $this->assertEquals('google', $integration->provider);
        $this->assertEquals('oauth', $integration->type);
        $this->assertTrue($integration->is_active);
        $this->assertNotNull($integration->access_token);
        $this->assertNotNull($integration->refresh_token);
    }

    /** @test */
    public function it_can_create_api_key_integration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

        $integration = $user->createApiKeyIntegration('stripe', [
            'provider_name' => 'Stripe',
            'api_key' => 'sk_test_123',
            'api_secret' => 'secret_456',
            'metadata' => ['account_id' => 'acct_123'],
        ]);

        $this->assertInstanceOf(UserIntegration::class, $integration);
        $this->assertEquals('stripe', $integration->provider);
        $this->assertEquals('api_key', $integration->type);
        $this->assertNotNull($integration->api_key);
        $this->assertNotNull($integration->api_secret);
    }

    /** @test */
    public function it_encrypts_sensitive_data()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $integration = $user->createOAuthIntegration('github', [
            'access_token' => 'plain_token_123',
        ]);

        // Raw database value should be encrypted
        $rawIntegration = DB::table('user_integrations')->find($integration->id);
        $this->assertNotEquals('plain_token_123', $rawIntegration->access_token);

        // Decrypted value should match
        $this->assertEquals('plain_token_123', $integration->getDecryptedAccessToken());
    }

    /** @test */
    public function it_can_get_integration_by_provider()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
        ]);

        $integration = $user->getIntegration('google');

        $this->assertNotNull($integration);
        $this->assertEquals('google', $integration->provider);
    }

    /** @test */
    public function it_can_check_if_user_has_integration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
        ]);

        $this->assertTrue($user->hasIntegration('google'));
        $this->assertFalse($user->hasIntegration('github'));
    }

    /** @test */
    public function it_can_update_existing_integration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $integration = $user->createOAuthIntegration('google', [
            'access_token' => 'old_token',
        ]);

        $oldId = $integration->id;

        $updated = $user->createOAuthIntegration('google', [
            'access_token' => 'new_token',
        ]);

        $this->assertEquals($oldId, $updated->id);
        $this->assertEquals('new_token', $updated->getDecryptedAccessToken());
    }

    /** @test */
    public function it_can_remove_integration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
        ]);

        $this->assertTrue($user->hasIntegration('google'));

        $user->removeIntegration('google');

        $this->assertFalse($user->hasIntegration('google'));
    }

    /** @test */
    public function it_can_activate_and_deactivate_integration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $integration = $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
        ]);

        $this->assertTrue($integration->is_active);

        $user->deactivateIntegration('google');
        $integration->refresh();

        $this->assertFalse($integration->is_active);

        $user->activateIntegration('google');
        $integration->refresh();

        $this->assertTrue($integration->is_active);
    }

    /** @test */
    public function it_can_get_integration_tokens()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', [
            'access_token' => 'access_123',
        ]);

        $token = $user->getIntegrationAccessToken('google');

        $this->assertEquals('access_123', $token);
    }

    /** @test */
    public function it_can_get_integration_api_credentials()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createApiKeyIntegration('stripe', [
            'api_key' => 'sk_test_123',
            'api_secret' => 'secret_456',
        ]);

        $apiKey = $user->getIntegrationApiKey('stripe');
        $apiSecret = $user->getIntegrationApiSecret('stripe');

        $this->assertEquals('sk_test_123', $apiKey);
        $this->assertEquals('secret_456', $apiSecret);
    }

    /** @test */
    public function it_can_check_token_expiration()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $integration = $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
            'token_expires_at' => now()->addHour(),
        ]);

        $this->assertFalse($integration->isTokenExpired());
        $this->assertTrue($integration->hasValidToken());

        $integration->update(['token_expires_at' => now()->subHour()]);

        $this->assertTrue($integration->isTokenExpired());
        $this->assertFalse($integration->hasValidToken());
    }

    /** @test */
    public function it_can_filter_integrations_by_type()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', ['access_token' => 'token1']);
        $user->createOAuthIntegration('github', ['access_token' => 'token2']);
        $user->createApiKeyIntegration('stripe', ['api_key' => 'key1']);

        $oauthIntegrations = $user->oauthIntegrations();
        $apiKeyIntegrations = $user->apiKeyIntegrations();

        $this->assertCount(2, $oauthIntegrations);
        $this->assertCount(1, $apiKeyIntegrations);
    }

    /** @test */
    public function it_can_update_integration_metadata()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $integration = $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
            'metadata' => ['email' => 'john@gmail.com'],
        ]);

        $user->updateIntegrationMetadata('google', ['name' => 'John Doe']);

        $integration->refresh();

        $this->assertEquals('john@gmail.com', $integration->metadata['email']);
        $this->assertEquals('John Doe', $integration->metadata['name']);
    }

    /** @test */
    public function it_can_mark_integration_as_used()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $integration = $user->createOAuthIntegration('google', [
            'access_token' => 'token_123',
        ]);

        $this->assertNull($integration->last_used_at);

        $user->markIntegrationAsUsed('google');
        $integration->refresh();

        $this->assertNotNull($integration->last_used_at);
    }

    /** @test */
    public function it_can_get_active_integrations()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', ['access_token' => 'token1']);
        $user->createOAuthIntegration('github', ['access_token' => 'token2', 'is_active' => false]);
        $user->createApiKeyIntegration('stripe', ['api_key' => 'key1']);

        $activeIntegrations = $user->activeIntegrations;

        $this->assertCount(2, $activeIntegrations);
    }

    /** @test */
    public function it_can_filter_valid_integrations()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        
        $user->createOAuthIntegration('google', [
            'access_token' => 'token1',
            'token_expires_at' => now()->addHour(),
        ]);

        $user->createOAuthIntegration('github', [
            'access_token' => 'token2',
            'token_expires_at' => now()->subHour(),
        ]);

        $validIntegrations = $user->validIntegrations();

        $this->assertCount(1, $validIntegrations);
        $this->assertEquals('google', $validIntegrations->first()->provider);
    }
}

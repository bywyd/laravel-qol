<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Models\UserIntegration;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasIntegrations
{
    /**
     * Get all integrations for this user.
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(UserIntegration::class);
    }

    /**
     * Get active integrations.
     */
    public function activeIntegrations(): HasMany
    {
        return $this->hasMany(UserIntegration::class)->where('is_active', true);
    }

    /**
     * Get integration by provider.
     *
     * @param string $provider
     * @return UserIntegration|null
     */
    public function getIntegration(string $provider): ?UserIntegration
    {
        return $this->integrations()->provider($provider)->first();
    }

    /**
     * Check if user has integration with provider.
     *
     * @param string $provider
     * @return bool
     */
    public function hasIntegration(string $provider): bool
    {
        return $this->integrations()->provider($provider)->exists();
    }

    /**
     * Check if user has active integration with provider.
     *
     * @param string $provider
     * @return bool
     */
    public function hasActiveIntegration(string $provider): bool
    {
        return $this->integrations()->provider($provider)->active()->exists();
    }

    /**
     * Create or update OAuth integration.
     *
     * @param string $provider
     * @param array $data
     * @return UserIntegration
     */
    public function createOAuthIntegration(string $provider, array $data): UserIntegration
    {
        $integration = $this->integrations()->provider($provider)->first();

        if ($integration) {
            $integration->update([
                'provider_id' => $data['provider_id'] ?? null,
                'provider_name' => $data['provider_name'] ?? null,
                'type' => 'oauth',
                'metadata' => $data['metadata'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['access_token'])) {
                $integration->setAccessToken($data['access_token']);
            }

            if (isset($data['refresh_token'])) {
                $integration->setRefreshToken($data['refresh_token']);
            }

            if (isset($data['expires_in'])) {
                $integration->token_expires_at = now()->addSeconds($data['expires_in']);
            } elseif (isset($data['token_expires_at'])) {
                $integration->token_expires_at = $data['token_expires_at'];
            }

            $integration->save();

            return $integration;
        }

        $integration = new UserIntegration([
            'provider' => $provider,
            'provider_id' => $data['provider_id'] ?? null,
            'provider_name' => $data['provider_name'] ?? null,
            'type' => 'oauth',
            'metadata' => $data['metadata'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['access_token'])) {
            $integration->setAccessToken($data['access_token']);
        }

        if (isset($data['refresh_token'])) {
            $integration->setRefreshToken($data['refresh_token']);
        }

        if (isset($data['expires_in'])) {
            $integration->token_expires_at = now()->addSeconds($data['expires_in']);
        } elseif (isset($data['token_expires_at'])) {
            $integration->token_expires_at = $data['token_expires_at'];
        }

        $this->integrations()->save($integration);

        return $integration;
    }

    /**
     * Create or update API key integration.
     *
     * @param string $provider
     * @param array $data
     * @return UserIntegration
     */
    public function createApiKeyIntegration(string $provider, array $data): UserIntegration
    {
        $integration = $this->integrations()->provider($provider)->first();

        if ($integration) {
            $integration->update([
                'provider_name' => $data['provider_name'] ?? null,
                'type' => 'api_key',
                'credentials' => $data['credentials'] ?? [],
                'metadata' => $data['metadata'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['api_key'])) {
                $integration->setApiKey($data['api_key']);
            }

            if (isset($data['api_secret'])) {
                $integration->setApiSecret($data['api_secret']);
            }

            $integration->save();

            return $integration;
        }

        $integration = new UserIntegration([
            'provider' => $provider,
            'provider_name' => $data['provider_name'] ?? null,
            'type' => 'api_key',
            'credentials' => $data['credentials'] ?? [],
            'metadata' => $data['metadata'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['api_key'])) {
            $integration->setApiKey($data['api_key']);
        }

        if (isset($data['api_secret'])) {
            $integration->setApiSecret($data['api_secret']);
        }

        $this->integrations()->save($integration);

        return $integration;
    }

    /**
     * Create or update custom integration.
     *
     * @param string $provider
     * @param string $type
     * @param array $data
     * @return UserIntegration
     */
    public function createIntegration(string $provider, string $type, array $data = []): UserIntegration
    {
        return $this->integrations()->updateOrCreate(
            ['provider' => $provider],
            array_merge([
                'provider_name' => $data['provider_name'] ?? null,
                'type' => $type,
                'credentials' => $data['credentials'] ?? [],
                'metadata' => $data['metadata'] ?? [],
                'is_active' => $data['is_active'] ?? true,
            ], $data)
        );
    }

    /**
     * Remove integration.
     *
     * @param string $provider
     * @return bool
     */
    public function removeIntegration(string $provider): bool
    {
        return $this->integrations()->provider($provider)->delete();
    }

    /**
     * Get OAuth integrations.
     *
     * @return Collection
     */
    public function oauthIntegrations(): Collection
    {
        return $this->integrations()->oauth()->get();
    }

    /**
     * Get API key integrations.
     *
     * @return Collection
     */
    public function apiKeyIntegrations(): Collection
    {
        return $this->integrations()->apiKey()->get();
    }

    /**
     * Get integrations with valid tokens.
     *
     * @return Collection
     */
    public function validIntegrations(): Collection
    {
        return $this->integrations()->validToken()->get();
    }

    /**
     * Activate integration.
     *
     * @param string $provider
     * @return bool
     */
    public function activateIntegration(string $provider): bool
    {
        $integration = $this->getIntegration($provider);

        if (!$integration) {
            return false;
        }

        return $integration->activate();
    }

    /**
     * Deactivate integration.
     *
     * @param string $provider
     * @return bool
     */
    public function deactivateIntegration(string $provider): bool
    {
        $integration = $this->getIntegration($provider);

        if (!$integration) {
            return false;
        }

        return $integration->deactivate();
    }

    /**
     * Get integration access token.
     *
     * @param string $provider
     * @return string|null
     */
    public function getIntegrationAccessToken(string $provider): ?string
    {
        $integration = $this->getIntegration($provider);

        if (!$integration) {
            return null;
        }

        return $integration->getDecryptedAccessToken();
    }

    /**
     * Get integration API key.
     *
     * @param string $provider
     * @return string|null
     */
    public function getIntegrationApiKey(string $provider): ?string
    {
        $integration = $this->getIntegration($provider);

        if (!$integration) {
            return null;
        }

        return $integration->getDecryptedApiKey();
    }

    /**
     * Get integration API secret.
     *
     * @param string $provider
     * @return string|null
     */
    public function getIntegrationApiSecret(string $provider): ?string
    {
        $integration = $this->getIntegration($provider);

        if (!$integration) {
            return null;
        }

        return $integration->getDecryptedApiSecret();
    }

    /**
     * Update integration metadata.
     *
     * @param string $provider
     * @param array $metadata
     * @return bool
     */
    public function updateIntegrationMetadata(string $provider, array $metadata): bool
    {
        $integration = $this->getIntegration($provider);

        if (!$integration) {
            return false;
        }

        return $integration->update([
            'metadata' => array_merge($integration->metadata ?? [], $metadata),
        ]);
    }

    /**
     * Mark integration as used.
     *
     * @param string $provider
     * @return void
     */
    public function markIntegrationAsUsed(string $provider): void
    {
        $integration = $this->getIntegration($provider);

        if ($integration) {
            $integration->markAsUsed();
        }
    }
}

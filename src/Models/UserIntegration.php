<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserIntegration extends Model
{
    protected $table = 'user_integrations';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_name',
        'type',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'api_key',
        'api_secret',
        'credentials',
        'metadata',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'credentials' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'api_key',
        'api_secret',
        'credentials',
    ];

    /**
     * Get the user that owns the integration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get decrypted access token.
     *
     * @return string|null
     */
    public function getDecryptedAccessToken(): ?string
    {
        if (!$this->access_token) {
            return null;
        }

        try {
            return Crypt::decryptString($this->access_token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted access token.
     *
     * @param string|null $value
     * @return void
     */
    public function setAccessToken(?string $value): void
    {
        $this->access_token = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted refresh token.
     *
     * @return string|null
     */
    public function getDecryptedRefreshToken(): ?string
    {
        if (!$this->refresh_token) {
            return null;
        }

        try {
            return Crypt::decryptString($this->refresh_token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted refresh token.
     *
     * @param string|null $value
     * @return void
     */
    public function setRefreshToken(?string $value): void
    {
        $this->refresh_token = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted API key.
     *
     * @return string|null
     */
    public function getDecryptedApiKey(): ?string
    {
        if (!$this->api_key) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted API key.
     *
     * @param string|null $value
     * @return void
     */
    public function setApiKey(?string $value): void
    {
        $this->api_key = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted API secret.
     *
     * @return string|null
     */
    public function getDecryptedApiSecret(): ?string
    {
        if (!$this->api_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted API secret.
     *
     * @param string|null $value
     * @return void
     */
    public function setApiSecret(?string $value): void
    {
        $this->api_secret = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Check if token is expired.
     *
     * @return bool
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if token is valid (exists and not expired).
     *
     * @return bool
     */
    public function hasValidToken(): bool
    {
        return !empty($this->access_token) && !$this->isTokenExpired();
    }

    /**
     * Update last used timestamp.
     *
     * @return void
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Activate the integration.
     *
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the integration.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Scope to filter by provider.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $provider
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to filter by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter active integrations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter integrations with valid tokens.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidToken($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('access_token')
              ->where(function ($q2) {
                  $q2->whereNull('token_expires_at')
                     ->orWhere('token_expires_at', '>', now());
              });
        });
    }

    /**
     * Scope to filter OAuth integrations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOAuth($query)
    {
        return $query->where('type', 'oauth');
    }

    /**
     * Scope to filter API key integrations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApiKey($query)
    {
        return $query->where('type', 'api_key');
    }
}

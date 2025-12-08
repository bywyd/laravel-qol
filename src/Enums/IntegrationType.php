<?php

namespace Bywyd\LaravelQol\Enums;

enum IntegrationType: string
{
    case OAUTH = 'oauth';
    case API_KEY = 'api_key';
    case WEBHOOK = 'webhook';
    case CUSTOM = 'custom';

    /**
     * Get the label for the integration type.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::OAUTH => 'OAuth',
            self::API_KEY => 'API Key',
            self::WEBHOOK => 'Webhook',
            self::CUSTOM => 'Custom',
        };
    }

    /**
     * Get all types as an array.
     *
     * @return array
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all types with labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}

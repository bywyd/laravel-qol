<?php

namespace Bywyd\LaravelQol\Enums;

enum HistoryLogTypes: int
{
    case CREATED = 1;
    case UPDATED = 2;
    case DELETED = 3;
    case RESTORED = 4;
    case CUSTOM = 99;

    /**
     * Get the label for the history log type.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::CREATED => 'Created',
            self::UPDATED => 'Updated',
            self::DELETED => 'Deleted',
            self::RESTORED => 'Restored',
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

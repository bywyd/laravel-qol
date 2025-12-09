<?php

namespace Bywyd\LaravelQol\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    protected string $pattern;

    public function __construct(?string $pattern = null)
    {
        $this->pattern = $pattern ?? '/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/';
    }

    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid phone number.';
    }
}

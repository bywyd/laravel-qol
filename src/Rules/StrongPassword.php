<?php

namespace Bywyd\LaravelQol\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    protected int $minLength;
    protected bool $requireUppercase;
    protected bool $requireLowercase;
    protected bool $requireNumbers;
    protected bool $requireSpecialChars;

    public function __construct(
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireNumbers = true,
        bool $requireSpecialChars = true
    ) {
        $this->minLength = $minLength;
        $this->requireUppercase = $requireUppercase;
        $this->requireLowercase = $requireLowercase;
        $this->requireNumbers = $requireNumbers;
        $this->requireSpecialChars = $requireSpecialChars;
    }

    public function passes($attribute, $value): bool
    {
        if (!is_string($value) || strlen($value) < $this->minLength) {
            return false;
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $value)) {
            return false;
        }

        if ($this->requireLowercase && !preg_match('/[a-z]/', $value)) {
            return false;
        }

        if ($this->requireNumbers && !preg_match('/[0-9]/', $value)) {
            return false;
        }

        if ($this->requireSpecialChars && !preg_match('/[^A-Za-z0-9]/', $value)) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        $requirements = [];
        $requirements[] = "at least {$this->minLength} characters";
        
        if ($this->requireUppercase) $requirements[] = 'one uppercase letter';
        if ($this->requireLowercase) $requirements[] = 'one lowercase letter';
        if ($this->requireNumbers) $requirements[] = 'one number';
        if ($this->requireSpecialChars) $requirements[] = 'one special character';

        return 'The :attribute must contain ' . implode(', ', $requirements) . '.';
    }
}

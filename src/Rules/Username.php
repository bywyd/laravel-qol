<?php

namespace Bywyd\LaravelQol\Rules;

use Illuminate\Contracts\Validation\Rule;

class Username implements Rule
{
    protected int $minLength;
    protected int $maxLength;
    protected bool $allowDash;
    protected bool $allowUnderscore;
    protected bool $allowDot;

    public function __construct(
        int $minLength = 3,
        int $maxLength = 20,
        bool $allowDash = true,
        bool $allowUnderscore = true,
        bool $allowDot = false
    ) {
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->allowDash = $allowDash;
        $this->allowUnderscore = $allowUnderscore;
        $this->allowDot = $allowDot;
    }

    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $length = strlen($value);
        if ($length < $this->minLength || $length > $this->maxLength) {
            return false;
        }

        $allowedChars = 'a-zA-Z0-9';
        if ($this->allowDash) $allowedChars .= '\-';
        if ($this->allowUnderscore) $allowedChars .= '_';
        if ($this->allowDot) $allowedChars .= '\.';

        $pattern = '/^[' . $allowedChars . ']+$/';

        if (!preg_match($pattern, $value)) {
            return false;
        }

        // Must start with a letter
        if (!preg_match('/^[a-zA-Z]/', $value)) {
            return false;
        }

        // Cannot end with special characters
        if (preg_match('/[\-_\.]$/', $value)) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return "The :attribute must be {$this->minLength}-{$this->maxLength} characters, start with a letter, and contain only letters, numbers" .
               ($this->allowDash ? ', dashes' : '') .
               ($this->allowUnderscore ? ', underscores' : '') .
               ($this->allowDot ? ', dots' : '') . '.';
    }
}

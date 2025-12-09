<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Rules\PhoneNumber;
use Bywyd\LaravelQol\Rules\StrongPassword;
use Bywyd\LaravelQol\Rules\Username;

class ValidationRulesTest extends TestCase
{
    /** @test */
    public function phone_number_validates_correctly()
    {
        $rule = new PhoneNumber();

        $this->assertTrue($rule->passes('phone', '+1234567890'));
        $this->assertTrue($rule->passes('phone', '1234567890'));
        
        $this->assertFalse($rule->passes('phone', 'abc'));
        $this->assertFalse($rule->passes('phone', '12'));
    }

    /** @test */
    public function phone_number_works_with_custom_pattern()
    {
        $rule = new PhoneNumber('/^\d{10}$/');

        $this->assertTrue($rule->passes('phone', '1234567890'));
        $this->assertFalse($rule->passes('phone', '+1234567890'));
    }

    /** @test */
    public function strong_password_validates_all_requirements()
    {
        $rule = new StrongPassword(8, true, true, true, true);

        $this->assertTrue($rule->passes('password', 'Test123!'));
        $this->assertTrue($rule->passes('password', 'MyP@ssw0rd'));
        
        $this->assertFalse($rule->passes('password', 'short1!'));
        $this->assertFalse($rule->passes('password', 'NoNumbers!'));
        $this->assertFalse($rule->passes('password', 'noupper123!'));
        $this->assertFalse($rule->passes('password', 'NOLOWER123!'));
        $this->assertFalse($rule->passes('password', 'NoSpecial123'));
    }

    /** @test */
    public function strong_password_with_optional_requirements()
    {
        $rule = new StrongPassword(
            minLength: 6,
            requireUppercase: false,
            requireLowercase: true,
            requireNumbers: true,
            requireSpecialChars: false
        );

        $this->assertTrue($rule->passes('password', 'test123'));
        $this->assertFalse($rule->passes('password', 'test'));
        $this->assertFalse($rule->passes('password', 'testtest'));
    }

    /** @test */
    public function username_validates_correctly()
    {
        $rule = new Username(3, 20, true, true, false);

        $this->assertTrue($rule->passes('username', 'john'));
        $this->assertTrue($rule->passes('username', 'john_doe'));
        $this->assertTrue($rule->passes('username', 'john-doe'));
        $this->assertTrue($rule->passes('username', 'john123'));
        
        $this->assertFalse($rule->passes('username', 'ab')); // Too short
        $this->assertFalse($rule->passes('username', '123john')); // Doesn't start with letter
        $this->assertFalse($rule->passes('username', 'john_')); // Ends with special char
        $this->assertFalse($rule->passes('username', 'john.doe')); // Dot not allowed
        $this->assertFalse($rule->passes('username', 'john@doe')); // @ not allowed
    }

    /** @test */
    public function username_with_custom_settings()
    {
        $rule = new Username(
            minLength: 5,
            maxLength: 15,
            allowDash: false,
            allowUnderscore: true,
            allowDot: true
        );

        $this->assertTrue($rule->passes('username', 'john_doe'));
        $this->assertTrue($rule->passes('username', 'john.doe'));
        $this->assertFalse($rule->passes('username', 'john-doe')); // Dash not allowed
        $this->assertFalse($rule->passes('username', 'john')); // Too short
    }

    /** @test */
    public function validation_rules_return_messages()
    {
        $phoneRule = new PhoneNumber();
        $this->assertIsString($phoneRule->message());

        $passwordRule = new StrongPassword();
        $this->assertIsString($passwordRule->message());

        $usernameRule = new Username();
        $this->assertIsString($usernameRule->message());
    }
}

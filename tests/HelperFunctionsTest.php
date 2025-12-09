<?php

namespace Bywyd\LaravelQol\Tests;

class HelperFunctionsTest extends TestCase
{
    /** @test */
    public function str_limit_words_limits_words_correctly()
    {
        $text = 'This is a long sentence with many words in it';
        
        $limited = str_limit_words($text, 5);
        
        $this->assertEquals('This is a long sentence...', $limited);
    }

    /** @test */
    public function str_limit_words_doesnt_truncate_short_strings()
    {
        $text = 'Short text';
        
        $limited = str_limit_words($text, 10);
        
        $this->assertEquals('Short text', $limited);
    }

    /** @test */
    public function money_format_simple_formats_correctly()
    {
        $this->assertEquals('$1,234.56', money_format_simple(1234.56));
        $this->assertEquals('€100.00', money_format_simple(100, '€'));
        $this->assertEquals('$50.5', money_format_simple(50.5, '$', 1));
    }

    /** @test */
    public function percentage_calculates_correctly()
    {
        $this->assertEquals(25.0, percentage(25, 100));
        $this->assertEquals(50.0, percentage(50, 100));
        $this->assertEquals(0.0, percentage(10, 0)); // Avoid division by zero
    }

    /** @test */
    public function array_filter_recursive_filters_arrays()
    {
        $array = [
            'a' => 1,
            'b' => null,
            'c' => [
                'd' => 2,
                'e' => null,
                'f' => [
                    'g' => 3,
                    'h' => null
                ]
            ]
        ];

        $filtered = array_filter_recursive($array);

        $this->assertArrayNotHasKey('b', $filtered);
        $this->assertArrayNotHasKey('e', $filtered['c']);
        $this->assertArrayNotHasKey('h', $filtered['c']['f']);
    }

    /** @test */
    public function sanitize_filename_removes_invalid_characters()
    {
        $result1 = sanitize_filename('My File (1).pdf');
        $this->assertStringContainsString('My', $result1);
        $this->assertStringContainsString('.pdf', $result1);
        $this->assertDoesNotMatchRegularExpression('/[^a-zA-Z0-9\._-]/', $result1);
        
        $result2 = sanitize_filename('test@file.txt');
        $this->assertStringContainsString('test', $result2);
        $this->assertStringContainsString('.txt', $result2);
    }

    /** @test */
    public function generate_random_string_generates_correct_length()
    {
        $string = generate_random_string(16);
        
        $this->assertEquals(16, strlen($string));
    }

    /** @test */
    public function generate_random_string_alphanumeric_only()
    {
        $string = generate_random_string(50, true);
        
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $string);
    }

    /** @test */
    public function bytes_to_human_converts_correctly()
    {
        $this->assertStringContainsString('KB', bytes_to_human(1024));
        $this->assertStringContainsString('MB', bytes_to_human(1048576));
        $this->assertStringContainsString('GB', bytes_to_human(1073741824));
    }

    /** @test */
    public function human_to_bytes_converts_correctly()
    {
        $this->assertEquals(1024, human_to_bytes('1KB'));
        $this->assertEquals(1048576, human_to_bytes('1MB'));
        $this->assertEquals(1073741824, human_to_bytes('1GB'));
        $this->assertEquals(10485760, human_to_bytes('10MB'));
    }

    /** @test */
    public function is_json_validates_json_strings()
    {
        $this->assertTrue(is_json('{"key":"value"}'));
        $this->assertTrue(is_json('[1,2,3]'));
        $this->assertFalse(is_json('not json'));
        $this->assertFalse(is_json('{invalid}'));
    }

    /** @test */
    public function carbon_parse_safe_parses_valid_dates()
    {
        $date = carbon_parse_safe('2024-01-01');
        
        $this->assertInstanceOf(\Carbon\Carbon::class, $date);
        $this->assertEquals('2024-01-01', $date->format('Y-m-d'));
    }

    /** @test */
    public function carbon_parse_safe_returns_default_for_invalid_dates()
    {
        $default = now();
        $result = carbon_parse_safe('invalid date', $default);
        
        $this->assertEquals($default, $result);
    }

    /** @test */
    public function truncate_middle_truncates_correctly()
    {
        $long = 'very-long-filename-with-many-characters.txt';
        
        $truncated = truncate_middle($long, 20);
        
        $this->assertEquals(20, strlen($truncated));
        $this->assertStringContainsString('...', $truncated);
        $this->assertStringStartsWith('very-', $truncated);
        $this->assertStringEndsWith('.txt', $truncated);
    }

    /** @test */
    public function truncate_middle_doesnt_truncate_short_strings()
    {
        $short = 'short.txt';
        
        $result = truncate_middle($short, 20);
        
        $this->assertEquals('short.txt', $result);
    }
}

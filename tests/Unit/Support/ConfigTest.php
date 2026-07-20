<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Support\Config;

describe('Config::string()', function () {
    test('returns config string value', function () {
        config(['test.key' => 'hello']);

        expect(Config::string('test.key'))->toBe('hello');
    });

    test('returns default when key is missing', function () {
        expect(Config::string('test.missing', 'default'))->toBe('default');
    });

    test('returns default when value is not a string', function () {
        config(['test.key' => 42]);

        expect(Config::string('test.key', 'fallback'))->toBe('fallback');
    });
});

describe('Config::bool()', function () {
    test('returns true for boolean true', function () {
        config(['test.key' => true]);

        expect(Config::bool('test.key'))->toBeTrue();
    });

    test('returns false for boolean false', function () {
        config(['test.key' => false]);

        expect(Config::bool('test.key', true))->toBeFalse();
    });

    test('returns false for string "false"', function () {
        config(['test.key' => 'false']);

        expect(Config::bool('test.key', true))->toBeFalse();
    });

    test('returns false for string "0"', function () {
        config(['test.key' => '0']);

        expect(Config::bool('test.key', true))->toBeFalse();
    });

    test('returns true for string "true"', function () {
        config(['test.key' => 'true']);

        expect(Config::bool('test.key'))->toBeTrue();
    });

    test('returns true for string "1"', function () {
        config(['test.key' => '1']);

        expect(Config::bool('test.key'))->toBeTrue();
    });

    test('returns default when key is missing', function () {
        expect(Config::bool('test.missing', true))->toBeTrue();
        expect(Config::bool('test.missing', false))->toBeFalse();
    });

    test('returns default for unrecognised string value', function () {
        config(['test.key' => 'notabool']);

        expect(Config::bool('test.key', true))->toBeTrue();
        expect(Config::bool('test.key', false))->toBeFalse();
    });
});

describe('Config::int()', function () {
    test('returns config integer value', function () {
        config(['test.key' => 5]);

        expect(Config::int('test.key'))->toBe(5);
    });

    test('returns default when key is missing', function () {
        expect(Config::int('test.missing', 10))->toBe(10);
    });

    test('returns default when value is not an integer', function () {
        config(['test.key' => '5']);

        expect(Config::int('test.key', 99))->toBe(99);
    });
});

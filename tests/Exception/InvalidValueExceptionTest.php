<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Exception;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class InvalidValueExceptionTest extends TestCase
{
    public function testMessageWithScalarValue(): void
    {
        $invalidValueException = new InvalidValueException('fieldname', 'badvalue');

        static::assertSame('invalid value `badvalue` for `fieldname`', $invalidValueException->getMessage());
    }

    public function testMessageWithIntegerValue(): void
    {
        $invalidValueException = new InvalidValueException('fieldname', 42);

        static::assertSame('invalid value `42` for `fieldname`', $invalidValueException->getMessage());
    }

    public function testMessageWithArrayValue(): void
    {
        $invalidValueException = new InvalidValueException('fieldname', ['a', 'b', 'c']);

        static::assertSame('invalid value `a, b, c` for `fieldname`', $invalidValueException->getMessage());
    }

    public function testMessageWithObjectValue(): void
    {
        $object = new \stdClass();
        $invalidValueException = new InvalidValueException('fieldname', $object);

        static::assertSame('invalid value `stdClass` for `fieldname`', $invalidValueException->getMessage());
    }

    public function testMessageWithNullValue(): void
    {
        $invalidValueException = new InvalidValueException('fieldname', null);

        static::assertStringContainsString('unknown type', $invalidValueException->getMessage());
    }

    public function testExtendsBaseException(): void
    {
        $invalidValueException = new InvalidValueException('field', 'value');

        static::assertInstanceOf(\PrecisionSoft\Symfony\JsonForm\Exception\Exception::class, $invalidValueException);
    }
}

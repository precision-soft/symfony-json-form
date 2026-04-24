<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Exception;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidModeException;

/**
 * @internal
 */
final class InvalidModeExceptionTest extends TestCase
{
    public function testMessage(): void
    {
        $invalidModeException = new InvalidModeException('status', 'wrong', ['single', 'multiple']);

        static::assertSame(
            'invalid mode `wrong` for `status` element; accepted: `single, multiple`',
            $invalidModeException->getMessage(),
        );
    }

    public function testExtendsBaseException(): void
    {
        $invalidModeException = new InvalidModeException('status', 'wrong', ['single']);

        static::assertInstanceOf(\PrecisionSoft\Symfony\JsonForm\Exception\Exception::class, $invalidModeException);
    }
}

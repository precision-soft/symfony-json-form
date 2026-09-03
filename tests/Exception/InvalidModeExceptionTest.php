<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Exception;

use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidModeException;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;

/**
 * @internal
 */
final class InvalidModeExceptionTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(InvalidModeException::class);
    }

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

        static::assertInstanceOf(Exception::class, $invalidModeException);
    }
}

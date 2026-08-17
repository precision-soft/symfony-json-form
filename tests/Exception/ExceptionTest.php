<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Exception;

use Exception as BaseException;
use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Contract\ExceptionInterface;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidModeException;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class ExceptionTest extends TestCase
{
    public function testExceptionExtendsBaseException(): void
    {
        $exception = new Exception('test message');

        static::assertInstanceOf(BaseException::class, $exception);
        static::assertSame('test message', $exception->getMessage());
    }

    public function testExceptionImplementsExceptionInterface(): void
    {
        static::assertInstanceOf(ExceptionInterface::class, new Exception('test message'));
    }

    public function testContextDefaultsToAnEmptyArray(): void
    {
        static::assertSame([], (new Exception('test message'))->getContext());
        static::assertSame([], (new Exception('test message', 0, null, null))->getContext());
    }

    public function testContextIsReadBackFromTheConstructor(): void
    {
        $exception = new Exception('test message', 0, null, ['formName' => 'myForm', 'requestMethod' => 'POST']);

        static::assertSame(['formName' => 'myForm', 'requestMethod' => 'POST'], $exception->getContext());
    }

    public function testSetContextReplacesTheContextAndIsFluent(): void
    {
        $exception = new Exception('test message', 0, null, ['first' => 1]);

        static::assertSame($exception, $exception->setContext(['second' => 2]));
        static::assertSame(['second' => 2], $exception->getContext());

        $exception->setContext(null);

        static::assertSame([], $exception->getContext());
    }

    public function testTheContextDoesNotLeakIntoTheMessageCodeOrPrevious(): void
    {
        $previousException = new BaseException('root cause');

        $exception = new Exception('test message', 7, $previousException, ['key' => 'value']);

        static::assertSame('test message', $exception->getMessage());
        static::assertSame(7, $exception->getCode());
        static::assertSame($previousException, $exception->getPrevious());
    }

    public function testTheSubclassesWithTheirOwnConstructorStillCarryTheCapability(): void
    {
        $invalidModeException = new InvalidModeException('fieldname', 'badmode', ['goodmode']);
        $invalidValueException = new InvalidValueException('fieldname', 'badvalue');

        static::assertInstanceOf(ExceptionInterface::class, $invalidModeException);
        static::assertInstanceOf(ExceptionInterface::class, $invalidValueException);

        static::assertSame([], $invalidModeException->getContext());
        static::assertSame(['name' => 'fieldname'], $invalidValueException->setContext(['name' => 'fieldname'])->getContext());
    }

    public function testTheConstructorDefaultsToAnEmptyMessageZeroCodeAndNoPrevious(): void
    {
        $exception = new Exception();

        static::assertSame('', $exception->getMessage());
        static::assertSame(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
        static::assertSame([], $exception->getContext());
    }
}

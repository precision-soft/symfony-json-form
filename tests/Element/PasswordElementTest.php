<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\PasswordElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class PasswordElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $passwordElement = new PasswordElement('secret', 'label');

        $result = $passwordElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithStringValue(): void
    {
        $passwordElement = new PasswordElement('secret', 'label');

        $result = $passwordElement->render('hunter2');

        static::assertSame('hunter2', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $passwordElement = new PasswordElement('secret', 'label');

        $result = $passwordElement->render('hunter2');

        static::assertSame('password', $result['type']);
        static::assertSame('secret', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithNonStringThrowsException(): void
    {
        $passwordElement = new PasswordElement('secret', 'label');

        static::expectException(InvalidValueException::class);

        $passwordElement->render(42);
    }

    public function testRenderWithReadonlyAndRequiredSet(): void
    {
        $passwordElement = new PasswordElement('secret', 'label');
        $passwordElement->setReadonly(true);
        $passwordElement->setRequired(true);

        $result = $passwordElement->render(null);

        static::assertTrue($result['readonly']);
        static::assertTrue($result['required']);
    }
}

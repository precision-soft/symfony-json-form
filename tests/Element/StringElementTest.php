<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class StringElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $stringElement = new StringElement('name', 'label');

        $result = $stringElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithStringValue(): void
    {
        $stringElement = new StringElement('name', 'label');

        $result = $stringElement->render('hello');

        static::assertSame('hello', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $stringElement = new StringElement('name', 'label');

        $result = $stringElement->render('hello');

        static::assertSame('string', $result['type']);
        static::assertSame('name', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithNonStringThrowsException(): void
    {
        $stringElement = new StringElement('name', 'label');

        static::expectException(InvalidValueException::class);

        $stringElement->render(42);
    }

    public function testRenderWithArrayThrowsException(): void
    {
        $stringElement = new StringElement('name', 'label');

        static::expectException(InvalidValueException::class);

        $stringElement->render([]);
    }

    public function testRenderWithReadonlySet(): void
    {
        $stringElement = new StringElement('name', 'label');
        $stringElement->setReadonly(true);

        $result = $stringElement->render(null);

        static::assertTrue($result['readonly']);
    }

    public function testRenderWithRequiredSet(): void
    {
        $stringElement = new StringElement('name', 'label');
        $stringElement->setRequired(true);

        $result = $stringElement->render(null);

        static::assertTrue($result['required']);
    }
}

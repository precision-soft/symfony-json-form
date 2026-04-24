<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\NumberElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class NumberElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $numberElement = new NumberElement('count', 'label');

        $result = $numberElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithInteger(): void
    {
        $numberElement = new NumberElement('count', 'label');

        $result = $numberElement->render(42);

        static::assertSame(42, $result['value']);
    }

    public function testRenderWithFloat(): void
    {
        $numberElement = new NumberElement('price', 'label');

        $result = $numberElement->render(9.99);

        static::assertSame(9.99, $result['value']);
    }

    public function testRenderWithNumericString(): void
    {
        $numberElement = new NumberElement('count', 'label');

        $result = $numberElement->render('123');

        static::assertSame('123', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $numberElement = new NumberElement('count', 'label', 1.0, 100.0, 0.5);

        $result = $numberElement->render(5);

        static::assertSame('number', $result['type']);
        static::assertSame('count', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertSame(1.0, $result['min']);
        static::assertSame(100.0, $result['max']);
        static::assertSame(0.5, $result['step']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithNullMinMaxStep(): void
    {
        $numberElement = new NumberElement('count', 'label');

        $result = $numberElement->render(null);

        static::assertNull($result['min']);
        static::assertNull($result['max']);
        static::assertNull($result['step']);
    }

    public function testRenderWithNonNumericStringThrowsException(): void
    {
        $numberElement = new NumberElement('count', 'label');

        static::expectException(InvalidValueException::class);

        $numberElement->render('abc');
    }

    public function testRenderWithArrayThrowsException(): void
    {
        $numberElement = new NumberElement('count', 'label');

        static::expectException(InvalidValueException::class);

        $numberElement->render([]);
    }
}

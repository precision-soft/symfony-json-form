<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PrecisionSoft\Symfony\JsonForm\Element\NumberElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;

/**
 * @internal
 */
final class NumberElementTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(NumberElement::class);
    }

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

    public function testRenderBelowMinThrowsException(): void
    {
        $numberElement = new NumberElement('quantity', 'label', 1.0, 10.0);

        static::expectException(InvalidValueException::class);

        $numberElement->render(0.5);
    }

    public function testRenderAboveMaxThrowsException(): void
    {
        $numberElement = new NumberElement('quantity', 'label', 1.0, 10.0);

        static::expectException(InvalidValueException::class);

        $numberElement->render(11);
    }

    public function testRenderAcceptsTheBoundsThemselves(): void
    {
        $numberElement = new NumberElement('quantity', 'label', 1.0, 10.0);

        static::assertSame(1, $numberElement->render(1)['value']);
        static::assertSame(10, $numberElement->render(10)['value']);
    }

    public function testRenderWithNumericStringRespectsTheBounds(): void
    {
        $numberElement = new NumberElement('quantity', 'label', 1.0, 10.0);

        static::expectException(InvalidValueException::class);

        $numberElement->render('42');
    }

    public function testConstructWithMinAboveMaxThrowsException(): void
    {
        static::expectException(InvalidValueException::class);

        new NumberElement('quantity', 'label', 10.0, 1.0);
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

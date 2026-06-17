<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\DateElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class DateElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $dateElement = new DateElement('birthday', 'label');

        $result = $dateElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithValidYMDDate(): void
    {
        $dateElement = new DateElement('birthday', 'label');

        $result = $dateElement->render('2024-01-15');

        static::assertSame('2024-01-15', $result['value']);
    }

    public function testRenderWithValidDMYDate(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_D_M_Y);

        $result = $dateElement->render('15-01-2024');

        static::assertSame('15-01-2024', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_Y_M_D, '2020-01-01', '2030-12-31');

        $result = $dateElement->render('2024-06-15');

        static::assertSame('date', $result['type']);
        static::assertSame('birthday', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertSame(DateElement::FORMAT_Y_M_D, $result['format']);
        static::assertSame('2020-01-01', $result['min']);
        static::assertSame('2030-12-31', $result['max']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithWrongFormatThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_Y_M_D);

        static::expectException(InvalidValueException::class);

        $dateElement->render('15-01-2024');
    }

    public function testRenderWithNonStringThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label');

        static::expectException(InvalidValueException::class);

        $dateElement->render(20240115);
    }

    public function testRenderWithInvalidDateStringThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label');

        static::expectException(InvalidValueException::class);

        $dateElement->render('not-a-date');
    }

    public function testRenderWithOverflowDateThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label');

        static::expectException(InvalidValueException::class);

        $dateElement->render('2021-02-30');
    }

    public function testRenderBelowMinThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_Y_M_D, '2020-01-01', '2030-12-31');

        static::expectException(InvalidValueException::class);

        $dateElement->render('2019-12-31');
    }

    public function testRenderAboveMaxThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_Y_M_D, '2020-01-01', '2030-12-31');

        static::expectException(InvalidValueException::class);

        $dateElement->render('2031-01-01');
    }

    public function testRenderAtMinBoundaryPasses(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_Y_M_D, '2020-01-01', '2030-12-31');

        $result = $dateElement->render('2020-01-01');

        static::assertSame('2020-01-01', $result['value']);
    }

    public function testRenderAtMaxBoundaryPasses(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_Y_M_D, '2020-01-01', '2030-12-31');

        $result = $dateElement->render('2030-12-31');

        static::assertSame('2030-12-31', $result['value']);
    }

    public function testRenderWithinDMYRangePasses(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_D_M_Y, '01-01-2020', '31-12-2020');

        $result = $dateElement->render('15-06-2020');

        static::assertSame('15-06-2020', $result['value']);
    }

    public function testRenderOutsideDMYRangeThrowsException(): void
    {
        $dateElement = new DateElement('birthday', 'label', DateElement::FORMAT_D_M_Y, '01-01-2020', '31-12-2020');

        static::expectException(InvalidValueException::class);

        $dateElement->render('15-06-2021');
    }
}

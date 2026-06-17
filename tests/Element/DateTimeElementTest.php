<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\DateTimeElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class DateTimeElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $dateTimeElement = new DateTimeElement('startsAt', 'label');

        $result = $dateTimeElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithValidValue(): void
    {
        $dateTimeElement = new DateTimeElement('startsAt', 'label');

        $result = $dateTimeElement->render('2024-01-15 13:45');

        static::assertSame('2024-01-15 13:45', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $dateTimeElement = new DateTimeElement(
            'startsAt',
            'label',
            DateTimeElement::FORMAT_Y_M_D_H_I,
            '2020-01-01 00:00',
            '2030-12-31 23:59',
        );

        $result = $dateTimeElement->render('2024-06-15 10:30');

        static::assertSame('dateTime', $result['type']);
        static::assertSame('startsAt', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertSame(DateTimeElement::FORMAT_Y_M_D_H_I, $result['format']);
        static::assertSame('2020-01-01 00:00', $result['min']);
        static::assertSame('2030-12-31 23:59', $result['max']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithWrongFormatThrowsException(): void
    {
        $dateTimeElement = new DateTimeElement('startsAt', 'label', DateTimeElement::FORMAT_Y_M_D_H_I);

        static::expectException(InvalidValueException::class);

        $dateTimeElement->render('15-01-2024 13:45');
    }

    public function testRenderWithNonStringThrowsException(): void
    {
        $dateTimeElement = new DateTimeElement('startsAt', 'label');

        static::expectException(InvalidValueException::class);

        $dateTimeElement->render(20240115);
    }

    public function testRenderWithOverflowDateThrowsException(): void
    {
        $dateTimeElement = new DateTimeElement('startsAt', 'label');

        static::expectException(InvalidValueException::class);

        $dateTimeElement->render('2021-02-30 10:00');
    }

    public function testRenderBelowMinThrowsException(): void
    {
        $dateTimeElement = new DateTimeElement(
            'startsAt',
            'label',
            DateTimeElement::FORMAT_Y_M_D_H_I,
            '2020-01-01 00:00',
            '2030-12-31 23:59',
        );

        static::expectException(InvalidValueException::class);

        $dateTimeElement->render('2019-12-31 23:59');
    }

    public function testRenderAboveMaxThrowsException(): void
    {
        $dateTimeElement = new DateTimeElement(
            'startsAt',
            'label',
            DateTimeElement::FORMAT_Y_M_D_H_I,
            '2020-01-01 00:00',
            '2030-12-31 23:59',
        );

        static::expectException(InvalidValueException::class);

        $dateTimeElement->render('2031-01-01 00:00');
    }

    public function testRenderAtMinBoundaryPasses(): void
    {
        $dateTimeElement = new DateTimeElement(
            'startsAt',
            'label',
            DateTimeElement::FORMAT_Y_M_D_H_I,
            '2020-01-01 00:00',
            '2030-12-31 23:59',
        );

        $result = $dateTimeElement->render('2020-01-01 00:00');

        static::assertSame('2020-01-01 00:00', $result['value']);
    }

    public function testRenderAtMaxBoundaryPasses(): void
    {
        $dateTimeElement = new DateTimeElement(
            'startsAt',
            'label',
            DateTimeElement::FORMAT_Y_M_D_H_I,
            '2020-01-01 00:00',
            '2030-12-31 23:59',
        );

        $result = $dateTimeElement->render('2030-12-31 23:59');

        static::assertSame('2030-12-31 23:59', $result['value']);
    }
}

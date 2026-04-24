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
}

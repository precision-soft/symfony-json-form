<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\LabelElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class LabelElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $labelElement = new LabelElement('info', 'label');

        $result = $labelElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithScalarValue(): void
    {
        $labelElement = new LabelElement('info', 'label');

        $result = $labelElement->render('some text');

        static::assertSame('some text', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $labelElement = new LabelElement('info', 'label');

        $result = $labelElement->render('text');

        static::assertSame('label', $result['type']);
        static::assertSame('info', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertSame('text', $result['value']);
    }

    public function testRenderWithNullLabelDefault(): void
    {
        $labelElement = new LabelElement('info');

        $result = $labelElement->render(null);

        static::assertNull($result['label']);
    }

    public function testRenderWithArrayThrowsException(): void
    {
        $labelElement = new LabelElement('info', 'label');

        static::expectException(InvalidValueException::class);

        $labelElement->render(['nested']);
    }
}

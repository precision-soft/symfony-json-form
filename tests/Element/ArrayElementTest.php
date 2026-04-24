<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\ArrayElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidModeException;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class ArrayElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $arrayElement = new ArrayElement('status', 'label', ['active' => 'Active', 'inactive' => 'Inactive']);

        $result = $arrayElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithValidSingleValue(): void
    {
        $arrayElement = new ArrayElement('status', 'label', ['active' => 'Active', 'inactive' => 'Inactive']);

        $result = $arrayElement->render(['active']);

        static::assertSame(['active'], $result['value']);
    }

    public function testRenderWithValueNotInOptionsThrowsException(): void
    {
        $arrayElement = new ArrayElement('status', 'label', ['active' => 'Active']);

        static::expectException(InvalidValueException::class);

        $arrayElement->render(['unknown']);
    }

    public function testRenderStructure(): void
    {
        $options = ['active' => 'Active', 'inactive' => 'Inactive'];
        $arrayElement = new ArrayElement('status', 'label', $options, ArrayElement::MODE_MULTIPLE);

        $result = $arrayElement->render(null);

        static::assertSame('array', $result['type']);
        static::assertSame('status', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertSame($options, $result['options']);
        static::assertSame(ArrayElement::MODE_MULTIPLE, $result['mode']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithGroupedOptions(): void
    {
        $options = ['Group' => ['a' => 'Option A', 'b' => 'Option B']];
        $arrayElement = new ArrayElement('type', 'label', $options);

        $result = $arrayElement->render(['a']);

        static::assertSame(['a'], $result['value']);
    }

    public function testConstructWithInvalidModeThrowsException(): void
    {
        static::expectException(InvalidModeException::class);

        new ArrayElement('status', 'label', ['a' => 'A'], 'invalid');
    }

    public function testModeSingleIsDefault(): void
    {
        $arrayElement = new ArrayElement('status', 'label', ['a' => 'A']);

        $result = $arrayElement->render(null);

        static::assertSame(ArrayElement::MODE_SINGLE, $result['mode']);
    }
}

<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PrecisionSoft\Symfony\JsonForm\Element\HiddenElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;

/**
 * @internal
 */
final class HiddenElementTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(HiddenElement::class);
    }

    public function testRenderWithNullValue(): void
    {
        $hiddenElement = new HiddenElement('token');

        $result = $hiddenElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithScalarValue(): void
    {
        $hiddenElement = new HiddenElement('token');

        $result = $hiddenElement->render('abc123');

        static::assertSame('abc123', $result['value']);
    }

    public function testRenderStructure(): void
    {
        $hiddenElement = new HiddenElement('token');

        $result = $hiddenElement->render(42);

        static::assertSame('hidden', $result['type']);
        static::assertSame('token', $result['name']);
        static::assertNull($result['label']);
        static::assertSame(42, $result['value']);
    }

    public function testRenderWithArrayThrowsException(): void
    {
        $hiddenElement = new HiddenElement('token');

        static::expectException(InvalidValueException::class);

        $hiddenElement->render(['nested']);
    }
}
